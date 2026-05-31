<?php
/**
 * Major Surgeries and Implants API Class
 * 
 * Provides REST API access to Major Surgeries and Implants data from clinicaltables.nlm.nih.gov
 */

class MajorSurgeriesApi {
    protected $baseUrl;
    protected $apiKey;
    protected $cacheEnabled;
    protected $cacheTtl;
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array
     */
    public function __construct($config) {
        $this->baseUrl = $config['api_url'] ?? 'https://clinicaltables.nlm.nih.gov/api/procedures/v3/';
        $this->apiKey = $config['api_key'] ?? '';
        $this->cacheEnabled = $config['cache_enabled'] ?? true;
        $this->cacheTtl = $config['cache_ttl'] ?? 3600;
    }
    
    /**
     * Search procedures
     * 
     * @param array $params Search parameters
     * @return array Search results
     */
    public function search($params = []) {
        $url = $this->baseUrl . 'search?' . http_build_query($params);
        
        $response = $this->makeRequest($url);
        
        return $response;
    }
    
    /**
     * Get procedure by code
     * 
     * @param string $code Procedure code
     * @return array|null Procedure data or null if not found
     */
    public function getByCode($code) {
        $params = [
            'terms' => $code,
            'sf' => 'procedure_code',
            'count' => 1
        ];
        
        $response = $this->search($params);
        
        if (isset($response['data']) && !empty($response['data'])) {
            return $response['data'][0];
        }
        
        return null;
    }
    
    /**
     * Get statistics
     * 
     * @return array Statistics data
     */
    public function getStatistics() {
        $url = $this->baseUrl . 'statistics';
        
        $response = $this->makeRequest($url);
        
        return $response;
    }
    
    /**
     * Make HTTP request to API
     * 
     * @param string $url Request URL
     * @return array Response data
     * @throws Exception If request fails
     */
    protected function makeRequest($url) {
        $cacheKey = md5($url);
        $cacheFile = sys_get_temp_dir() . '/major_surgeries_' . $cacheKey . '.json';
        
        // Check cache
        if ($this->cacheEnabled && file_exists($cacheFile) && (time() - filemtime($cacheFile) < $this->cacheTtl)) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        
        if ($this->apiKey) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('API Request Error: ' . $error);
        }
        
        $data = json_decode($response, true);
        
        // Save to cache (skip if cache directory is not writable)
        if ($this->cacheEnabled && is_writable(sys_get_temp_dir())) {
            @file_put_contents($cacheFile, json_encode($data));
        }
        
        // Handle clinicaltables.nlm.nih.gov API response format: [total, codes, extra, display]
        if (is_array($data) && isset($data[0])) {
            return $this->parseApiResponse($data);
        }
        
        return $data ?: ['data' => [], 'meta' => []];
    }
    
    /**
     * Parse API response from clinicaltables.nlm.nih.gov format
     * 
     * @param array $data Raw API response [total, codes, extra, display]
     * @return array Parsed results
     */
    private function parseApiResponse($data) {
        $total = $data[0] ?? 0;
        $codes = $data[1] ?? [];
        $extra = $data[2] ?? [];
        $display = $data[3] ?? [];
        
        $results = [];
        for ($i = 0; $i < count($codes); $i++) {
            $name = '';
            if (isset($display[$i])) {
                if (is_array($display[$i])) {
                    // Display format: [["name"], ["name"], ...] - single element array
                    $name = $display[$i][0] ?? '';
                } else {
                    $name = $display[$i];
                }
            }
            
            $row = [
                'code' => $codes[$i],
                'consumer_name' => $name,
                'primary_name' => $name,
                'procedure_code' => $codes[$i]
            ];
            
            // Add extra fields
            if ($extra && is_array($extra)) {
                foreach ($extra as $field => $values) {
                    if (isset($values[$i])) {
                        $row[strtolower($field)] = $values[$i];
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
}