<?php

namespace App\Http\Controllers;

use App\Models\DeliveryWebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeliveryWebhookController extends Controller
{
    public function configuration()
    {
        return response()->json([
            'webhook_url' => url('/api/delivery/webhook'),
            'method' => 'POST',
            'header_name' => 'X-Webhook-Key',
            'key_configured' => config('services.delivery_webhook.key') !== null && config('services.delivery_webhook.key') !== '',
            'required_fields' => [
                'waybill_no',
                'status_key',
                'status',
            ],
            'sample_payload' => [
                'waybill_no' => 'WB123456',
                'status_key' => 'delivered',
                'status' => 'Delivered',
            ],
        ]);
    }

    public function logs(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $query = DeliveryWebhookLog::query()->latest('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('waybill_no', 'like', '%' . $search . '%')
                    ->orWhere('status_key', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%');
            });
        }

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $configuredKey = (string) config('services.delivery_webhook.key');
        $requestKey = (string) $request->header('X-Webhook-Key');

        if ($configuredKey === '') {
            Log::warning('Delivery webhook rejected: missing DELIVERY_WEBHOOK_KEY configuration.');

            return response()->json([
                'message' => 'Webhook is not configured.',
            ], 503);
        }

        if ($requestKey === '' || !hash_equals($configuredKey, $requestKey)) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        $validated = $request->validate([
            'waybill_no' => ['required', 'string', 'max:255'],
            'status_key' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:255'],
        ]);

        $log = DeliveryWebhookLog::create([
            'waybill_no' => $validated['waybill_no'],
            'raw_data' => $request->all(),
            'status_key' => $validated['status_key'],
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Delivery webhook logged successfully.',
            'data' => [
                'id' => $log->id,
                'waybill_no' => $log->waybill_no,
                'status_key' => $log->status_key,
                'status' => $log->status,
                'created_at' => $log->created_at,
            ],
        ], 201);
    }
}
