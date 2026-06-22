<?php

return [
    'trial_ai_credits' => 5,

    'plans' => [
        'tenant' => [
            'ai_premium' => [
                'name' => 'Fitur AI Premium',
                'price' => 15000,
                'duration_days' => 30,
                'ai_request_limit' => -1, // unlimited
                'features' => [
                    'Akses AI unlimited',
                    'AI Finder kos sesuai kebutuhan',
                    'Analisis area dan fasilitas sekitar',
                ],
            ],
        ],
        'owner' => [
            'dashboard_access' => [
                'name' => 'Akses Dashboard & Laporan',
                'price' => 50000,
                'duration_days' => 30,
                'features' => [
                    'Akses Dashboard Utama',
                    'Laporan Keuangan Otomatis',
                ],
            ],
            'ai_advisor_premium' => [
                'name' => 'AI Advisor Premium',
                'price' => 150000,
                'duration_days' => 30,
                'ai_request_limit' => -1, // unlimited
                'features' => [
                    'Semua fitur Akses Dashboard',
                    'Fitur Analisa Keuangan berbasis AI',
                    'Rekomendasi Bisnis Cerdas',
                ],
            ],
            'boost_premium' => [
                'name' => 'Boost/Iklan Premium',
                'price' => 100000,
                'duration_days' => 30,
                'features' => [
                    'Listing muncul teratas',
                    'Akses dashboard manajemen',
                ],
            ],
        ],
    ],
    
    'service_fee_percentage' => 2, // 2% dari nilai transaksi
];
