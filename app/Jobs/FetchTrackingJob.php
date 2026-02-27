<?php

namespace App\Jobs;

use App\Services\OrderTrackingSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchTrackingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public int $orderId)
    {
    }

    public function handle(OrderTrackingSyncService $service): void
    {
        $result = $service->syncOrderById($this->orderId);

        if (!empty($result['error'])) {
            Log::warning('Tracking sync job warning', [
                'order_id' => $this->orderId,
                'error' => $result['error'],
            ]);
        }
    }

    public function failed(Throwable $e): void
    {
        Log::error('Tracking sync job permanently failed', [
            'order_id' => $this->orderId,
            'error' => $e->getMessage(),
        ]);
    }
}
