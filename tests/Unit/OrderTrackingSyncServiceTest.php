<?php

namespace Tests\Unit;

use App\Services\OrderTrackingSyncService;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

class OrderTrackingSyncServiceTest extends TestCase
{
    #[Test]
    public function it_maps_webhook_cancel_status_keys_to_cancelled(): void
    {
        $service = new OrderTrackingSyncService();
        $method = new ReflectionMethod($service, 'mapLocalStatus');

        $this->assertSame('cancelled', $method->invoke($service, 'cancelled'));
        $this->assertSame('cancelled', $method->invoke($service, 'CANCELLED'));
        $this->assertSame('cancelled', $method->invoke($service, 'order_cancelled'));
    }
}
