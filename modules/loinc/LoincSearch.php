<?php
/**
 * LOINC Search Class
 * 
 * Provides search functionality for LOINC data with Indonesian language support.
 * Uses Google Translate for automatic translation from Indonesian to English.
 */

require_once __DIR__ . '/../Translator.php';

class LoincSearch {
    protected $pdo;
    protected $translator;
    
    /**
     * Constructor
     * 
     * @param PDO $pdo PDO database connection
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
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
        
        // Search using translated term
        $sql = "SELECT LOINC_NUM, LONG_COMMON_NAME, COMPONENT, SYSTEM, CLASS, STATUS
                FROM loinc
                WHERE LOWER(LOINC_NUM) LIKE LOWER(CONCAT('%', :keyword, '%'))
                   OR LOWER(COMPONENT) LIKE LOWER(CONCAT('%', :keyword, '%'))
                   OR LOWER(PROPERTY) LIKE LOWER(CONCAT('%', :keyword, '%'))
                   OR LOWER(SYSTEM) LIKE LOWER(CONCAT('%', :keyword, '%'))
                   OR LOWER(METHOD_TYP) LIKE LOWER(CONCAT('%', :keyword, '%'))
                   OR LOWER(LONG_COMMON_NAME) LIKE LOWER(CONCAT('%', :keyword, '%'))";
        
        if ($category !== null) {
            $sql .= " AND LOWER(CLASS) = LOWER(:category)";
        }
        
        $sql .= " ORDER BY LOINC_NUM LIMIT 1000";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':keyword', $englishTerm, PDO::PARAM_STR);
        
        if ($category !== null) {
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Normalize results to lowercase keys
        return $this->normalizeResults($results);
    }
    
    /**
     * Search LOINC by keyword (supports multi-word search)
     * 
     * @param string $keyword Search keyword (can be multi-word, supports Indonesian)
     * @param string|null $status Status filter
     * @return array Search results
     */
    public function searchByKeyword($keyword, $status = null) {
        // Limit results for performance
        $limit = 1000;
        
        // Translate keyword if it might be Indonesian
        $translatedKeyword = $this->translator->translate($keyword);
        
        // Use the translated keyword for search
        $searchKeyword = $translatedKeyword;
        
        // Split keyword into words for multi-word search
        $words = preg_split('/\s+/', trim($searchKeyword), -1, PREG_SPLIT_NO_EMPTY);
        
        // Build query with AND conditions for each word
        $sql = "SELECT LOINC_NUM, LONG_COMMON_NAME, COMPONENT, SYSTEM, CLASS, STATUS
                FROM loinc
                WHERE (";
        
        $conditions = [];
        $params = [];
        
        // Add conditions for each word
        foreach ($words as $i => $word) {
            $conditions[] = "(LOWER(LOINC_NUM) LIKE LOWER(CONCAT('%', :word{$i}a, '%'))
                             OR LOWER(COMPONENT) LIKE LOWER(CONCAT('%', :word{$i}b, '%'))
                             OR LOWER(PROPERTY) LIKE LOWER(CONCAT('%', :word{$i}c, '%'))
                             OR LOWER(SYSTEM) LIKE LOWER(CONCAT('%', :word{$i}d, '%'))
                             OR LOWER(METHOD_TYP) LIKE LOWER(CONCAT('%', :word{$i}e, '%'))
                             OR LOWER(LONG_COMMON_NAME) LIKE LOWER(CONCAT('%', :word{$i}f, '%')))";
            $params[":word{$i}a"] = $word;
            $params[":word{$i}b"] = $word;
            $params[":word{$i}c"] = $word;
            $params[":word{$i}d"] = $word;
            $params[":word{$i}e"] = $word;
            $params[":word{$i}f"] = $word;
        }
        
        $sql .= implode(' AND ', $conditions);
        $sql .= ")";
        
        if ($status !== null) {
            $sql .= " AND STATUS = :status";
        }
        
        $sql .= " ORDER BY LOINC_NUM LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        
        // Bind word parameters
        foreach ($params as $key => $value) {
            $stmt->bindParam($key, $value, PDO::PARAM_STR);
        }
        
        if ($status !== null) {
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Normalize results to lowercase keys
        return $this->normalizeResults($results);
    }
    
    /**
     * Normalize result keys to lowercase for consistent access
     * 
     * @param array $results Raw results from database
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
        $sql = "SELECT LOINC_NUM, LONG_COMMON_NAME, COMPONENT, SYSTEM, CLASS, STATUS
                FROM loinc
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($criteria['component'])) {
            $sql .= " AND LOWER(COMPONENT) LIKE LOWER(CONCAT('%', :component, '%'))";
            $params[':component'] = $criteria['component'];
        }
        
        if (!empty($criteria['system'])) {
            $sql .= " AND LOWER(SYSTEM) LIKE LOWER(CONCAT('%', :system, '%'))";
            $params[':system'] = $criteria['system'];
        }
        
        if (!empty($criteria['class'])) {
            $sql .= " AND LOWER(CLASS) = LOWER(:class)";
            $params[':class'] = $criteria['class'];
        }
        
        if (!empty($criteria['status'])) {
            $sql .= " AND STATUS = :status";
            $params[':status'] = $criteria['status'];
        }
        
        $sql .= " ORDER BY LOINC_NUM";
        
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindParam($key, $value, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get statistics
     * 
     * @return array Statistics data
     */
    public function getStatistics() {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM loinc) as total_records,
                    (SELECT COUNT(*) FROM loinc WHERE STATUS = 'ACTIVE') as active_records,
                    (SELECT COUNT(*) FROM loinc WHERE PanelType IS NOT NULL AND PanelType != '') as panel_count,
                    (SELECT COUNT(DISTINCT CLASS) FROM loinc WHERE CLASS IS NOT NULL) as class_count";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get top classes by record count
     * 
     * @param int $limit Number of top classes to return
     * @return array Top classes
     */
    public function getTopClasses($limit = 10) {
        $sql = "SELECT CLASS as class, COUNT(*) as jumlah 
                FROM loinc 
                WHERE CLASS IS NOT NULL 
                GROUP BY CLASS 
                ORDER BY jumlah DESC 
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get available Indonesian terms for a category
     * 
     * @param string|null $category Category filter
     * @return array List of Indonesian terms
     */
    public function getIdTerms($category = null) {
        $sql = "SELECT DISTINCT id_term, en_term, category, description
                FROM id_mapping";
        
        if ($category !== null) {
            $sql .= " WHERE category = :category";
        }
        
        $sql .= " ORDER BY category, id_term";
        
        $stmt = $this->pdo->prepare($sql);
        
        if ($category !== null) {
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get available classes from LOINC data
     * 
     * @return array List of classes
     */
    public function getAvailableClasses() {
        $sql = "SELECT DISTINCT CLASS as class, COUNT(*) as count
                FROM loinc
                WHERE CLASS IS NOT NULL
                GROUP BY CLASS
                ORDER BY count DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get available systems from LOINC data
     * 
     * @return array List of systems
     */
    public function getAvailableSystems() {
        $sql = "SELECT DISTINCT SYSTEM as system, COUNT(*) as count
                FROM loinc
                WHERE SYSTEM IS NOT NULL
                GROUP BY SYSTEM
                ORDER BY count DESC
                LIMIT 100";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get available methods from LOINC data
     * 
     * @return array List of methods
     */
    public function getAvailableMethods() {
        $sql = "SELECT DISTINCT METHOD_TYP as method, COUNT(*) as count
                FROM loinc
                WHERE METHOD_TYP IS NOT NULL
                GROUP BY METHOD_TYP
                ORDER BY count DESC
                LIMIT 100";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}