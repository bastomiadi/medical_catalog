<?php
/**
 * LOINC Database Search Class
 * 
 * Provides search functionality for LOINC data from MySQL database with Indonesian language support.
 */

require_once __DIR__ . '/../Translator.php';

class LoincDbSearch {
    protected $pdo;
    protected $translator;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->translator = new Translator();
    }
    
    /**
     * Search LOINC by keyword (supports multi-word search)
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
        $sql = "SELECT LOINC_NUM, LONG_COMMON_NAME as text, CLASS as class, STATUS as status
                FROM loinc
                WHERE (";
        
        $conditions = [];
        $params = [];
        
        // Add conditions for each word
        foreach ($words as $i => $word) {
            $conditions[] = "(LOWER(LOINC_NUM) LIKE LOWER(CONCAT('%', :word{$i}a, '%'))
                             OR LOWER(LONG_COMMON_NAME) LIKE LOWER(CONCAT('%', :word{$i}b, '%'))
                             OR LOWER(CLASS) LIKE LOWER(CONCAT('%', :word{$i}c, '%')))";
            $params[":word{$i}a"] = $word;
            $params[":word{$i}b"] = $word;
            $params[":word{$i}c"] = $word;
        }
        
        $sql .= implode(' AND ', $conditions);
        $sql .= ")";
        
        // Add status filter if provided
        if ($status) {
            $sql .= " AND STATUS = :status";
            $params[':status'] = $status;
        }
        
        $sql .= " ORDER BY LOINC_NUM LIMIT :limit";
        
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
                    (SELECT COUNT(*) FROM loinc) as total_records,
                    (SELECT COUNT(DISTINCT CLASS) FROM loinc WHERE CLASS IS NOT NULL) as class_count,
                    (SELECT COUNT(DISTINCT PanelType) FROM loinc WHERE PanelType IS NOT NULL) as panel_count,
                    (SELECT COUNT(*) FROM loinc WHERE STATUS = 'ACTIVE') as active_records";
        
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'total_records' => $result['total_records'] ?? 0,
            'active_records' => $result['active_records'] ?? 0,
            'class_count' => $result['class_count'] ?? 0,
            'panel_count' => $result['panel_count'] ?? 0
        ];
    }
    
    /**
     * Get available classes
     * 
     * @return array List of classes
     */
    public function getAvailableClasses() {
        $sql = "SELECT DISTINCT CLASS, COUNT(*) as jumlah
                FROM loinc
                WHERE CLASS IS NOT NULL
                GROUP BY CLASS
                ORDER BY jumlah DESC
                LIMIT 20";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get LOINC record by code
     * 
     * @param string $loincNum LOINC code
     * @return array|null LOINC record or null if not found
     */
    public function getByCode($loincNum) {
        $sql = "SELECT * FROM loinc WHERE LOINC_NUM = :loinc_num";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':loinc_num' => $loincNum]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}