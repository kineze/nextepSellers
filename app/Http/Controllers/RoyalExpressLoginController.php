<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\CurfoxCity;
use App\Models\CurfoxState;
use App\Models\District;
use App\Models\Province;
use App\Models\RoyalExpressLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RoyalExpressLoginController extends Controller
{
    public function index(Request $request)
    {
        return RoyalExpressLogin::query()
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Save a new logistic partner login.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'partner_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email'],
            'partner_user_id' => ['nullable', 'integer'],
            'merchant_id' => ['nullable', 'integer'],
            'merchant_business_id' => ['nullable', 'integer'],
            'token' => ['nullable', 'string'],
            'token_expiry' => ['nullable', 'date'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'role_name' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
        ]);

        $login = RoyalExpressLogin::updateOrCreate(
            [
                'partner_name' => $validated['partner_name'],
            ],
            [
                ...$validated,
                'is_active' => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : true,
            ]
        );

        return response()->json([
            'message' => 'Login saved successfully',
            'data' => $login,
        ]);
    }

    /**
     * Delete a saved login.
     */
    public function destroy($id)
    {
        $login = RoyalExpressLogin::findOrFail($id);
        $login->delete();

        return response()->json([
            'message' => 'Login deleted successfully',
        ]);
    }

    public function loginAndSave(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $cfg = $this->courierConfig();
        $baseUrl = $cfg['base_url'];
        $tenant = $cfg['tenant'];

        if ($baseUrl === '') {
            return response()->json([
                'message' => 'Royal Express base URL is not configured.',
            ], 500);
        }

        // Step 1: Authenticate with Royal Express / Curfox API.
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-tenant' => $tenant,
        ])->post($baseUrl . '/api/public/merchant/login', [
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if ($response->failed()) {
            return response()->json([
                'status' => $response->status(),
                'body' => $response->body(),
                'message' => $response->json('message') ?? 'Login failed',
            ], $response->status());
        }

        $data = $response->json();
        $token = data_get($data, 'token');

        if (!$token) {
            return response()->json([
                'message' => 'Token not received from Royal Express.',
            ], 422);
        }

        // Step 2: Fetch businesses (default business ID).
        $businessResponse = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'X-tenant' => $tenant,
        ])->get($baseUrl . '/api/public/merchant/business', [
            'noPagination' => 1,
        ]);

        $businessId = null;
        if ($businessResponse->ok()) {
            $businessData = $businessResponse->json('data') ?? [];
            $defaultBusiness = collect($businessData)->firstWhere('is_default', true);
            if ($defaultBusiness) {
                $businessId = $defaultBusiness['id'] ?? null;
            }
        }

        // Step 3: Save login.
        $login = RoyalExpressLogin::updateOrCreate(
            [
                'partner_name' => 'royal_express',
            ],
            [
                'email' => data_get($data, 'user.email', $request->email),
                'partner_user_id' => data_get($data, 'user.id'),
                'merchant_id' => data_get($data, 'user.merchant_id'),
                'merchant_business_id' => $businessId,
                'token' => $token,
                'token_expiry' => now()->addHours(12),
                'first_name' => data_get($data, 'user.first_name'),
                'last_name' => data_get($data, 'user.last_name'),
                'role_name' => data_get($data, 'user.role.name'),
                'is_active' => true,
            ]
        );

        return response()->json([
            'message' => 'Royal Express login saved successfully',
            'data' => $login,
        ]);
    }

    public function updateLocation(Request $request, RoyalExpressLogin $login)
    {
        $validated = $request->validate([
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
        ]);

        $login->update($validated);

        return response()->json([
            'message' => 'Location updated successfully',
            'data' => $login,
        ]);
    }

    public function businesses(Request $request)
    {
        $login = $this->activeLoginWithToken();
        if ($login instanceof \Illuminate\Http\JsonResponse) {
            return $login;
        }

        $cfg = $this->courierConfig();
        $baseUrl = $cfg['base_url'];
        $tenant = $cfg['tenant'];

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $login->token,
            'Content-Type' => 'application/json',
            'X-tenant' => $tenant,
        ])->get($baseUrl . '/api/public/merchant/business', [
            'noPagination' => $request->integer('noPagination', 1),
        ]);

        if ($response->failed()) {
            return response()->json([
                'status' => $response->status(),
                'body' => $response->body(),
                'message' => $response->json('message') ?? 'Failed to fetch businesses',
            ], $response->status());
        }

        return response()->json([
            'message' => 'OK',
            'tenant' => $tenant,
            'businesses' => $response->json('data') ?? [],
            'meta' => $response->json('meta') ?? null,
        ]);
    }

    public function fetchCitiesStates(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:state,city'],
            'page' => ['nullable', 'integer', 'min:1'],
            'paginate' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $login = $this->activeLoginWithToken();
        if ($login instanceof \Illuminate\Http\JsonResponse) {
            return $login;
        }

        $cfg = $this->courierConfig();
        $baseUrl = $cfg['base_url'];
        $tenant = $cfg['tenant'];

        $type = $validated['type'];
        $page = (int) ($validated['page'] ?? 1);
        $paginate = (int) ($validated['paginate'] ?? 100);
        $endpoint = $type === 'state'
            ? '/api/public/merchant/state'
            : '/api/public/merchant/city';

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $login->token,
            'Content-Type' => 'application/json',
            'X-tenant' => $tenant,
        ])->get($baseUrl . $endpoint, [
            'page' => $page,
            'paginate' => $paginate,
        ]);

        if ($response->failed()) {
            return response()->json([
                'status' => $response->status(),
                'body' => $response->body(),
                'message' => $response->json('message') ?? 'Fetch failed',
            ], $response->status());
        }

        $payload = $response->json();
        $rows = collect($payload['data'] ?? [])->filter(fn ($row) => is_array($row))->values();

        $count = $type === 'state'
            ? $this->syncStates($rows)
            : $this->syncCities($rows);

        $meta = (array) ($payload['meta'] ?? []);
        $pagination = (array) ($payload['pagination'] ?? []);

        $currentPage = (int) (
            $payload['current_page']
            ?? $meta['current_page']
            ?? $pagination['current_page']
            ?? $page
        );

        $lastPage = (int) (
            $payload['last_page']
            ?? $meta['last_page']
            ?? $pagination['last_page']
            ?? $currentPage
        );

        $totalRows = (int) (
            $payload['total']
            ?? $meta['total']
            ?? $pagination['total']
            ?? 0
        );

        $perPage = (int) (
            $payload['per_page']
            ?? $meta['per_page']
            ?? $pagination['per_page']
            ?? $paginate
        );

        $next = null;

        if (!empty($payload['next'])) {
            $next = (int) $payload['next'];
        } elseif (!empty($meta['next_page'])) {
            $next = (int) $meta['next_page'];
        } elseif (!empty($pagination['next_page'])) {
            $next = (int) $pagination['next_page'];
        } elseif ($currentPage < $lastPage) {
            $next = $currentPage + 1;
        } elseif ($lastPage <= 1 && $totalRows > 0 && $perPage > 0 && ($currentPage * $perPage) < $totalRows) {
            $next = $currentPage + 1;
        } elseif ($count === $perPage) {
            // Fallback when API omits pagination metadata but still truncates by page size.
            $next = $page + 1;
        }

        return response()->json([
            'type' => $type,
            'count' => $count,
            'total' => $type === 'state' ? CurfoxState::count() : CurfoxCity::count(),
            'next' => $next,
            'logs' => [sprintf('Synced %d %s record(s) on page %d.', $count, $type, $page)],
        ]);
    }

    private function activeLoginWithToken(): RoyalExpressLogin|\Illuminate\Http\JsonResponse
    {
        $login = RoyalExpressLogin::query()
            ->where('is_active', true)
            ->whereNotNull('token')
            ->latest('updated_at')
            ->first();

        if (!$login) {
            return response()->json([
                'message' => 'No active Royal Express login found. Please connect first.',
            ], 422);
        }

        if ($login->token_expiry && now()->greaterThan($login->token_expiry)) {
            return response()->json([
                'message' => 'Royal Express token expired. Please login again.',
            ], 422);
        }

        return $login;
    }

    private function syncStates($rows): int
    {
        $count = 0;

        foreach ($rows as $row) {
            $refNo = (string) ($row['ref_no'] ?? $row['code'] ?? $row['id'] ?? '');
            $name = trim((string) ($row['name'] ?? ''));
            $countryId = $row['country_id'] ?? null;
            $hasChild = (bool) ($row['has_child'] ?? false);

            if ($name === '') {
                continue;
            }

            $where = $refNo !== ''
                ? ['ref_no' => $refNo]
                : ['name' => $name, 'country_id' => $countryId];

            CurfoxState::query()->updateOrCreate($where, [
                'ref_no' => $refNo !== '' ? $refNo : null,
                'name' => $name,
                'country_id' => $countryId,
                'has_child' => $hasChild,
            ]);

            $count++;
        }

        return $count;
    }

    private function syncCities($rows): int
    {
        $count = 0;

        foreach ($rows as $row) {
            $refNo = (string) ($row['ref_no'] ?? $row['code'] ?? $row['id'] ?? '');
            $name = trim((string) ($row['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $resolvedStateId = $this->resolveCurfoxStateId($row);

            $where = $refNo !== ''
                ? ['ref_no' => $refNo]
                : ['name' => $name, 'state_id' => $resolvedStateId];

            CurfoxCity::query()->updateOrCreate($where, [
                'ref_no' => $refNo !== '' ? $refNo : null,
                'name' => $name,
                'postal_code' => $row['postal_code'] ?? null,
                'state_id' => $resolvedStateId,
                'country_id' => $row['country_id'] ?? null,
                'zone_id' => $row['zone_id'] ?? null,
                'default_warehouse_id' => $row['default_warehouse_id'] ?? null,
                'is_active' => array_key_exists('is_active', $row) ? (bool) $row['is_active'] : true,
            ]);

            $count++;
        }

        return $count;
    }

    private function resolveCurfoxStateId(array $row): ?int
    {
        $stateIdRaw = $row['state_id'] ?? null;
        $stateRef = $row['state_ref_no'] ?? data_get($row, 'state.ref_no');

        if ($stateRef) {
            $state = CurfoxState::query()->where('ref_no', (string) $stateRef)->first();
            if ($state) {
                return (int) $state->id;
            }
        }

        if ($stateIdRaw !== null) {
            $state = CurfoxState::query()->find($stateIdRaw);
            if ($state) {
                return (int) $state->id;
            }

            $state = CurfoxState::query()->where('ref_no', (string) $stateIdRaw)->first();
            if ($state) {
                return (int) $state->id;
            }
        }

        $stateName = trim((string) ($row['state_name'] ?? data_get($row, 'state.name') ?? ''));
        if ($stateName !== '') {
            $state = CurfoxState::query()->where('name', $stateName)->first();
            if ($state) {
                return (int) $state->id;
            }
        }

        return null;
    }

    public function statesPreview()
    {
        $districtLookup = $this->districtLookupByName();
        $states = CurfoxState::with('systemDistrict')->get();

        $syncedStates = [];
        $matchedStates = [];
        $unmatchedStates = [];

        foreach ($states as $state) {
            if ($state->system_district_id && $state->systemDistrict) {
                $syncedStates[] = [
                    'curfox_state_id' => $state->id,
                    'curfox_state_name' => $state->name,
                    'district_id' => $state->systemDistrict->id,
                    'district_name' => $state->systemDistrict->name_en,
                ];
                continue;
            }

            $district = $this->findDistrictByStateName($state->name, $districtLookup);

            if ($district) {
                $matchedStates[] = [
                    'curfox_state_id' => $state->id,
                    'curfox_state_name' => $state->name,
                    'district_id' => $district->id,
                    'district_name' => $district->name_en,
                ];
            } else {
                $unmatchedStates[] = [
                    'curfox_state_id' => $state->id,
                    'curfox_state_name' => $state->name,
                ];
            }
        }

        $total = $states->count();
        $synced = count($syncedStates);
        $matched = count($matchedStates);
        $unmatched = count($unmatchedStates);
        $score = ($matched + $unmatched) > 0
            ? round(($matched / ($matched + $unmatched)) * 100, 2)
            : 0;

        return response()->json([
            'total_states' => $total,
            'synced_count' => $synced,
            'matched_count' => $matched,
            'unmatched_count' => $unmatched,
            'score' => $score,
            'synced_states' => $syncedStates,
            'matched_states' => $matchedStates,
            'unmatched_states' => $unmatchedStates,
        ]);
    }

    public function statesSyncMatched()
    {
        $districtLookup = $this->districtLookupByName();
        $states = CurfoxState::all();
        $synced = 0;

        foreach ($states as $state) {
            $district = $this->findDistrictByStateName($state->name, $districtLookup);

            if ($district) {
                $state->system_district_id = $district->id;
                $state->save();
                $synced++;
            }
        }

        return response()->json([
            'success' => true,
            'synced' => $synced,
            'remaining_unmatched' => CurfoxState::whereNull('system_district_id')->count(),
        ]);
    }

    public function statesManualMatch(Request $request)
    {
        $request->validate([
            'state_id' => 'required|exists:curfox_states,id',
            'district_id' => 'required|exists:districts,id',
        ]);

        $state = CurfoxState::findOrFail($request->integer('state_id'));
        $state->system_district_id = $request->integer('district_id');
        $state->save();

        return response()->json([
            'success' => true,
            'message' => "State '{$state->name}' matched with District ID {$state->system_district_id}",
        ]);
    }

    public function statesCreateAndMatch(Request $request)
    {
        $request->validate([
            'state_id' => 'required|exists:curfox_states,id',
        ]);

        $state = CurfoxState::findOrFail($request->integer('state_id'));

        $provinceId = Province::query()->value('id');
        if (!$provinceId) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot create district: no province found.',
            ], 422);
        }

        $district = District::firstOrCreate(
            [
                'province_id' => $provinceId,
                'name_en' => $state->name,
            ],
            [
                'name_si' => $state->name,
                'name_ta' => $state->name,
            ]
        );

        $state->system_district_id = $district->id;
        $state->save();

        return response()->json([
            'success' => true,
            'message' => "Created/linked district '{$district->name_en}' for state '{$state->name}'",
            'district' => $district,
        ]);
    }

    public function districtsIndex()
    {
        return response()->json(
            District::select('id', 'name_en')->orderBy('name_en')->get()
        );
    }

    public function systemCities(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = City::query()
            ->select('id', 'name_en')
            ->whereNotIn('id', CurfoxCity::whereNotNull('system_city_id')->pluck('system_city_id'));

        if ($search !== '') {
            $query->where('name_en', 'like', '%' . $search . '%');
        }

        return response()->json(
            $query->orderBy('name_en')->limit(20)->get()
        );
    }

    public function cityStats()
    {
        $total = CurfoxCity::count();

        $syncedCount = CurfoxCity::whereNotNull('system_city_id')->count();

        $unsyncedNames = CurfoxCity::whereNull('system_city_id')
            ->pluck('name');

        $matchedCount = City::whereIn('name_en', $unsyncedNames)->count();
        $unmatchedCount = $unsyncedNames->count() - $matchedCount;

        return response()->json([
            'total_cities' => $total,
            'synced_count' => $syncedCount,
            'matched_count' => $matchedCount,
            'unmatched_count' => $unmatchedCount,
            'score' => ($matchedCount + $unmatchedCount) > 0
                ? round(($matchedCount / ($matchedCount + $unmatchedCount)) * 100, 2)
                : 0,
        ]);
    }

    public function cityPreview(Request $request)
    {
        $type = (string) $request->query('type', 'synced'); // synced|matched|unmatched
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min($perPage, 100));
        $search = trim((string) $request->query('search', ''));

        if ($type === 'synced') {
            $query = CurfoxCity::with('systemCity')
                ->whereNotNull('system_city_id');

            if ($search !== '') {
                $query->where('curfox_cities.name', 'like', "%{$search}%");
            }
        } elseif ($type === 'matched') {
            $query = CurfoxCity::whereNull('system_city_id')
                ->join('cities', 'curfox_cities.name', '=', 'cities.name_en')
                ->select(
                    'curfox_cities.*',
                    'cities.id as system_city_id',
                    'cities.name_en as system_city_name'
                );

            if ($search !== '') {
                $query->where('curfox_cities.name', 'like', "%{$search}%");
            }
        } else { // unmatched
            $query = CurfoxCity::whereNull('system_city_id')
                ->whereNotIn('name', function ($sub) {
                    $sub->select('name_en')->from('cities');
                });

            if ($search !== '') {
                $query->where('curfox_cities.name', 'like', "%{$search}%");
            }
        }

        $paginated = $query->paginate($perPage);

        $data = $paginated->getCollection()->map(function ($c) use ($type) {
            if ($type === 'synced') {
                return [
                    'curfox_city_id' => $c->id,
                    'curfox_city_name' => $c->name,
                    'system_city_id' => $c->systemCity->id ?? null,
                    'system_city_name' => $c->systemCity->name_en ?? null,
                ];
            }

            if ($type === 'matched') {
                return [
                    'curfox_city_id' => $c->id,
                    'curfox_city_name' => $c->name,
                    'system_city_id' => $c->system_city_id,
                    'system_city_name' => $c->system_city_name,
                    'status' => 'matched',
                ];
            }

            return [
                'curfox_city_id' => $c->id,
                'curfox_city_name' => $c->name,
                'status' => 'unmatched',
            ];
        });

        return response()->json([
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
            'data' => $data,
        ]);
    }

    public function citySyncMatched(Request $request)
    {
        $batchSize = (int) $request->get('batch', 50);
        $batchSize = max(1, min($batchSize, 200));

        $matchedCities = CurfoxCity::whereNull('system_city_id')
            ->join('cities', 'curfox_cities.name', '=', 'cities.name_en')
            ->select('curfox_cities.id as curfox_id', 'cities.id as system_id')
            ->limit($batchSize)
            ->get();

        $syncedCount = 0;

        foreach ($matchedCities as $row) {
            $curfoxCity = CurfoxCity::find($row->curfox_id);
            if ($curfoxCity) {
                $curfoxCity->system_city_id = $row->system_id;
                $curfoxCity->save();
                $syncedCount++;
            }
        }

        $remaining = CurfoxCity::whereNull('system_city_id')
            ->join('cities', 'curfox_cities.name', '=', 'cities.name_en')
            ->count();

        return response()->json([
            'success' => true,
            'synced' => $syncedCount,
            'remaining' => $remaining,
        ]);
    }

    public function cityManualMatch(Request $request)
    {
        $validated = $request->validate([
            'curfox_city_id' => ['required', 'exists:curfox_cities,id'],
            'system_city_id' => ['required', 'exists:cities,id'],
        ]);

        $curfoxCity = CurfoxCity::findOrFail($validated['curfox_city_id']);
        $curfoxCity->system_city_id = (int) $validated['system_city_id'];
        $curfoxCity->save();

        return response()->json([
            'success' => true,
            'message' => "City '{$curfoxCity->name}' linked successfully.",
        ]);
    }

    public function cityCreateAndMatch(Request $request)
    {
        $request->validate([
            'curfox_city_id' => 'required|exists:curfox_cities,id',
        ]);

        $curfoxCity = CurfoxCity::with('state')->findOrFail($request->integer('curfox_city_id'));

        // Correct mapping: use linked district from Curfox state mapping.
        $districtId = $curfoxCity->state?->system_district_id;
        if (!$districtId) {
            $districtId = District::query()->value('id');
        }

        if (!$districtId) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot create city: no district available.',
            ], 422);
        }

        $systemCity = City::create([
            'district_id' => $districtId,
            'name_en' => $curfoxCity->name,
            'name_si' => $curfoxCity->name,
            'name_ta' => $curfoxCity->name,
            'postcode' => $curfoxCity->postal_code,
            'latitude' => null,
            'longitude' => null,
        ]);

        $curfoxCity->system_city_id = $systemCity->id;
        $curfoxCity->save();

        return response()->json([
            'success' => true,
            'message' => "Created system city '{$systemCity->name_en}' and linked",
        ]);
    }

    public function cityAutoCreateSync(Request $request)
    {
        $batchSize = (int) $request->input('batch', 50);
        $batchSize = max(1, min($batchSize, 200));
        $createdCount = 0;

        $unmatchedCities = CurfoxCity::whereNull('system_city_id')
            ->whereNotIn('name', function ($sub) {
                $sub->select('name_en')->from('cities');
            })
            ->limit($batchSize)
            ->get();

        foreach ($unmatchedCities as $curfoxCity) {
            $districtId = CurfoxState::query()
                ->where('id', $curfoxCity->state_id)
                ->value('system_district_id');

            if (!$districtId) {
                $districtId = District::query()->value('id');
            }
            if (!$districtId) {
                continue;
            }

            $systemCity = City::create([
                'district_id' => $districtId,
                'name_en' => $curfoxCity->name,
                'name_si' => $curfoxCity->name,
                'name_ta' => $curfoxCity->name,
                'postcode' => $curfoxCity->postal_code,
                'latitude' => null,
                'longitude' => null,
            ]);

            $curfoxCity->system_city_id = $systemCity->id;
            $curfoxCity->save();
            $createdCount++;
        }

        $remaining = CurfoxCity::whereNull('system_city_id')
            ->whereNotIn('name', function ($sub) {
                $sub->select('name_en')->from('cities');
            })
            ->count();

        return response()->json([
            'success' => true,
            'created' => $createdCount,
            'remaining' => $remaining,
            'message' => "Created & synced {$createdCount} cities. {$remaining} remaining.",
        ]);
    }

    public function cityOrphans()
    {
        $count = City::doesntHave('curfoxCities')->count();

        return response()->json([
            'orphan_count' => $count,
        ]);
    }

    public function cityDeleteOrphans()
    {
        $orphans = City::doesntHave('curfoxCities')->pluck('id');
        $deleted = City::whereIn('id', $orphans)->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => "Deleted {$deleted} orphan cities.",
        ]);
    }

    private function districtLookupByName(): array
    {
        $lookup = [];

        $districts = District::query()
            ->select('id', 'name_en', 'name_si', 'name_ta')
            ->get();

        foreach ($districts as $district) {
            foreach ([$district->name_en, $district->name_si, $district->name_ta] as $name) {
                $normalized = $this->normalizeName($name);
                if ($normalized === '') {
                    continue;
                }

                if (!isset($lookup[$normalized])) {
                    $lookup[$normalized] = $district;
                }
            }
        }

        return $lookup;
    }

    private function findDistrictByStateName(string $stateName, array $lookup): ?District
    {
        $normalized = $this->normalizeName($stateName);
        if ($normalized === '') {
            return null;
        }

        return $lookup[$normalized] ?? null;
    }

    private function normalizeName(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $normalized = mb_strtolower(trim($value));
        return preg_replace('/\s+/', ' ', $normalized) ?? '';
    }

    private function courierConfig(): array
    {
        $baseUrl = (string) (config('services.royal_express.base_url') ?: config('services.curfox.base_url') ?: '');
        $tenant = (string) (config('services.royal_express.tenant') ?: config('services.curfox.tenant') ?: '');

        return [
            'base_url' => rtrim($baseUrl, '/'),
            'tenant' => $tenant,
        ];
    }
}
