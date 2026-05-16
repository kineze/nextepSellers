<?php

return [
    // LKR amount required to earn 1 point.
    'lkr_per_point' => (float) env('SELLER_POINTS_LKR_PER_POINT', 100),

    // Days after the seller's first successful order before the first auto invoice.
    'first_invoice_delay_days' => (int) env('SELLER_FIRST_INVOICE_DELAY_DAYS', 7),

    // Days between automatic invoices after the seller's first invoice.
    'invoice_cycle_days' => (int) env('SELLER_INVOICE_CYCLE_DAYS', 7),

    // Delivery score percentage where failed-delivery penalties begin.
    'penalty_limit_start_for_failed_to_delivery' => (int) env('PENALTY_LIMIT_START_FOR_FAILED_TO_DELIVERY', 60),
];
