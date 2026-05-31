<?php
/**
 * ICD-11 Codes Module Configuration
 * Configuration for ICD-11 Codes catalog module
 */

return [
    // API configuration
    'api' => [
        'base_url' => 'https://clinicaltables.nlm.nih.gov/api/icd11_codes/v3/',
        'timeout' => 30
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
        'INACTIVE' => 'Tidak Aktif'
    ]
];