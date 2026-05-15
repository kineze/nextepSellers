<?php

namespace App\Http\Controllers;

use App\Models\SystemData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SystemDataController extends Controller
{
    public function indexView()
    {
        return view('dashboards.admin.settings.systemData');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $query = SystemData::query()->latest();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('company_name', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('country', 'like', '%' . $search . '%')
                    ->orWhere('phone_number', 'like', '%' . $search . '%')
                    ->orWhere('fax', 'like', '%' . $search . '%');
            });
        }

        $items = $query->paginate($perPage);
        $items->getCollection()->transform(fn (SystemData $systemData) => $this->systemDataPayload($systemData));

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validated = $this->validateSystemData($request);

        $payload = $this->formatSystemData($validated);
        if ($request->hasFile('logo')) {
            $payload['logo'] = $request->file('logo')->store('system-data', 'public');
        }

        $systemData = SystemData::create($payload);

        return response()->json([
            'message' => 'System data created successfully.',
            'data' => $this->systemDataPayload($systemData),
        ], 201);
    }

    public function update(Request $request, SystemData $systemData)
    {
        $validated = $this->validateSystemData($request);

        $payload = $this->formatSystemData($validated);
        if ($request->hasFile('logo')) {
            if ($systemData->logo) {
                Storage::disk('public')->delete($systemData->logo);
            }
            $payload['logo'] = $request->file('logo')->store('system-data', 'public');
        }

        $systemData->update($payload);

        return response()->json([
            'message' => 'System data updated successfully.',
            'data' => $this->systemDataPayload($systemData->fresh()),
        ]);
    }

    public function destroy(SystemData $systemData)
    {
        if ($systemData->logo) {
            Storage::disk('public')->delete($systemData->logo);
        }

        $systemData->delete();

        return response()->json([
            'message' => 'System data deleted successfully.',
        ]);
    }

    private function validateSystemData(Request $request): array
    {
        return $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'country' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'fax' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }

    private function formatSystemData(array $validated): array
    {
        return [
            'company_name' => trim($validated['company_name']),
            'address' => isset($validated['address']) ? trim((string) $validated['address']) : null,
            'country' => trim($validated['country']),
            'phone_number' => isset($validated['phone_number']) ? trim((string) $validated['phone_number']) : null,
            'fax' => isset($validated['fax']) ? trim((string) $validated['fax']) : null,
        ];
    }

    private function systemDataPayload(SystemData $systemData): array
    {
        return [
            'id' => (int) $systemData->id,
            'company_name' => $systemData->company_name,
            'address' => $systemData->address,
            'country' => $systemData->country,
            'phone_number' => $systemData->phone_number,
            'fax' => $systemData->fax,
            'logo' => $systemData->logo,
            'logo_url' => $systemData->logo ? Storage::disk('public')->url($systemData->logo) : null,
            'created_at' => $systemData->created_at,
            'updated_at' => $systemData->updated_at,
        ];
    }
}
