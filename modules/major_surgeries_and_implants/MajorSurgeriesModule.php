<?php
/**
 * Major Surgeries and Implants Module Class
 * 
 * Main module class for Major Surgeries and Implants catalog system with Indonesian language support.
 * Supports REST API from clinicaltables.nlm.nih.gov.
 */

require_once __DIR__ . '/MajorSurgeriesSearch.php';

class MajorSurgeriesModule extends MajorSurgeriesSearch {
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
            'name' => 'Major Surgeries and Implants',
            'version' => '1.0.0',
            'description' => 'Major Surgeries and Implants procedures (API-based)',
            'language_support' => ['en', 'id'],
            'data_source' => 'https://clinicaltables.nlm.nih.gov/api/procedures/v3/',
            'tables' => $this->config['tables'] ?? []
        ];
    }
    
    /**
     * Get Major Surgeries and Implants record by code
     * 
     * @param string $code Procedure code
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
        throw new Exception("Import is not supported for API-based Major Surgeries and Implants module. The module uses REST API from clinicaltables.nlm.nih.gov");
    }
}