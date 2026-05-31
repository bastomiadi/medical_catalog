<?php
/**
 * UCUM Module Class
 * 
 * Main module class for UCUM (The Unified Code for Units of Measure) catalog system
 * with Indonesian language support.
 * Supports REST API from clinicaltables.nlm.nih.gov
 */

require_once __DIR__ . '/UcumSearch.php';

class UcumModule extends UcumSearch {
    private $config;
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array
     */
    public function __construct($config) {
        $this->config = $config;
        parent::__construct($config);
    }
    
    /**
     * Search UCUM by keyword
     * 
     * @param string $keyword Search keyword
     * @return array Search results
     */
    public function searchByKeyword($keyword) {
        return parent::searchByKeyword($keyword);
    }
    
    /**
     * Get UCUM record by code
     * 
     * @param string $code UCUM code
     * @return array|null UCUM record or null if not found
     */
    public function getByCode($code) {
        return parent::getByCode($code);
    }
    
    /**
     * Get available categories
     * 
     * @return array Array of unique categories
     */
    public function getAvailableCategories() {
        return parent::getAvailableCategories();
    }
    
    /**
     * Get statistics
     * 
     * @return array Statistics data
     */
    public function getStatistics() {
        return parent::getStatistics();
    }
    
    /**
     * Get module info
     * 
     * @return array Module information
     */
    public function getModuleInfo() {
        return [
            'name' => 'UCUM',
            'version' => '1.0.0',
            'description' => 'The Unified Code for Units of Measure (API-based)',
            'language_support' => ['en', 'id'],
            'data_source' => 'https://clinicaltables.nlm.nih.gov/api/ucum/v3/search',
            'config' => $this->config
        ];
    }
    
    /**
     * Import data from file (not supported for API-based module)
     * 
     * @param string $filePath Path to the data file
     * @param string $format File format
     * @return array Import result
     * @throws Exception Always throws exception as this is not supported
     */
    public function importData($filePath, $format = 'csv') {
        throw new Exception("Import is not supported for API-based UCUM module. The module uses REST API from clinicaltables.nlm.nih.gov");
    }
}