<?php
/**
 * Translator Class
 * 
 * Uses Google Translate API for automatic translation from Indonesian to English.
 * Shared by both LOINC and SNOMED-CT modules.
 */

class Translator {
    private $cache = [];
    
    /**
     * Translate Indonesian text to English using Google Translate API
     * 
     * @param string $text Indonesian text
     * @return string English translation
     */
    public function translate($text) {
        // Check cache first
        $cacheKey = strtolower($text);
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        // Use Google Translate API via curl
        $url = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=id&tl=en&dt=t&q=' . urlencode($text);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response && $httpCode == 200) {
            $result = json_decode($response, true);
            if (isset($result[0][0][0])) {
                $translated = $result[0][0][0];
                $this->cache[$cacheKey] = $translated;
                return $translated;
            }
        }
        
        // Fallback: return original text if translation fails
        $this->cache[$cacheKey] = $text;
        return $text;
    }
    
    /**
     * Translate and search in database
     * 
     * @param string $indonesianTerm Indonesian search term
     * @param PDO $pdo Database connection
     * @param string $table Table name
     * @param string $codeColumn Code column name
     * @param string $descColumn Description column name
     * @return array Search results
     */
    public function searchTranslated($indonesianTerm, $pdo, $table, $codeColumn, $descColumn) {
        $englishTerm = $this->translate($indonesianTerm);
        
        $sql = "SELECT {$codeColumn} as code, {$descColumn} as description
                FROM {$table}
                WHERE LOWER({$codeColumn}) LIKE LOWER(CONCAT('%', :keyword, '%'))
                   OR LOWER({$descColumn}) LIKE LOWER(CONCAT('%', :keyword, '%'))
                ORDER BY {$codeColumn}
                LIMIT 1000";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':keyword', $englishTerm, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}