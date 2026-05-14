<?php

return [
    // LKR amount required to earn 1 point.
    'lkr_per_point' => (float) env('SELLER_POINTS_LKR_PER_POINT', 100),

    // Days after the seller's first successful order before the first auto invoice.
    'first_invoice_delay_days' => (int) env('SELLER_FIRST_INVOICE_DELAY_DAYS', 7),
];
