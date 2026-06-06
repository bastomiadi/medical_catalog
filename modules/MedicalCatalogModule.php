<?php
/**
 * Medical Catalog Module
 * 
 * Unified module for LOINC and SNOMED-CT catalog system with Indonesian language support.
 * Supports both REST API (LOINC) and MySQL database (SNOMED-CT) sources.
 */

require_once __DIR__ . '/Translator.php';
require_once __DIR__ . '/loinc/LoincApi.php';
require_once __DIR__ . '/loinc/LoincSearch.php';
require_once __DIR__ . '/loinc/LoincDbSearch.php';
require_once __DIR__ . '/snomed/SnomedSearch.php';
require_once __DIR__ . '/icd10/IcdApi.php';
require_once __DIR__ . '/icd10/IcdSearch.php';
require_once __DIR__ . '/icd9_procedure/Icd9ProcedureApi.php';
require_once __DIR__ . '/icd9_procedure/Icd9ProcedureSearch.php';
require_once __DIR__ . '/icd9_procedure/Icd9ProcedureModule.php';
require_once __DIR__ . '/icd9_diagnose/Icd9DiagnoseApi.php';
require_once __DIR__ . '/icd9_diagnose/Icd9DiagnoseSearch.php';
require_once __DIR__ . '/icd9_diagnose/Icd9DiagnoseModule.php';
require_once __DIR__ . '/icd11_codes/Icd11Api.php';
require_once __DIR__ . '/icd11_codes/Icd11Search.php';
require_once __DIR__ . '/icd11_codes/Icd11Module.php';
require_once __DIR__ . '/hcpcs/HcpcsApi.php';
require_once __DIR__ . '/hcpcs/HcpcsSearch.php';
require_once __DIR__ . '/hcpcs/HcpcsModule.php';
require_once __DIR__ . '/hpo/HpoApi.php';
require_once __DIR__ . '/hpo/HpoSearch.php';
require_once __DIR__ . '/hpo/HpoModule.php';
require_once __DIR__ . '/major_surgeries_and_implants/MajorSurgeriesApi.php';
require_once __DIR__ . '/major_surgeries_and_implants/MajorSurgeriesSearch.php';
require_once __DIR__ . '/major_surgeries_and_implants/MajorSurgeriesModule.php';
require_once __DIR__ . '/medical_conditions/MedicalConditionsApi.php';
require_once __DIR__ . '/medical_conditions/MedicalConditionsSearch.php';
require_once __DIR__ . '/medical_conditions/MedicalConditionsModule.php';
require_once __DIR__ . '/ucum/UcumApi.php';
require_once __DIR__ . '/ucum/UcumSearch.php';
require_once __DIR__ . '/ucum/UcumModule.php';
require_once __DIR__ . '/prescribable_drug_ingredients_RxTerms/RxTermsApi.php';
require_once __DIR__ . '/prescribable_drug_ingredients_RxTerms/RxTermsSearch.php';
require_once __DIR__ . '/prescribable_drug_ingredients_RxTerms/RxTermsModule.php';
require_once __DIR__ . '/kfa/KfaSearch.php';
require_once __DIR__ . '/kfa/KfaModule.php';

