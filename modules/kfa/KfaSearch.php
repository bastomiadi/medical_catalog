<?php
/**
 * KFA Search Class
 * 
 * Provides search functionality for KFA (Farmaceutical Product) data with Indonesian language support.
 */

require_once __DIR__ . '/../Translator.php';

class KfaSearch {
    protected $pdo;
    protected $translator;
    protected $tableName;
    
    public function __construct(PDO $pdo, $tableName = 'products') {
        $this->pdo = $pdo;
        $this->translator = new Translator();
        $this->tableName = $tableName;
    }
    
    /**
     * Search KFA products by keyword (supports multi-word search)
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
        $sql = "SELECT kfa_code, name, active, state, manufacturer, nama_dagang,
                       fix_price, het_price, dose_per_unit
                FROM {$this->tableName}
                WHERE (";
        
        $conditions = [];
        $params = [];
        
        // Add conditions for each word
        foreach ($words as $i => $word) {
            $conditions[] = "(LOWER(kfa_code) LIKE LOWER(CONCAT('%', :word{$i}a, '%'))
                             OR LOWER(name) LIKE LOWER(CONCAT('%', :word{$i}b, '%'))
                             OR LOWER(manufacturer) LIKE LOWER(CONCAT('%', :word{$i}c, '%'))
                             OR LOWER(nama_dagang) LIKE LOWER(CONCAT('%', :word{$i}d, '%')))";
            $params[":word{$i}a"] = $word;
            $params[":word{$i}b"] = $word;
            $params[":word{$i}c"] = $word;
            $params[":word{$i}d"] = $word;
        }
        
        $sql .= implode(' AND ', $conditions);
        $sql .= ")";
        
        // Add status filter if provided
        if ($status !== null) {
            $sql .= " AND active = :status";
            $params[':status'] = ($status === 'ACTIVE') ? 1 : 0;
        }
        
        $sql .= " ORDER BY kfa_code LIMIT :limit";
        
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
                    (SELECT COUNT(*) FROM {$this->tableName}) as total_records,
                    (SELECT COUNT(DISTINCT manufacturer) FROM {$this->tableName} WHERE manufacturer IS NOT NULL) as manufacturer_count,
                    (SELECT COUNT(DISTINCT nama_dagang) FROM {$this->tableName} WHERE nama_dagang IS NOT NULL) as brand_name_count";
        
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Ensure all values are set
        return [
            'total_records' => $result['total_records'] ?? 0,
            'manufacturer_count' => $result['manufacturer_count'] ?? 0,
            'brand_name_count' => $result['brand_name_count'] ?? 0
        ];
    }
    
    /**
     * Get available manufacturers
     * 
     * @return array List of manufacturers
     */
    public function getManufacturers() {
        $sql = "SELECT DISTINCT manufacturer, COUNT(*) as jumlah
                FROM {$this->tableName}
                WHERE manufacturer IS NOT NULL
                GROUP BY manufacturer
                ORDER BY jumlah DESC
                LIMIT 50";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get product by KFA code
     * 
     * @param string $code KFA code
     * @return array|null Product record or null if not found
     */
    public function getByCode($code) {
        $sql = "SELECT * FROM {$this->tableName} WHERE kfa_code = :code";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':code' => $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}