<?php
/**
 * LOINC API Class
 * 
 * Provides REST API access to LOINC data from clinicaltables.nlm.nih.gov
 * Implements the API for LOINC Questions and Forms specification.
 */

require_once __DIR__ . '/../Translator.php';

class LoincApi {
    private $config;
    private $translator;
    
    // API Base URLs
    const SEARCH_API_BASE = 'https://clinicaltables.nlm.nih.gov/api/loinc_items/v3/search';
    const ANSWERS_API_BASE = 'https://clinicaltables.nlm.nih.gov/loinc_answers';
    const FORM_DEFINITIONS_API_BASE = 'https://clinicaltables.nlm.nih.gov/loinc_form_definitions';
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array
     */
    public function __construct($config) {
        $this->config = $config;
        $this->translator = new Translator();
    }
    
    /**
     * Search LOINC questions and/or forms
     * 
     * @param array $params Search parameters:
     *   - terms: (required) search string
     *   - type: 'question', 'form', 'panel', or 'form_and_section'
     *   - maxList: max results (default 7, max 500)
     *   - count: page size (default 7, max 500)
     *   - offset: pagination offset (default 0)
     *   - q: additional query string
     *   - excludeCopyrighted: boolean
     *   - available: boolean (for forms)
     *   - df: display fields
     *   - sf: search fields
     *   - cf: code field
     *   - ef: extra fields
     * @return array Search results
     */
    public function search($params) {
        // Translate terms if provided (Indonesian to English)
        if (isset($params['terms'])) {
            $params['terms'] = $this->translator->translate($params['terms']);
        }
        
        $url = $this->buildSearchUrl($params);
        $response = $this->makeApiRequest($url);
        
        return $this->parseSearchResponse($response, $params);
    }
    
    /**
     * Get answer list for a question
     * 
     * @param string $loincNum LOINC code
     * @return array Answer list
     */
    public function getAnswers($loincNum) {
        $url = self::ANSWERS_API_BASE . '?loinc_num=' . urlencode($loincNum);
        $response = $this->makeApiRequest($url);
        
        return json_decode($response, true) ?: [];
    }
    
    /**
     * Get form definition
     * 
     * @param string $loincNum LOINC code for the form
     * @return array Form definition
     */
    public function getFormDefinition($loincNum) {
        $url = self::FORM_DEFINITIONS_API_BASE . '?loinc_num=' . urlencode($loincNum);
        $response = $this->makeApiRequest($url);
        
        return json_decode($response, true) ?: [];
    }
    
    /**
     * Get LOINC record by code
     * 
     * @param string $loincNum LOINC code
     * @return array|null LOINC record or null if not found
     */
    public function getByCode($loincNum) {
        $params = [
            'terms' => $loincNum,
            'type' => 'question',
            'ef' => 'text,LOINC_NUM,PROPERTY,METHOD_TYP,SYSTEM,STATUS,LONG_COMMON_NAME,COMPONENT'
        ];
        
        $results = $this->search($params);
        
        if (!empty($results['data']) && isset($results['data'][0])) {
            return $results['data'][0];
        }
        
        return null;
    }
    
    /**
     * Search questions by keyword
     * 
     * @param string $keyword Search keyword
     * @param string|null $status Status filter
     * @param int $limit Limit number of results
     * @return array Search results
     */
    public function searchQuestions($keyword, $status = null, $limit = 100) {
        $params = [
            'terms' => $keyword,
            'type' => 'question',
            'count' => min($limit, 500),
            'ef' => 'text,LOINC_NUM,PROPERTY,METHOD_TYP,SYSTEM,STATUS,LONG_COMMON_NAME,COMPONENT'
        ];
        
        if ($status) {
            $params['q'] = 'STATUS:' . $status;
        }
        
        return $this->search($params);
    }
    
