<?php
/**
 * LOINC Module Configuration
 * Configuration for LOINC catalog module
 * Supports both REST API and MySQL database sources
 */

return [
    // Set to true to use MySQL database, false to use REST API
    'use_database' => false,
    
    // Database configuration (for database mode)
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'dbname' => 'loinc_db',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8'
    ],
    
    // API configuration (for API mode)
    'api' => [
        'base_url' => 'https://clinicaltables.nlm.nih.gov/api/loinc_items/v3/',
        'timeout' => 30
    ],
    
    // Table names (for database mode)
    'tables' => [
        'loinc' => 'loinc',
        'id_mapping' => 'id_mapping',
        'id_translations' => 'id_translations',
        'import_log' => 'import_log'
    ],
    
    // Indonesian term categories
    'categories' => [
        'component' => 'Komponen',
        'property' => 'Sifat',
        'system' => 'Sistem',
        'method' => 'Metode',
        'class' => 'Kelas'
    ],
    
    // Search configuration
    'search' => [
        'default_limit' => 100,
        'max_limit' => 500,
        'enable_translation' => true
    ],
    
    // Status values
    'statuses' => [
        'ACTIVE' => 'Aktif',
        'INACTIVE' => 'Tidak Aktif',
        'ORDERABLE' => 'Dapat Dipesan',
        'NOT_ORDERABLE' => 'Tidak Dapat Dipesan'
    ]
];