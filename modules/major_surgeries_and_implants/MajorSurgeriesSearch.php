<?php
/**
 * Major Surgeries and Implants Search Class
 * 
 * Provides search functionality for Major Surgeries and Implants data via REST API with Indonesian language support.
 * Uses Google Translate for automatic translation from Indonesian to English.
 */

require_once __DIR__ . '/MajorSurgeriesApi.php';

class MajorSurgeriesSearch {
    protected $api;
    protected $translator;
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array
     */
    public function __construct($config) {
        $this->api = new MajorSurgeriesApi($config);
        $this->translator = new Translator();
    }
    
    /**
     * Search Major Surgeries and Implants by keyword (supports multi-word search)
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
            'sf' => 'consumer_name',
            'df' => 'consumer_name',
            'count' => 500
        ];
        
        $results = $this->api->search($params);
        
        // Normalize results to match expected format
        $data = $results['data'] ?? [];
        if (!is_array($data)) {
            $data = [];
        }
        return $this->normalizeResults($data);
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
}