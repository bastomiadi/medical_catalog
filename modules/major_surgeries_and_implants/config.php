<?php
/**
 * Major Surgeries and Implants Module Configuration
 */

return [
    'api_url' => 'https://clinicaltables.nlm.nih.gov/api/procedures/v3/',
    'api_key' => '',
    'tables' => [
        'procedures' => [
            'consumer_name' => 'Consumer Name',
            'procedure_code' => 'Procedure Code',
            'description' => 'Description'
        ]
    ],
    'cache_enabled' => true,
    'cache_ttl' => 3600,
    'rate_limit' => 100,
    'rate_limit_window' => 60
];