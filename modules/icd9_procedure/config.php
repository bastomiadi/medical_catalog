<?php
/**
 * ICD-9 Procedure Module Configuration
 * Configuration for ICD-9-CM Procedure catalog module
 */

return [
    // API configuration
    'api' => [
        'base_url' => 'https://clinicaltables.nlm.nih.gov/api/icd9cm_sg/v3/',
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