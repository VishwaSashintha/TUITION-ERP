<?php

declare(strict_types=1);

/**
 * TuitionPro ERP — Single-Institute Installation Configuration
 *
 * Each customer deployment (e.g., Iskole, Pasala, Apex) operates with its own
 * dedicated configuration, database, branding, and contact details.
 */
return [
    'name'            => env('INSTITUTE_NAME', 'Apex Higher Education Institute'),
    'code'            => env('INSTITUTE_CODE', 'apex'),
    'email'           => env('INSTITUTE_EMAIL', 'info@apexeducation.lk'),
    'phone'           => env('INSTITUTE_PHONE', '+94 11 234 5678'),
    'address'         => env('INSTITUTE_ADDRESS', 'No. 123, Galle Road, Colombo 03, Sri Lanka'),
    'timezone'        => env('INSTITUTE_TIMEZONE', 'Asia/Colombo'),
    'currency'        => env('INSTITUTE_CURRENCY', 'LKR'),
    'currency_symbol' => env('INSTITUTE_CURRENCY_SYMBOL', 'Rs. '),
    'logo_path'       => env('INSTITUTE_LOGO_PATH', 'assets/images/logo.svg'),
    'logo_white_path' => env('INSTITUTE_LOGO_WHITE_PATH', 'assets/images/logo-white.svg'),
    'logo_mark_path'  => env('INSTITUTE_LOGO_MARK_PATH', 'assets/images/logo-mark.svg'),
];