    /**
     * Search forms
     * 
     * @param string $keyword Search keyword
     * @param int $limit Limit number of results
     * @return array Search results
     */
    public function searchForms($keyword, $limit = 100) {
        $params = [
            'terms' => $keyword,
            'type' => 'form',
            'available' => 'true',
            'count' => min($limit, 500),
            'ef' => 'text,LOINC_NUM,LONG_COMMON_NAME'
        ];
        
        return $this->search($params);
    }
    
    /**
     * Search forms and sections
     * 
     * @param string $keyword Search keyword
     * @param int $limit Limit number of results
     * @return array Search results
     */
    public function searchFormsAndSections($keyword, $limit = 100) {
        $params = [
            'terms' => $keyword,
            'type' => 'form_and_section',
            'available' => 'true',
            'count' => min($limit, 500),
            'ef' => 'text,LOINC_NUM,LONG_COMMON_NAME'
        ];
        
        return $this->search($params);
    }
    
    /**
     * Get available classes from LOINC data
     * 
     * @return array List of classes
     */
    public function getAvailableClasses() {
        // Use a broad search to get all classes
        $params = [
            'terms' => '*',
            'type' => 'question',
            'count' => 1,
            'ef' => 'CLASS'
        ];
        
        // This is a workaround - we'll search for common terms and extract classes
        // The API doesn't have a direct endpoint for getting all classes
        $results = $this->search($params);
        
        // Return empty array - classes should be obtained from search results
        return [];
    }
    
    /**
     * Get available systems from LOINC data
     * 
     * @return array List of systems
     */
    public function getAvailableSystems() {
        return [];
    }
    
    /**
     * Get available methods from LOINC data
     * 
     * @return array List of methods
     */
    public function getAvailableMethods() {
        return [];
    }
    
    /**
     * Get statistics (placeholder - API doesn't provide this directly)
     * 
     * @return array Statistics data
     */
    public function getStatistics() {
        return [
            'total_records' => 'N/A (API-based)',
            'active_records' => 'N/A (API-based)',
            'class_count' => 'N/A (API-based)',
            'panel_count' => 'N/A (API-based)'
        ];
    }
    
    /**
     * Build search URL with parameters
     * 
     * @param array $params Search parameters
     * @return string Full URL
     */
    private function buildSearchUrl($params) {
        $queryParams = [];
        
        // Map parameter names to API parameter names
        $paramMap = [
            'terms' => 'terms',
            'type' => 'type',
            'maxList' => 'maxList',
            'count' => 'count',
            'offset' => 'offset',
            'q' => 'q',
            'excludeCopyrighted' => 'excludeCopyrighted',
            'available' => 'available',
            'df' => 'df',
            'sf' => 'sf',
            'cf' => 'cf',
            'ef' => 'ef'
        ];
        
        foreach ($paramMap as $key => $apiParam) {
            if (isset($params[$key]) && $params[$key] !== '' && $params[$key] !== null) {
                // Handle boolean values
                if (is_bool($params[$key])) {
                    $queryParams[] = $apiParam . '=' . ($params[$key] ? 'true' : 'false');
                }
                // Don't URL encode numeric values
                elseif (is_numeric($params[$key])) {
                    $queryParams[] = $apiParam . '=' . $params[$key];
                } else {
                    $queryParams[] = $apiParam . '=' . urlencode($params[$key]);
                }
            }
        }
        
        return self::SEARCH_API_BASE . '?' . implode('&', $queryParams);
    }
    
