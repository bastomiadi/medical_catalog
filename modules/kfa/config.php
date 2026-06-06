<?php
/**
 * KFA Module Configuration
 * Configuration for KFA (Farmaceutical Product) catalog module
 */

return [
    // Database configuration
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'dbname' => 'master_kfa',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8'
    ],
    
    // Table names
    'tables' => [
        'products' => 'products',
        'farmalkes_types' => 'farmalkes_types',
        'dosage_forms' => 'dosage_forms',
        'product_templates' => 'product_templates',
        'active_ingredients' => 'active_ingredients',
        'tags' => 'tags',
        'replacements' => 'replacements',
        'paket_obats' => 'paket_obats',
        'fornas' => 'fornas',
        'uoms' => 'uoms',
        'med_devs' => 'med_devs',
        'klasifikasi_izins' => 'klasifikasi_izins'
    ],
    
    // Indonesian term categories
    'categories' => [
        'clinical_focus' => 'Fokus Klinis',
        'value_set' => 'Value Set',
        'code' => 'Kode'
    ],
    
    // Search configuration
    'search' => [
        'default_limit' => 100,
        'max_limit' => 1000,
        'enable_fulltext' => true
    ],
    
    // Status values
    'statuses' => [
        'ACTIVE' => 'Aktif',
        'INACTIVE' => 'Tidak Aktif'
    ]
];