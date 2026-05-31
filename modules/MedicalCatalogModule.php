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

class MedicalCatalogModule {
    private $config;
    private $loincModule;
    private $snomedModule;
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