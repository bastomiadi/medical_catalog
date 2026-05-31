<?php
/**
 * HCPCS API Class
 * 
 * Provides REST API access to HCPCS data from clinicaltables.nlm.nih.gov
 */

require_once __DIR__ . '/../Translator.php';

class HcpcsApi {
    private $config;
    private $translator;
    
    // API Base URL
    const SEARCH_API_BASE = 'https://clinicaltables.nlm.nih.gov/api/hcpcs/v3/search';
    
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
     * Search HCPCS codes by keyword
     * 
     * @param array $params Search parameters
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
     * Search HCPCS by keyword
     * 
     * @param string $keyword Search keyword (supports Indonesian)
     * @param string|null $status Status filter
     * @return array Search results
     */
    public function searchByKeyword($keyword, $status = null) {
        // Translate keyword if it might be Indonesian
        $translatedKeyword = $this->translator->translate($keyword);
        
        $params = [
            'terms' => $translatedKeyword,
            'sf' => 'code,code_dotted,short_name,long_name',
            'df' => 'code_dotted,long_name',
            'count' => 500
        ];
        
        $results = $this->search($params);
        return $this->normalizeResults($results['data']);
    }
    
    /**
     * Get HCPCS record by code
     * 
     * @param string $code HCPCS code
     * @return array|null HCPCS record or null if not found
     */
    public function getByCode($code) {
        $params = [
            'terms' => $code,
            'sf' => 'code',
            'count' => 1,
            'ef' => 'code,short_desc,long_desc'
        ];
        
        $results = $this->search($params);
        
        if (!empty($results['data']) && isset($results['data'][0])) {
            return $results['data'][0];
        }
        
        return null;
    }
    
    /**
     * Get statistics
     * 
     * @return array Statistics data
     */
    public function getStatistics() {
        return [
            'total_records' => 'N/A (API-based)',
            'active_records' => 'N/A (API-based)'
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
            'sf' => 'sf',
            'maxList' => 'maxList',
            'count' => 'count',
            'offset' => 'offset',
            'q' => 'q',
            'df' => 'df',
            'cf' => 'cf',
            'ef' => 'ef'
        ];
        
        foreach ($paramMap as $key => $apiParam) {
            if (isset($params[$key]) && $params[$key] !== '' && $params[$key] !== null) {
                // Don't URL encode numeric values
                if (is_numeric($params[$key])) {
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
        curl_setopt($ch, CURLOPT_USERAGENT, 'HCPCS-PHP-Client/1.0');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("HCPCS API Error: " . $error);
        }
        
        if ($httpCode !== 200) {
            error_log("HCPCS API HTTP Error: " . $httpCode . " for URL: " . $url);
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
        
        // API response format: [total, codes, extra, display]
        $total = $data[0] ?? 0;
        $codes = $data[1] ?? [];
        $extra = $data[2] ?? [];
        $display = $data[3] ?? [];
        
        // Build data array
        $results = [];
        
        for ($i = 0; $i < count($codes); $i++) {
            // Display format: [["code", "long_desc"], ...]
            $name = '';
            if (isset($display[$i])) {
                if (is_array($display[$i])) {
                    $name = $display[$i][1] ?? $display[$i][0] ?? '';
                } else {
                    $name = $display[$i];
                }
            }
            
            $row = [
                'code' => $codes[$i],
                'short_desc' => $extra['short_desc'][$i] ?? '',
                'long_desc' => $name,
                'name' => $name
            ];
            
            // Add extra fields (only if extra is not null)
            if ($extra && is_array($extra)) {
                foreach ($extra as $field => $values) {
                    if (isset($values[$i]) && $field !== 'short_desc') {
                        $row[$field] = $values[$i];
                    }
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
}