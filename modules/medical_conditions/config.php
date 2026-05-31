<?php
/**
 * Medical Conditions Module Configuration
 */

return [
    'api_url' => 'https://clinicaltables.nlm.nih.gov/api/conditions/v3/',
    'api_key' => '',
    'tables' => [
        'conditions' => [
            'key_id' => 'Key ID',
            'primary_name' => 'Primary Name',
            'consumer_name' => 'Consumer Name',
            'term_icd9_code' => 'ICD-9 Code',
            'term_icd9_text' => 'ICD-9 Text'
        ]
    ],
    'cache_enabled' => true,
    'cache_ttl' => 3600,
    'rate_limit' => 100,
    'rate_limit_window' => 60
];