<?php

return [
    'payfast' => [
        'merchant_id' => env('PAYFAST_MERCHANT_ID'),
        'merchant_key' => env('PAYFAST_MERCHANT_KEY'),
        'passphrase' => env('PAYFAST_PASSPHRASE'),
        'webhook_secret' => env('PAYFAST_WEBHOOK_SECRET'),
    ],
];
