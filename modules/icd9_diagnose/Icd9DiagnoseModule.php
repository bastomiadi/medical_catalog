<?php
/**
 * ICD-9-CM Diagnoses Module Class
 * 
 * Main module class for ICD-9-CM Diagnoses catalog system with Indonesian language support.
 * Supports REST API from clinicaltables.nlm.nih.gov.
 */

require_once __DIR__ . '/Icd9DiagnoseSearch.php';

class Icd9DiagnoseModule extends Icd9DiagnoseSearch {
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
            'name' => 'ICD-9-CM Diagnoses',
            'version' => '1.0.0',
            'description' => 'International Classification of Diseases, 9th Revision, Clinical Modification - Diagnoses (API-based)',
            'language_support' => ['en', 'id'],
            'data_source' => 'https://clinicaltables.nlm.nih.gov/api/icd9cm_dx/v3/',
            'tables' => $this->config['tables'] ?? []
        ];
    }
    
    /**
     * Get ICD-9-CM Diagnoses record by code
     * 
     * @param string $code ICD-9 code
     * @return array|null ICD-9 record or null if not found
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
        throw new Exception("Import is not supported for API-based ICD-9-CM Diagnoses module. The module uses REST API from clinicaltables.nlm.nih.gov");
    }
}