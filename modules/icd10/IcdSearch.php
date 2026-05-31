<?php
/**
 * ICD-10 Search Class
 * 
 * Provides search functionality for ICD-10 data via REST API with Indonesian language support.
 * Uses Google Translate for automatic translation from Indonesian to English.
 */

require_once __DIR__ . '/IcdApi.php';

class IcdSearch {
    protected $api;
    protected $translator;
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array
     */
    public function __construct($config) {
        $this->api = new IcdApi($config);
        $this->translator = new Translator();
    }
    
    /**
     * Search ICD-10 by Indonesian term (uses Google Translate)
     * 
     * @param string $idTerm Indonesian search term
     * @param string|null $category Category filter
     * @return array Search results
     */
    public function searchByIdTerm($idTerm, $category = null) {
        // Translate Indonesian to English
        $englishTerm = $this->translator->translate($idTerm);
        
        // Search using translated term via API
        $params = [
            'terms' => $englishTerm,
            'sf' => 'code,name',
            'count' => 100,
            'df' => 'code,name'
        ];
        
        $results = $this->api->search($params);
        
        // Normalize results to match expected format
        return $this->normalizeResults($results['data']);
    }
    
    /**
     * Search ICD-10 by keyword (supports multi-word search)
     * 
     * @param string $keyword Search keyword (can be multi-word, supports Indonesian)
     * @param string|null $status Status filter
     * @return array Search results
     */
    public function searchByKeyword($keyword, $status = null) {
        // Translate keyword if it might be Indonesian
        $translatedKeyword = $this->translator->translate($keyword);
        
        // Use the translated keyword for search
        $searchKeyword = $translatedKeyword;
        
        // Build search parameters
        $params = [
            'terms' => $searchKeyword,
            'sf' => 'code,name',
            'df' => 'code,name',
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
        return [];
    }
}