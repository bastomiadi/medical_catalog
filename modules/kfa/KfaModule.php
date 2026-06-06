<?php
/**
 * KFA Module Class
 * 
 * Main module class for KFA (Farmaceutical Product) catalog system with Indonesian language support.
 */

require_once __DIR__ . '/KfaSearch.php';

class KfaModule extends KfaSearch {
    private $config;
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array
     */
    public function __construct($config) {
        $this->config = $config;
        
        // Load default config if not provided
        $defaultConfig = include __DIR__ . '/config.php';
        $config = array_merge($defaultConfig, $config);
        
        // Initialize database connection
        $host = $config['db']['host'] ?? 'localhost';
        $port = $config['db']['port'] ?? 3306;
        $dbname = $config['db']['dbname'] ?? 'master_kfa';
        $username = $config['db']['username'] ?? 'root';
        $password = $config['db']['password'] ?? '';
        
        // Use TCP/IP connection
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        $pdo = new PDO($dsn, $username, $password, $options);
        parent::__construct($pdo);
        
        $this->tableName = $config['tables']['products'] ?? 'products';
    }
    
    /**
     * Get KFA product by code
     * 
     * @param string $code KFA code
     * @return array|null KFA product record or null if not found
     */
    public function getByCode($code) {
        return parent::getByCode($code);
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
     * Get available manufacturers
     * 
     * @return array List of manufacturers
     */
    public function getManufacturers() {
        return parent::getManufacturers();
    }
    
    /**
     * Get module info
     * 
     * @return array Module information
     */
    public function getModuleInfo() {
        return [
            'name' => 'KFA',
            'version' => '1.0.0',
            'description' => 'Farmaceutical Product Catalog (Master KFA)',
            'language_support' => ['en', 'id'],
            'data_source' => 'MySQL Database (master_kfa)'
        ];
    }
}