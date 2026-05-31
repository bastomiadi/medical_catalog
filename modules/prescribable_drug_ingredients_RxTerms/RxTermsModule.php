<?php
/**
 * RxTerms Module Class
 * 
 * Main module class for Prescribable Drug Ingredients from RxTerms catalog system
 * with Indonesian language support.
 * Supports REST API from clinicaltables.nlm.nih.gov
 */

require_once __DIR__ . '/RxTermsSearch.php';

class RxTermsModule extends RxTermsSearch {
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
     * Search drug ingredients by keyword
     * 
     * @param string $keyword Search keyword
     * @return array Search results
     */
    public function searchByKeyword($keyword) {
        return parent::searchByKeyword($keyword);
    }
    
    /**
     * Get drug ingredient by RXCUI
     * 
     * @param string $rxcui RxNorm Unique Identifier
     * @return array|null Drug ingredient record or null if not found
     */
    public function getByCode($rxcui) {
        return parent::getByCode($rxcui);
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
            'name' => 'Prescribable Drug Ingredients (RxTerms)',
            'version' => '1.0.0',
            'description' => 'Prescribable Drug Ingredients from RxTerms (API-based)',
            'language_support' => ['en', 'id'],
            'data_source' => 'https://clinicaltables.nlm.nih.gov/api/drug_ingredients/v3/search',
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
        throw new Exception("Import is not supported for API-based RxTerms module. The module uses REST API from clinicaltables.nlm.nih.gov");
    }
}