<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identitas Aplikasi
    |--------------------------------------------------------------------------
    */

    'name' => 'CosplayIn',

    'home_banner' => [
        'title' => 'Sewa Kostum Cosplay Favoritmu di CosplayIn',
        'subtitle' => 'Temukan kostum berkualitas dari tenant terpercaya di seluruh Indonesia. Mulai dari Rp150.000 per hari.',
        'cta' => 'Mulai Menyewa',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway
    |--------------------------------------------------------------------------
    | TODO: Gateway final (Midtrans/Xendit) belum ditentukan. Saat ini
    | menggunakan implementasi mock. Kredensial dibaca dari file .env.
    */

    'payment' => [
        'gateway' => env('PAYMENT_GATEWAY', 'mock'),

        'mock' => [
            'key' => env('MOCK_PAYMENT_KEY', 'cosplayin-mock-secret'),
            'allow_mock_success' => env('MOCK_PAYMENT_ALLOW_SUCCESS', true),
        ],

        // Placeholder konfigurasi Midtrans (belum dipakai).
        'midtrans' => [
            'server_key' => env('MIDTRANS_SERVER_KEY'),
            'client_key' => env('MIDTRANS_CLIENT_KEY'),
            'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        ],

        // Placeholder konfigurasi Xendit (belum dipakai).
        'xendit' => [
            'secret_key' => env('XENDIT_SECRET_KEY'),
            'callback_token' => env('XENDIT_CALLBACK_TOKEN'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Aturan Sewa (nilai sementara, dapat diubah admin melalui settings)
    |--------------------------------------------------------------------------
    | TODO: Nilai final belum ditentukan oleh pemilik produk.
    */

    'rental' => [
        'min_days' => (int) env('RENTAL_MIN_DAYS', 1),
        'max_days' => (int) env('RENTAL_MAX_DAYS', 14),
    ],

    'deposit' => (int) env('DEFAULT_DEPOSIT', 100000),

    'shipping_fee' => (int) env('DEFAULT_SHIPPING_FEE', 25000),

    /*
    |--------------------------------------------------------------------------
    | Denda Keterlambatan
    |--------------------------------------------------------------------------
    | TODO: Formula final belum diputuskan. Nilai awal: tarif per hari dan
    | batas maksimum denda. Dapat diedit admin melalui tabel settings.
    */

    'late_fee' => [
        'per_day' => (int) env('LATE_FEE_PER_DAY', 50000),
        'max' => (int) env('MAX_LATE_FEE', 500000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Komisi Platform
    |--------------------------------------------------------------------------
    | TODO: Persentase komisi final belum ditentukan.
    */

    'commission_percent' => (float) env('PLATFORM_COMMISSION_PERCENT', 10),

    /*
    |--------------------------------------------------------------------------
    | Kebijakan Pembatalan & Refund
    |--------------------------------------------------------------------------
    | TODO: Kebijakan final belum ditentukan. Teks di bawah hanya placeholder.
    */

    'cancellation' => [
        'policy_text' => 'Kebijakan pembatalan dan refund belum final. Hubungi admin untuk pengajuan pembatalan.',
        'refund_allowed' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Database
    |--------------------------------------------------------------------------
    | TODO: Interval backup final belum ditentukan.
    */

    'backup' => [
        'interval_hours' => (int) env('DB_BACKUP_INTERVAL_HOURS', 24),
        'path' => storage_path('app/backups'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifikasi
    |--------------------------------------------------------------------------
    | TODO: Kanal final (email/SMS/WhatsApp/in-app) belum ditentukan.
    | Untuk saat ini digunakan kanal database (in-app) dan email.
    */

    'notifications' => [
        'channels' => ['database', 'mail'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rekening Pembayaran Manual (placeholder)
    |--------------------------------------------------------------------------
    */

    'bank_accounts' => [
        'transfer' => [
            'bank' => 'Bank Contoh',
            'number' => '1234567890',
            'holder' => 'PT CosplayIn Contoh',
        ],
        'ewallet' => [
            'provider' => 'E-Wallet Contoh',
            'number' => '081234567890',
            'holder' => 'CosplayIn',
        ],
        'qris' => [
            'note' => 'QRIS mock - belum terhubung payment gateway.',
        ],
    ],

];
