<?php
/**
 * UCUM Search Class
 * 
 * Provides search functionality for UCUM (The Unified Code for Units of Measure) data
 * via REST API with Indonesian language support.
 * Uses Google Translate for automatic translation from Indonesian to English.
 */

require_once __DIR__ . '/UcumApi.php';

class UcumSearch {
    protected $api;
    protected $translator;
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array
     */
    public function __construct($config) {
        $this->api = new UcumApi($config);
        $this->translator = new Translator();
    }
    
    /**
     * Search UCUM by Indonesian term (uses Google Translate)
     * 
     * @param string $idTerm Indonesian search term
     * @return array Search results
     */
    public function searchByIdTerm($idTerm) {
        // Translate Indonesian to English
        $englishTerm = $this->translator->translate($idTerm);
        
        // Search using translated term via API
        $params = [
            'terms' => $englishTerm,
            'count' => 100,
            'ef' => 'name,category,synonyms,loinc_property,guidance,source,is_simple'
        ];
        
        $results = $this->api->search($params);
        
        // Normalize results to match expected format
        return $this->normalizeResults($results['data']);
    }
    
    /**
     * Search UCUM by keyword (supports multi-word search)
     * 
     * @param string $keyword Search keyword (can be multi-word, supports Indonesian)
     * @return array Search results
     */
    public function searchByKeyword($keyword) {
        // Translate keyword if it might be Indonesian
        $translatedKeyword = $this->translator->translate($keyword);
        
        // Use the translated keyword for search
        $searchKeyword = $translatedKeyword;
        
        // Build search parameters
        $params = [
            'terms' => $searchKeyword,
            'count' => 500
        ];
        
        $results = $this->api->search($params);
        
        // Normalize results to match expected format
        return $this->normalizeResults($results['data']);
    }
    
    /**
     * Normalize result keys to lowercase for consistent access
     * 
     * @param array $results Raw results from API
     * @return array Normalized results
     */
    private function normalizeResults($results) {
        $normalized = [];
        foreach ($results as $row) {
            $normalizedRow = [];
            foreach ($row as $key => $value) {
                $normalizedRow[strtolower($key)] = $value;
            }
            $normalized[] = $normalizedRow;
        }
        return $normalized;
    }
    
    /**
     * Get record by code
     * 
     * @param string $code UCUM code
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
     * Get available categories
     * 
     * @return array List of categories
     */
    public function getAvailableCategories() {
        return ['Clinical', 'Nonclinical', 'Constant', 'Obsolete'];
    }
    
    /**
     * Search units by category
     * 
     * @param string $category Category filter
     * @param int $limit Limit number of results
     * @return array Search results
     */
    public function getByCategory($category, $limit = 100) {
        $results = $this->api->getByCategory($category, $limit);
        return $this->normalizeResults($results['data']);
    }
    
    /**
     * Search units by LOINC property
     * 
     * @param string $property LOINC property
     * @param int $limit Limit number of results
     * @return array Search results
     */
    public function getByLoincProperty($property, $limit = 100) {
        $results = $this->api->getByLoincProperty($property, $limit);
        return $this->normalizeResults($results['data']);
    }
}