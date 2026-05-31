<?php
/**
 * RxTerms API Class
 * 
 * Provides REST API access to Prescribable Drug Ingredients from RxTerms data
 * from clinicaltables.nlm.nih.gov
 * Implements the API for Prescribable Drug Ingredients from RxTerms specification.
 */

require_once __DIR__ . '/../Translator.php';

class RxTermsApi {
    private $config;
    private $translator;
    
    // API Base URL
    const SEARCH_API_BASE = 'https://clinicaltables.nlm.nih.gov/api/drug_ingredients/v3/search';
    
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
     * Search drug ingredients
     * 
     * @param array $params Search parameters:
     *   - terms: (required) search string
     *   - maxList: max results (default 7, max 500)
     *   - count: page size (default 7, max 500)
     *   - offset: pagination offset (default 0)
     *   - q: additional query string
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
     * Get drug ingredient by RXCUI
     * 
     * @param string $rxcui RxNorm Unique Identifier
     * @return array|null Drug ingredient record or null if not found
     */
    public function getByCode($rxcui) {
        $params = [
            'terms' => $rxcui,
            'count' => 1,
            'df' => 'code,name'
        ];
        
        $results = $this->search($params);
        
        if (!empty($results['data']) && isset($results['data'][0])) {
            return $results['data'][0];
        }
        
        return null;
    }
    
    /**
     * Search ingredients by keyword
     * 
     * @param string $keyword Search keyword
     * @param int $limit Limit number of results
     * @return array Search results
     */
    public function searchByKeyword($keyword, $limit = 100) {
        $params = [
            'terms' => $keyword,
            'count' => min($limit, 500),
            'df' => 'code,name'
        ];
        
        return $this->search($params);
    }
    
    /**
     * Get statistics (placeholder - API doesn't provide this directly)
     * 
     * @return array Statistics data
     */
    public function getStatistics() {
        return [
            'total_records' => 'N/A (API-based)',
            'data_source' => 'https://clinicaltables.nlm.nih.gov/api/drug_ingredients/v3/'
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
            'maxList' => 'maxList',
            'count' => 'count',
            'offset' => 'offset',
            'q' => 'q',
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
        curl_setopt($ch, CURLOPT_USERAGENT, 'RxTerms-PHP-Client/1.0');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("RxTerms API Error: " . $error);
        }
        
        if ($httpCode !== 200) {
            error_log("RxTerms API HTTP Error: " . $httpCode . " for URL: " . $url);
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
        $codeField = $params['cf'] ?? 'code';
        
        for ($i = 0; $i < count($codes); $i++) {
            // Display format: [["code", "name"], ...]
            $name = '';
            if (isset($display[$i]) && is_array($display[$i])) {
                $name = $display[$i][1] ?? $display[$i][0] ?? '';
            }
            
            $row = [
                $codeField => $codes[$i],
                'code' => $codes[$i],
                'name' => $name,
                'text' => $name
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
     * @return array Search results
     */
    public function searchByIdTerm($idTerm) {
        $englishTerm = $this->translator->translate($idTerm);
        
        $params = [
            'terms' => $englishTerm,
            'count' => 100,
            'df' => 'code,name'
        ];
        
        return $this->search($params);
    }
}