<?php
/**
 * HPO Module Class
 * 
 * Main module class for Human Phenotype Ontology catalog system with Indonesian language support.
 * Supports REST API from clinicaltables.nlm.nih.gov.
 */

require_once __DIR__ . '/HpoSearch.php';

class HpoModule extends HpoSearch {
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
            'name' => 'HPO',
            'version' => '1.0.0',
            'description' => 'Human Phenotype Ontology (API-based)',
            'language_support' => ['en', 'id'],
            'data_source' => 'https://clinicaltables.nlm.nih.gov/api/hpo/v3/',
            'tables' => $this->config['tables'] ?? []
        ];
    }
    
    /**
     * Get HPO record by code
     * 
     * @param string $code HPO code
     * @return array|null HPO record or null if not found
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
        throw new Exception("Import is not supported for API-based HPO module. The module uses REST API from clinicaltables.nlm.nih.gov");
    }
}