class MedicalCatalogModule {
    private $config;
    private $loincModule;
    private $snomedModule;
    private $kfaModule;
    private $icd10Module;
    private $icd9ProcedureModule;
    private $icd9DiagnoseModule;
    private $icd11Module;
    private $hcpcsModule;
    private $hpoModule;
    private $majorSurgeriesModule;
    private $medicalConditionsModule;
    private $ucumModule;
    private $rxTermsModule;
    private $activeModule;
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array
     */
    public function __construct($config) {
        $this->config = $config;
        $this->activeModule = $config['active_module'] ?? 'loinc';
        
        // Initialize LOINC module
        $loincConfig = $config['loinc'] ?? [];
        $this->loincModule = new LoincModule($loincConfig);
        
        // Initialize SNOMED module
        $snomedConfig = $config['snomed'] ?? [];
        $this->snomedModule = new SnomedModule($snomedConfig);
        
        // Initialize ICD-10 module
        $icd10Config = $config['icd10'] ?? [];
        require_once __DIR__ . '/icd10/IcdApi.php';
        require_once __DIR__ . '/icd10/IcdSearch.php';
        $this->icd10Module = new IcdModule($icd10Config);
        
        // Initialize ICD-9 Procedure module
        $icd9ProcedureConfig = $config['icd9_procedure'] ?? [];
        require_once __DIR__ . '/icd9_procedure/Icd9ProcedureApi.php';
        require_once __DIR__ . '/icd9_procedure/Icd9ProcedureSearch.php';
        $this->icd9ProcedureModule = new Icd9ProcedureModule($icd9ProcedureConfig);
        
        // Initialize HCPCS module
        $hcpcsConfig = $config['hcpcs'] ?? [];
        require_once __DIR__ . '/hcpcs/HcpcsApi.php';
        require_once __DIR__ . '/hcpcs/HcpcsSearch.php';
        $this->hcpcsModule = new HcpcsModule($hcpcsConfig);
        
        // Initialize ICD-9 Diagnoses module
        $icd9DiagnoseConfig = $config['icd9_diagnose'] ?? [];
        require_once __DIR__ . '/icd9_diagnose/Icd9DiagnoseApi.php';
        require_once __DIR__ . '/icd9_diagnose/Icd9DiagnoseSearch.php';
        $this->icd9DiagnoseModule = new Icd9DiagnoseModule($icd9DiagnoseConfig);
        
        // Initialize HPO module
        $hpoConfig = $config['hpo'] ?? [];
        require_once __DIR__ . '/hpo/HpoApi.php';
        require_once __DIR__ . '/hpo/HpoSearch.php';
        $this->hpoModule = new HpoModule($hpoConfig);
        
        // Initialize ICD-11 Codes module
        $icd11Config = $config['icd11_codes'] ?? [];
        require_once __DIR__ . '/icd11_codes/Icd11Api.php';
        require_once __DIR__ . '/icd11_codes/Icd11Search.php';
        $this->icd11Module = new Icd11Module($icd11Config);
        
        // Initialize Major Surgeries and Implants module
        $majorSurgeriesConfig = $config['major_surgeries_and_implants'] ?? [];
        require_once __DIR__ . '/major_surgeries_and_implants/MajorSurgeriesApi.php';
        require_once __DIR__ . '/major_surgeries_and_implants/MajorSurgeriesSearch.php';
        $this->majorSurgeriesModule = new MajorSurgeriesModule($majorSurgeriesConfig);
        
        // Initialize Medical Conditions module
        $medicalConditionsConfig = $config['medical_conditions'] ?? [];
        require_once __DIR__ . '/medical_conditions/MedicalConditionsApi.php';
        require_once __DIR__ . '/medical_conditions/MedicalConditionsSearch.php';
        $this->medicalConditionsModule = new MedicalConditionsModule($medicalConditionsConfig);
        
        // Initialize UCUM module
        $ucumConfig = $config['ucum'] ?? [];
        $this->ucumModule = new UcumModule($ucumConfig);
        
        // Initialize RxTerms module
        $rxTermsConfig = $config['prescribable_drug_ingredients_RxTerms'] ?? [];
        $this->rxTermsModule = new RxTermsModule($rxTermsConfig);
        
        // Initialize KFA module
        $kfaConfig = $config['kfa'] ?? [];
        $this->kfaModule = new KfaModule($kfaConfig);
    }
    
    /**
     * Set active module
     * 
     * @param string $module Module name ('loinc' or 'snomed')
     */
    public function setActiveModule($module) {
        $this->activeModule = $module;
    }
    
    /**
     * Get active module name
     * 
     * @return string Module name
     */
    public function getActiveModule() {
        return $this->activeModule;
    }
    
