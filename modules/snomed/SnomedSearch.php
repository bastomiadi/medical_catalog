<?php
/**
 * SNOMED-CT Search Class
 * 
 * Provides search functionality for SNOMED-CT data with Indonesian language support.
 */

require_once __DIR__ . '/../Translator.php';

class SnomedSearch {
    protected $pdo;
    protected $translator;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->translator = new Translator();
    }
    
    /**
     * Search SNOMED-CT by keyword (supports multi-word search)
     * 
     * @param string $keyword Search keyword
     * @param string|null $status Status filter
     * @return array Search results
     */
    public function searchByKeyword($keyword, $status = null) {
        $limit = 1000;
        
        // Translate keyword if it might be Indonesian
        $translatedKeyword = $this->translator->translate($keyword);
        
        // Split keyword into words for multi-word search
        $words = preg_split('/\s+/', trim($translatedKeyword), -1, PREG_SPLIT_NO_EMPTY);
        
        // Build query with AND conditions for each word
        $sql = "SELECT code, description, value_set_name, clinical_focus
                FROM snomed_ct
                WHERE (";
        
        $conditions = [];
        $params = [];
        
        // Add conditions for each word
        foreach ($words as $i => $word) {
            $conditions[] = "(LOWER(code) LIKE LOWER(CONCAT('%', :word{$i}a, '%'))
                             OR LOWER(description) LIKE LOWER(CONCAT('%', :word{$i}b, '%'))
                             OR LOWER(value_set_name) LIKE LOWER(CONCAT('%', :word{$i}c, '%'))
                             OR LOWER(clinical_focus) LIKE LOWER(CONCAT('%', :word{$i}d, '%')))";
            $params[":word{$i}a"] = $word;
            $params[":word{$i}b"] = $word;
            $params[":word{$i}c"] = $word;
            $params[":word{$i}d"] = $word;
        }
        
        $sql .= implode(' AND ', $conditions);
        $sql .= ")";
        $sql .= " ORDER BY code LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        
        // Bind word parameters
        foreach ($params as $key => $value) {
            $stmt->bindParam($key, $value, PDO::PARAM_STR);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Normalize results to lowercase keys
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
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM snomed_ct) as total_records,
                    (SELECT COUNT(DISTINCT value_set_name) FROM snomed_ct WHERE value_set_name IS NOT NULL) as value_set_count,
                    (SELECT COUNT(DISTINCT clinical_focus) FROM snomed_ct WHERE clinical_focus IS NOT NULL) as clinical_focus_count";
        
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Ensure all values are set
        return [
            'total_records' => $result['total_records'] ?? 0,
            'value_set_count' => $result['value_set_count'] ?? 0,
            'clinical_focus_count' => $result['clinical_focus_count'] ?? 0
        ];
    }
    
    /**
     * Get available value sets
     * 
     * @return array List of value sets
     */
    public function getValueSets() {
        $sql = "SELECT DISTINCT value_set_name, COUNT(*) as jumlah
                FROM snomed_ct
                WHERE value_set_name IS NOT NULL
                GROUP BY value_set_name
                ORDER BY jumlah DESC
                LIMIT 50";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}