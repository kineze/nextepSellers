<?php

namespace Tests\Unit;

use App\Models\ProductLevel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ProductLevelAffiliateCommissionTest extends TestCase
{
    #[Test]
    public function it_calculates_percentage_affiliate_commission_for_all_units(): void
    {
        $level = new ProductLevel([
            'affiliate_commission_type' => 'percentage',
            'affiliate_commission' => 7.5,
        ]);

        $this->assertSame(225.0, $level->calculateAffiliateCommission(1000, 3));
    }

    #[Test]
    public function it_calculates_fixed_affiliate_commission_for_each_unit(): void
    {
        $level = new ProductLevel([
            'affiliate_commission_type' => 'amount',
            'affiliate_commission' => 125,
        ]);

        $this->assertSame(375.0, $level->calculateAffiliateCommission(1000, 3));
    }

    #[Test]
    public function legacy_rows_default_to_percentage_calculation(): void
    {
        $level = new ProductLevel([
            'affiliate_commission' => 10,
        ]);

        $this->assertSame(200.0, $level->calculateAffiliateCommission(1000, 2));
    }
}
