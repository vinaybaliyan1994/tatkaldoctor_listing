<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Public Website URL
    |--------------------------------------------------------------------------
    | Used to build public profile URLs for doctor QR codes.
    | Set PUBLIC_WEBSITE_URL in your .env to override.
    */
    'public_website_url'    => env('PUBLIC_WEBSITE_URL', 'https://taktaldoctor.com'),
    'whatsapp_business_phone' => env('WHATSAPP_BUSINESS_PHONE', '919999999999'),
];
