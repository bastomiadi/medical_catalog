<?php
/**
 * RxTerms Module Configuration
 * Configuration for Prescribable Drug Ingredients from RxTerms catalog module
 * Supports REST API from clinicaltables.nlm.nih.gov
 */

return [
    // API configuration
    'api' => [
        'base_url' => 'https://clinicaltables.nlm.nih.gov/api/drug_ingredients/v3/search',
        'timeout' => 30
    ],
    
    // Display fields for results
    'display_fields' => [
        'code',
        'name'
    ],
    
    // Search fields
    'search_fields' => [
        'name'
    ],
    
    // Default code field
    'code_field' => 'code',
    
    // Search configuration
    'search' => [
        'default_limit' => 10,
        'max_limit' => 500,
        'enable_translation' => true
    ]
];