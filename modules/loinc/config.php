<?php
/**
 * LOINC Module Configuration
 * Configuration for LOINC catalog module
 */

return [
    // Database configuration
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'dbname' => 'loinc_db',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8'
    ],
    
    // Table names
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
        'max_limit' => 1000,
        'enable_fulltext' => true
    ],
    
    // Status values
    'statuses' => [
        'ACTIVE' => 'Aktif',
        'INACTIVE' => 'Tidak Aktif',
        'ORDERABLE' => 'Dapat Dipesan',
        'NOT_ORDERABLE' => 'Tidak Dapat Dipesan'
    ]
];