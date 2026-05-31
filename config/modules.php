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
    'icd10' => [
        'class' => 'IcdModule',
        'path' => __DIR__ . '/../modules/icd10/IcdModule.php',
        'config_path' => __DIR__ . '/../modules/icd10/config.php'
    ],
    'icd9_procedure' => [
        'class' => 'Icd9ProcedureModule',
        'path' => __DIR__ . '/../modules/icd9_procedure/Icd9ProcedureModule.php',
        'config_path' => __DIR__ . '/../modules/icd9_procedure/config.php'
    ],
    'hcpcs' => [
        'class' => 'HcpcsModule',
        'path' => __DIR__ . '/../modules/hcpcs/HcpcsModule.php',
        'config_path' => __DIR__ . '/../modules/hcpcs/config.php'
    ],
    'hpo' => [
        'class' => 'HpoModule',
        'path' => __DIR__ . '/../modules/hpo/HpoModule.php',
        'config_path' => __DIR__ . '/../modules/hpo/config.php'
    ],
    'icd9_diagnose' => [
        'class' => 'Icd9DiagnoseModule',
        'path' => __DIR__ . '/../modules/icd9_diagnose/Icd9DiagnoseModule.php',
        'config_path' => __DIR__ . '/../modules/icd9_diagnose/config.php'
    ],
    'icd11_codes' => [
        'class' => 'Icd11Module',
        'path' => __DIR__ . '/../modules/icd11_codes/Icd11Module.php',
        'config_path' => __DIR__ . '/../modules/icd11_codes/config.php'
    ],
    'major_surgeries_and_implants' => [
        'class' => 'MajorSurgeriesModule',
        'path' => __DIR__ . '/../modules/major_surgeries_and_implants/MajorSurgeriesModule.php',
        'config_path' => __DIR__ . '/../modules/major_surgeries_and_implants/config.php'
    ],
    'medical_conditions' => [
        'class' => 'MedicalConditionsModule',
        'path' => __DIR__ . '/../modules/medical_conditions/MedicalConditionsModule.php',
        'config_path' => __DIR__ . '/../modules/medical_conditions/config.php'
    ],
    'ucum' => [
        'class' => 'UcumModule',
        'path' => __DIR__ . '/../modules/ucum/UcumModule.php',
        'config_path' => __DIR__ . '/../modules/ucum/config.php'
    ],
    'prescribable_drug_ingredients_RxTerms' => [
        'class' => 'RxTermsModule',
        'path' => __DIR__ . '/../modules/prescribable_drug_ingredients_RxTerms/RxTermsModule.php',
        'config_path' => __DIR__ . '/../modules/prescribable_drug_ingredients_RxTerms/config.php'
    ],
    
    // Application settings
    'app' => [
        'name' => 'Medical Catalog - Indonesian Language Filter',
        'version' => '1.0.0',
        'debug' => false
    ]
];