<?php
/**
 * UCUM Module Configuration
 * Configuration for UCUM (The Unified Code for Units of Measure) catalog module
 * Supports REST API from clinicaltables.nlm.nih.gov
 */

return [
    // API configuration
    'api' => [
        'base_url' => 'https://clinicaltables.nlm.nih.gov/api/ucum/v3/search',
        'timeout' => 30
    ],
    
    // Display fields for results
    'display_fields' => [
        'cs_code',
        'name'
    ],
    
    // Search fields
    'search_fields' => [
        'cs_code',
        'name',
        'synonyms',
        'cs_code_tokens'
    ],
    
    // Default code field
    'code_field' => 'cs_code',
    
    // Search configuration
    'search' => [
        'default_limit' => 10,
        'max_limit' => 500,
        'enable_translation' => true
    ],
    
    // UCUM categories
    'categories' => [
        'Clinical' => 'Klinis',
        'Nonclinical' => 'Nonklinis',
        'Constant' => 'Konstan',
        'Obsolete' => 'Usang'
    ],
    
    // Status values
    'statuses' => [
        'active' => 'Aktif',
        'inactive' => 'Tidak Aktif'
    ]
];