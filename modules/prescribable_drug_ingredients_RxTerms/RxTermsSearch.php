<?php
/**
 * RxTerms Search Class
 * 
 * Provides search functionality for Prescribable Drug Ingredients from RxTerms data
 * via REST API with Indonesian language support.
 * Uses Google Translate for automatic translation from Indonesian to English.
 */

require_once __DIR__ . '/RxTermsApi.php';

class RxTermsSearch {
    protected $api;
    protected $translator;
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array
     */
    public function __construct($config) {
        $this->api = new RxTermsApi($config);
        $this->translator = new Translator();
    }
    
    /**
     * Search drug ingredients by Indonesian term (uses Google Translate)
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
            'df' => 'code,name'
        ];
        
        $results = $this->api->search($params);
        
        // Normalize results to match expected format
        return $this->normalizeResults($results['data']);
    }
    
    /**
     * Search drug ingredients by keyword (supports multi-word search)
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
            'count' => 500,
            'df' => 'code,name'
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
     * Get drug ingredient by RXCUI
     * 
     * @param string $rxcui RxNorm Unique Identifier
     * @return array|null Record or null if not found
     */
    public function getByCode($rxcui) {
        return $this->api->getByCode($rxcui);
    }
    
    /**
     * Get statistics
     * 
     * @return array Statistics data
     */
    public function getStatistics() {
        return $this->api->getStatistics();
    }
}