    /**
     * Search by keyword
     * 
     * @param string $keyword Search keyword
     * @param string|null $status Status filter
     * @return array Search results
     */
    public function searchByKeyword($keyword, $status = null) {
        if ($this->activeModule === 'snomed') {
            return $this->snomedModule->searchByKeyword($keyword, $status);
        }
        if ($this->activeModule === 'kfa') {
            return $this->kfaModule->searchByKeyword($keyword, $status);
        }
        if ($this->activeModule === 'icd10') {
            return $this->icd10Module->searchByKeyword($keyword, $status);
        }
        if ($this->activeModule === 'icd9_procedure') {
            return $this->icd9ProcedureModule->searchByKeyword($keyword, $status);
        }
        if ($this->activeModule === 'icd9_diagnose') {
            return $this->icd9DiagnoseModule->searchByKeyword($keyword, $status);
        }
        if ($this->activeModule === 'icd11_codes') {
            return $this->icd11Module->searchByKeyword($keyword, $status);
        }
        if ($this->activeModule === 'hcpcs') {
            return $this->hcpcsModule->searchByKeyword($keyword, $status);
        }
        if ($this->activeModule === 'hpo') {
            return $this->hpoModule->searchByKeyword($keyword, $status);
        }
        if ($this->activeModule === 'major_surgeries_and_implants') {
            return $this->majorSurgeriesModule->searchByKeyword($keyword, $status);
        }
        if ($this->activeModule === 'medical_conditions') {
            return $this->medicalConditionsModule->searchByKeyword($keyword, $status);
        }
        if ($this->activeModule === 'ucum') {
            return $this->ucumModule->searchByKeyword($keyword);
        }
        if ($this->activeModule === 'prescribable_drug_ingredients_RxTerms') {
            return $this->rxTermsModule->searchByKeyword($keyword);
        }
        return $this->loincModule->searchByKeyword($keyword, $status);
    }
    
    /**
     * Get record by code
     * 
     * @param string $code Code value
     * @return array|null Record or null if not found
     */
    public function getByCode($code) {
        if ($this->activeModule === 'snomed') {
            return $this->snomedModule->getByCode($code);
        }
        if ($this->activeModule === 'kfa') {
            return $this->kfaModule->getByCode($code);
        }
        if ($this->activeModule === 'icd10') {
            return $this->icd10Module->getByCode($code);
        }
        if ($this->activeModule === 'icd9_procedure') {
            return $this->icd9ProcedureModule->getByCode($code);
        }
        if ($this->activeModule === 'icd9_diagnose') {
            return $this->icd9DiagnoseModule->getByCode($code);
        }
        if ($this->activeModule === 'icd11_codes') {
            return $this->icd11Module->getByCode($code);
        }
        if ($this->activeModule === 'hcpcs') {
            return $this->hcpcsModule->getByCode($code);
        }
        if ($this->activeModule === 'hpo') {
            return $this->hpoModule->getByCode($code);
        }
        if ($this->activeModule === 'major_surgeries_and_implants') {
            return $this->majorSurgeriesModule->getByCode($code);
        }
        if ($this->activeModule === 'medical_conditions') {
            return $this->medicalConditionsModule->getByCode($code);
        }
        if ($this->activeModule === 'ucum') {
            return $this->ucumModule->getByCode($code);
        }
        if ($this->activeModule === 'prescribable_drug_ingredients_RxTerms') {
            return $this->rxTermsModule->getByCode($code);
        }
        return $this->loincModule->getByCode($code);
    }
    
    /**
     * Get statistics
     * 
     * @return array Statistics data
     */
    public function getStatistics() {
        if ($this->activeModule === 'snomed') {
            return $this->snomedModule->getStatistics();
        }
        if ($this->activeModule === 'kfa') {
            return $this->kfaModule->getStatistics();
        }
        return $this->loincModule->getStatistics();
    }
    
