<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Batas Waktu Pembayaran (Deadline Hours)
    |--------------------------------------------------------------------------
    |
    | Batas waktu transfer dalam hitungan jam sejak pesanan dibuat.
    |
    */
    'deadline_hours' => 24,

    /*
    |--------------------------------------------------------------------------
    | Rekening Bank Tujuan Transfer
    |--------------------------------------------------------------------------
    |
    | Daftar rekening bank resmi yang digunakan toko online Modeva untuk
    | menerima pembayaran transfer manual dari pelanggan.
    |
    */
    'banks' => [
        [
            'code' => 'bca',
            'bank_name' => 'BCA',
            'account_number' => '8830123456',
            'account_name' => 'PT Modeva Fashion Indonesia',
        ],
        [
            'code' => 'bni',
            'bank_name' => 'BNI',
            'account_number' => '0823456789',
            'account_name' => 'PT Modeva Fashion Indonesia',
        ],
        [
            'code' => 'mandiri',
            'bank_name' => 'Bank Mandiri',
            'account_number' => '1370001234567',
            'account_name' => 'PT Modeva Fashion Indonesia',
        ],
        [
            'code' => 'bri',
            'bank_name' => 'BRI',
            'account_number' => '034101001234530',
            'account_name' => 'PT Modeva Fashion Indonesia',
        ],
    ],
];
