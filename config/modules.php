<?php
/**
 * Main Modules Configuration
 * Central configuration for all catalog modules
 */

return [
    // Default module
    'default_module' => 'loinc',
    
    // Module configurations for ModuleRegistry
    'loinc' => [
        'class' => 'LoincModule',
        'path' => __DIR__ . '/../modules/loinc/LoincModule.php',
        'config_path' => __DIR__ . '/../modules/loinc/config.php'
    ],
    'snomed' => [
        'class' => 'SnomedModule',
        'path' => __DIR__ . '/../modules/snomed/SnomedModule.php',
        'config_path' => __DIR__ . '/../modules/snomed/config.php'
    ],
    
    // Application settings
    'app' => [
        'name' => 'Medical Catalog - Indonesian Language Filter',
        'version' => '1.0.0',
        'debug' => false
    ]
];