    /**
     * Get module info
     * 
     * @return array Module information
     */
    public function getModuleInfo() {
        return [
            'loinc' => $this->loincModule->getModuleInfo(),
            'snomed' => [
                'name' => 'SNOMED-CT',
                'version' => '1.0.0',
                'description' => 'Systematized Nomenclature of Medicine Clinical Terms',
                'language_support' => ['en', 'id'],
                'data_source' => 'MySQL Database'
            ],
            'kfa' => [
                'name' => 'KFA',
                'version' => '1.0.0',
                'description' => 'Farmaceutical Product Catalog (Master KFA)',
                'language_support' => ['en', 'id'],
                'data_source' => 'MySQL Database (master_kfa)'
            ],
            'icd10' => [
                'name' => 'ICD-10',
                'version' => '1.0.0',
                'description' => 'International Classification of Diseases, 10th Revision',
                'language_support' => ['en', 'id'],
                'data_source' => 'https://clinicaltables.nlm.nih.gov/api/icd10cm/v3/'
            ],
            'icd9_procedure' => [
                'name' => 'ICD-9 Procedure',
                'version' => '1.0.0',
                'description' => 'International Classification of Diseases, 9th Revision, Clinical Modification - Procedures',
                'language_support' => ['en', 'id'],
                'data_source' => 'https://clinicaltables.nlm.nih.gov/api/icd9cm_sg/v3/'
            ],
            'hcpcs' => [
                'name' => 'HCPCS',
                'version' => '1.0.0',
                'description' => 'Healthcare Common Procedure Coding System',
                'language_support' => ['en', 'id'],
                'data_source' => 'https://clinicaltables.nlm.nih.gov/api/hcpcs/v3/'
            ],
            'hpo' => [
                'name' => 'HPO',
                'version' => '1.0.0',
                'description' => 'Human Phenotype Ontology',
                'language_support' => ['en', 'id'],
                'data_source' => 'https://clinicaltables.nlm.nih.gov/api/hpo/v3/'
            ],
            'icd9_diagnose' => [
                'name' => 'ICD-9-CM Diagnoses',
                'version' => '1.0.0',
                'description' => 'International Classification of Diseases, 9th Revision, Clinical Modification - Diagnoses',
                'language_support' => ['en', 'id'],
                'data_source' => 'https://clinicaltables.nlm.nih.gov/api/icd9cm_dx/v3/'
            ],
            'icd11_codes' => [
                'name' => 'ICD-11 Codes',
                'version' => '1.0.0',
                'description' => 'International Classification of Diseases, 11th Revision',
                'language_support' => ['en', 'id'],
                'data_source' => 'https://clinicaltables.nlm.nih.gov/api/icd11_codes/v3/'
            ],
            'major_surgeries_and_implants' => [
                'name' => 'Major Surgeries and Implants',
                'version' => '1.0.0',
                'description' => 'Major Surgeries and Implants procedures',
                'language_support' => ['en', 'id'],
                'data_source' => 'https://clinicaltables.nlm.nih.gov/api/procedures/v3/'
            ],
            'medical_conditions' => [
                'name' => 'Medical Conditions',
                'version' => '1.0.0',
                'description' => 'Medical Conditions from Regenstrief Institute Medical Gopher program',
                'language_support' => ['en', 'id'],
                'data_source' => 'https://clinicaltables.nlm.nih.gov/api/conditions/v3/'
            ],
            'ucum' => [
                'name' => 'UCUM',
                'version' => '1.0.0',
                'description' => 'The Unified Code for Units of Measure',
                'language_support' => ['en', 'id'],
                'data_source' => 'https://clinicaltables.nlm.nih.gov/api/ucum/v3/search'
            ],
            'prescribable_drug_ingredients_RxTerms' => [
                'name' => 'Prescribable Drug Ingredients (RxTerms)',
                'version' => '1.0.0',
                'description' => 'Prescribable Drug Ingredients from RxTerms',
                'language_support' => ['en', 'id'],
                'data_source' => 'https://clinicaltables.nlm.nih.gov/api/drug_ingredients/v3/'
            ]
        ];
    }
    
    /**
     * Get available classes (LOINC) or value sets (SNOMED)
     * 
     * @return array List of available items
     */
    public function getAvailableItems() {
        if ($this->activeModule === 'snomed') {
            return $this->snomedModule->getValueSets();
        }
        return $this->loincModule->getAvailableClasses();
    }
    
    /**
     * Get autocomplete results
     * 
     * @param string $keyword Search keyword
     * @param int $limit Limit number of results
     * @return array Autocomplete results
     */
    public function getAutocompleteResults($keyword, $limit = 10) {
        $results = $this->searchByKeyword($keyword, null);
        return array_slice($results, 0, $limit);
    }
}