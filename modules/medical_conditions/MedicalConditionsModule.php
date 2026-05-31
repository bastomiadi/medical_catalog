<?php
/**
 * Medical Conditions Module Class
 * 
 * Main module class for Medical Conditions catalog system with Indonesian language support.
 * Supports REST API from clinicaltables.nlm.nih.gov.
 * Based on Regenstrief Institute's Medical Gopher program with over 2,400 medical conditions.
 */

require_once __DIR__ . '/MedicalConditionsSearch.php';

class MedicalConditionsModule extends MedicalConditionsSearch {
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
     * Get module info
     * 
     * @return array Module information
     */
    public function getModuleInfo() {
        return [
            'name' => 'Medical Conditions',
            'version' => '1.0.0',
            'description' => 'Medical Conditions from Regenstrief Institute Medical Gopher program (API-based)',
            'language_support' => ['en', 'id'],
            'data_source' => 'https://clinicaltables.nlm.nih.gov/api/conditions/v3/',
            'tables' => $this->config['tables'] ?? []
        ];
    }
    
    /**
     * Get Medical Conditions record by code
     * 
     * @param string $code Condition code (key_id)
     * @return array|null Record or null if not found
     */
    public function getByCode($code) {
        return $this->api->getByCode($code);
    }
    
    /**
     * Get statistics
     * 
     * @return array Statistics data
     */
    public function getStatistics() {
        return $this->api->getStatistics();
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
        throw new Exception("Import is not supported for API-based Medical Conditions module. The module uses REST API from clinicaltables.nlm.nih.gov");
    }
}