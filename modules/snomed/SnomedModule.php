<?php
/**
 * SNOMED-CT Module Class
 * 
 * Main module class for SNOMED-CT catalog system with Indonesian language support.
 */

require_once __DIR__ . '/SnomedSearch.php';

class SnomedModule extends SnomedSearch {
    private $config;
    private $tableName;
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array
     */
    public function __construct($config) {
        $this->config = $config;
        
        // Initialize database connection
        $host = $config['db']['host'] ?? 'localhost';
        $port = $config['db']['port'] ?? 3306;
        $dbname = $config['db']['dbname'];
        $username = $config['db']['username'];
        $password = $config['db']['password'];
        
        // Use TCP/IP connection
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        $pdo = new PDO($dsn, $username, $password, $options);
        parent::__construct($pdo);
        
        $this->tableName = $config['tables']['snomed'];
    }
    
    /**
     * Get SNOMED record by code
     * 
     * @param string $code SNOMED code
     * @return array|null SNOMED record or null if not found
     */
    public function getByCode($code) {
        $sql = "SELECT * FROM {$this->tableName} WHERE code = :code";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':code' => $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get statistics
     * 
     * @return array Statistics data
     */
    public function getStatistics() {
        return parent::getStatistics();
    }
    
    /**
     * Get available value sets
     * 
     * @return array List of value sets
     */
    public function getValueSets() {
        return parent::getValueSets();
    }
}