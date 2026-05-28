<?php
/**
 * SNOMED-CT Module Configuration
 * Configuration for SNOMED-CT catalog module
 */

return [
    // Database configuration
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'dbname' => 'snomed_db',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8'
    ],
    
    // Table names
    'tables' => [
        'snomed' => 'snomed_ct',
        'id_mapping' => 'snomed_id_mapping',
        'id_translations' => 'snomed_id_translations',
        'import_log' => 'snomed_import_log'
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