    /**
     * Make API request with error handling
     * 
     * @param string $url Full URL
     * @return string Response body
     */
    private function makeApiRequest($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'LOINC-PHP-Client/1.0');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("LOINC API Error: " . $error);
        }
        
        if ($httpCode !== 200) {
            error_log("LOINC API HTTP Error: " . $httpCode . " for URL: " . $url);
            error_log("Response: " . $response);
            return '[]';
        }
        
        return $response ?: '[]';
    }
    
    /**
     * Parse search response from API
     * 
     * @param string $response Raw JSON response
     * @param array $params Original search parameters
     * @return array Parsed results
     */
    private function parseSearchResponse($response, $params) {
        $data = json_decode($response, true);
        
        if (!$data || !is_array($data)) {
            return [
                'total' => 0,
                'data' => [],
                'codes' => [],
                'extra' => []
            ];
        }
        
        // API response format: [total, codes, extra, display, codeSystem]
        $total = $data[0] ?? 0;
        $codes = $data[1] ?? [];
        $extra = $data[2] ?? [];
        $display = $data[3] ?? [];
        
        // Build data array
        $results = [];
        $codeField = $params['cf'] ?? 'LOINC_NUM';
        
        for ($i = 0; $i < count($codes); $i++) {
            // Get text from display array or extra fields
            $text = '';
            if (isset($display[$i])) {
                // Display can be an array of strings or a string
                if (is_array($display[$i])) {
                    $text = $display[$i][0] ?? '';
                } else {
                    $text = $display[$i];
                }
            }
            
            $row = [
                $codeField => $codes[$i],
                'text' => $text
            ];
            
            // Add extra fields
            foreach ($extra as $field => $values) {
                if (isset($values[$i])) {
                    $row[$field] = $values[$i];
                }
            }
            
            $results[] = $row;
        }
        
        return [
            'total' => $total,
            'data' => $results,
            'codes' => $codes,
            'extra' => $extra,
            'display' => $display
        ];
    }
    
    /**
     * Search by Indonesian term (uses Google Translate)
     * 
     * @param string $idTerm Indonesian search term
     * @param string|null $category Category filter
     * @return array Search results
     */
    public function searchByIdTerm($idTerm, $category = null) {
        $englishTerm = $this->translator->translate($idTerm);
        
        $params = [
            'terms' => $englishTerm,
            'type' => 'question',
            'count' => 100,
            'ef' => 'text,LOINC_NUM,PROPERTY,METHOD_TYP,SYSTEM,STATUS,LONG_COMMON_NAME,COMPONENT'
        ];
        
        if ($category) {
            $params['q'] = 'CLASS:' . $category;
        }
        
        return $this->search($params);
    }
    
    /**
     * Search by keyword
     * 
     * @param string $keyword Search keyword
     * @param string|null $status Status filter
     * @return array Search results
     */
    public function searchByKeyword($keyword, $status = null) {
        $params = [
            'terms' => $keyword,
            'type' => 'question',
            'count' => 1000,
            'ef' => 'text,LOINC_NUM,PROPERTY,METHOD_TYP,SYSTEM,STATUS,LONG_COMMON_NAME,COMPONENT'
        ];
        
        if ($status) {
            $params['q'] = 'STATUS:' . $status;
        }
        
        return $this->search($params);
    }
    
    /**
     * Search by multiple criteria
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
        
        return $this->search($params);
    }
    
    /**
     * Get panels
     * 
     * @return array Array of panel records
     */
    public function getPanels() {
        $params = [
            'terms' => '*',
            'type' => 'form',
            'available' => 'true',
            'count' => 500,
            'ef' => 'text,LOINC_NUM,LONG_COMMON_NAME'
        ];
        
        $results = $this->search($params);
        return $results['data'];
    }
    
    /**
     * Get panel contents
     * 
     * @param string $loincNum Parent LOINC code
     * @return array Array of related LOINC records
     */
    public function getPanelContents($loincNum) {
        // Search for questions that might be part of this form
        // This is a workaround since the API doesn't have a direct way to get panel contents
        return [];
    }
    
    /**
     * Get top classes by record count
     * 
     * @param int $limit Number of top classes to return
     * @return array Top classes
     */
    public function getTopClasses($limit = 10) {
        // Return empty - classes should be obtained from search results
        return [];
    }
    
    /**
     * Get available Indonesian terms for a category
     * 
     * @param string|null $category Category filter
     * @return array List of Indonesian terms
     */
    public function getIdTerms($category = null) {
        // Return empty - this was for database-based mapping
        return [];
    }
}