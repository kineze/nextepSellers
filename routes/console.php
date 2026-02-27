<?php

use App\Jobs\FetchTrackingJob;
use App\Services\OrderTrackingSyncService;
use App\Models\Order;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tracking:fetch', function (OrderTrackingSyncService $service) {
    $startedAt = now();
    $this->info('Starting tracking job dispatch at ' . $startedAt->toDateTimeString());

    $context = $service->validateTrackingContext();
    if (!($context['ready'] ?? false)) {
        $this->warn((string) ($context['message'] ?? 'Tracking context is not ready.'));
        return;
    }

    $dispatched = 0;
    Order::query()
        ->where('status', 'shipped')
        ->whereNotNull('waybill_no')
        ->orderBy('id')
        ->chunkById(200, function ($orders) use (&$dispatched) {
            foreach ($orders as $order) {
                FetchTrackingJob::dispatch((int) $order->id)->onQueue('tracking');
                $dispatched++;
            }
        });

    $this->line('Dispatched jobs: ' . $dispatched);
    $this->info('Tracking job dispatch finished at ' . now()->toDateTimeString());
})->purpose('Dispatch tracking sync jobs for shipped orders and seller progression updates.');

Schedule::command('tracking:fetch')
    ->everyThirtyMinutes()
    ->timezone('Asia/Colombo')
    ->between('09:00', '16:00')
    ->withoutOverlapping()
    ->runInBackground();
