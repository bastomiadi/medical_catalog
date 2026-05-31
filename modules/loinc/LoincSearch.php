<?php
/**
 * LOINC Search Class
 * 
 * Provides search functionality for LOINC data via REST API with Indonesian language support.
 * Uses Google Translate for automatic translation from Indonesian to English.
 */

require_once __DIR__ . '/LoincApi.php';

class LoincSearch {
    protected $api;
    protected $translator;
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array
     */
    public function __construct($config) {
        $this->api = new LoincApi($config);
        $this->translator = new Translator();
    }
    
    /**
     * Search LOINC by Indonesian term (uses Google Translate)
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
            'type' => 'question',
            'count' => 100,
            'ef' => 'text,LOINC_NUM,PROPERTY,METHOD_TYP,SYSTEM,STATUS,LONG_COMMON_NAME,COMPONENT'
        ];
        
        if ($category !== null) {
            $params['q'] = 'CLASS:' . $category;
        }
        
        $results = $this->api->search($params);
        
        // Normalize results to match expected format
        return $this->normalizeResults($results['data']);
    }
    
    /**
     * Search LOINC by keyword (supports multi-word search)
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
        
        // Build search parameters - search questions by default
        // Use count=500 as max allowed by API
        $params = [
            'terms' => $searchKeyword,
            'type' => 'question',
            'count' => 500
        ];
        
        if ($status !== null) {
            $params['q'] = 'STATUS:' . $status;
        }
        
        $results = $this->api->search($params);
        
        // Normalize results to match expected format
        return $this->normalizeResults($results['data']);
    }
    
    /**
     * Search LOINC forms by keyword
     * 
     * @param string $keyword Search keyword
     * @param int $limit Limit number of results
     * @return array Search results
     */
    public function searchForms($keyword, $limit = 100) {
        $translatedKeyword = $this->translator->translate($keyword);
        
        $params = [
            'terms' => $translatedKeyword,
            'type' => 'form',
            'available' => true,
            'count' => min($limit, 500)
        ];
        
        $results = $this->api->search($params);
        return $this->normalizeResults($results['data']);
    }
    
    /**
     * Search LOINC forms and sections by keyword
     * 
     * @param string $keyword Search keyword
     * @param int $limit Limit number of results
     * @return array Search results
     */
    public function searchFormsAndSections($keyword, $limit = 100) {
        $translatedKeyword = $this->translator->translate($keyword);
        
        $params = [
            'terms' => $translatedKeyword,
            'type' => 'form_and_section',
            'available' => true,
            'count' => min($limit, 500)
        ];
        
        $results = $this->api->search($params);
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
     * Search LOINC by multiple criteria
     * 
     * @param array $criteria Search criteria
     * @return array Search results
     */
    public function searchByCriteria($criteria) {
        $queryParts = [];
        
        if (!empty($criteria['component'])) {
            $queryParts[] = 'COMPONENT:' . $criteria['component'];
        }
        
        if (!empty($criteria['system'])) {
            $queryParts[] = 'SYSTEM:' . $criteria['system'];
        }
        
        if (!empty($criteria['class'])) {
            $queryParts[] = 'CLASS:' . $criteria['class'];
        }
        
        if (!empty($criteria['status'])) {
            $queryParts[] = 'STATUS:' . $criteria['status'];
        }
        
        $params = [
            'terms' => '*',
            'type' => 'question',
            'count' => 1000,
            'ef' => 'text,LOINC_NUM,PROPERTY,METHOD_TYP,SYSTEM,STATUS,LONG_COMMON_NAME,COMPONENT'
        ];
        
        if (!empty($queryParts)) {
            $params['q'] = implode(' ', $queryParts);
        }
        
        $results = $this->api->search($params);
        return $this->normalizeResults($results['data']);
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
     * Get top classes by record count
     * 
     * @param int $limit Number of top classes to return
     * @return array Top classes
     */
    public function getTopClasses($limit = 10) {
        return $this->api->getTopClasses($limit);
    }
    
    /**
     * Get available Indonesian terms for a category
     * 
     * @param string|null $category Category filter
     * @return array List of Indonesian terms
     */
    public function getIdTerms($category = null) {
        return $this->api->getIdTerms($category);
    }
    
    /**
     * Get available classes
     * 
     * @return array List of classes
     */
    public function getAvailableClasses() {
        return $this->api->getAvailableClasses();
    }
    
    /**
     * Get available systems
     * 
     * @return array List of systems
     */
    public function getAvailableSystems() {
        return $this->api->getAvailableSystems();
    }
    
    /**
     * Get available methods
     * 
     * @return array List of methods
     */
    public function getAvailableMethods() {
        return $this->api->getAvailableMethods();
    }
}