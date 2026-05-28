<?php
/**
 * LOINC Module Class
 * 
 * Main module class for LOINC catalog system with Indonesian language support.
 */

require_once __DIR__ . '/LoincSearch.php';

class LoincModule extends LoincSearch {
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
        
        $this->tableName = $config['tables']['loinc'];
    }
    
    /**
     * Get LOINC record by code
     * 
     * @param string $loincNum LOINC code
     * @return array|null LOINC record or null if not found
     */
    public function getByCode($loincNum) {
        $sql = "SELECT * FROM {$this->tableName} WHERE LOINC_NUM = :loinc_num";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':loinc_num' => $loincNum]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get LOINC records by class
     * 
     * @param string $class Class name
     * @param int $limit Limit number of results
     * @return array Array of LOINC records
     */
    public function getByClass($class, $limit = 100) {
        $sql = "SELECT * FROM {$this->tableName} 
                WHERE LOWER(CLASS) = LOWER(:class) 
                ORDER BY LOINC_NUM 
                LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':class', $class, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get LOINC records by status
     * 
     * @param string $status Status value
     * @return array Array of LOINC records
     */
    public function getByStatus($status = 'ACTIVE') {
        $sql = "SELECT * FROM {$this->tableName} 
                WHERE STATUS = :status 
                ORDER BY LOINC_NUM";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get panel information
     * 
     * @return array Array of panel records
     */
    public function getPanels() {
        $sql = "SELECT DISTINCT LOINC_NUM, LONG_COMMON_NAME, PanelType
                FROM {$this->tableName}
                WHERE PanelType IS NOT NULL AND PanelType != ''
                ORDER BY PanelType, LOINC_NUM";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get related LOINC codes for a panel
     * 
     * @param string $loincNum Parent LOINC code
     * @return array Array of related LOINC records
     */
    public function getPanelContents($loincNum) {
        $sql = "SELECT * FROM {$this->tableName}
                WHERE AssociatedObservations LIKE :loinc_num
                ORDER BY LOINC_NUM";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':loinc_num' => "%{$loincNum}%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get available classes
     * 
     * @return array Array of unique classes
     */
    public function getAvailableClasses() {
        $sql = "SELECT DISTINCT CLASS as class, COUNT(*) as count
                FROM {$this->tableName}
                WHERE CLASS IS NOT NULL
                GROUP BY CLASS
                ORDER BY count DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get available systems
     * 
     * @return array Array of unique systems
     */
    public function getAvailableSystems() {
        $sql = "SELECT DISTINCT SYSTEM
                FROM {$this->tableName}
                WHERE SYSTEM IS NOT NULL
                ORDER BY SYSTEM";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get available methods
     * 
     * @return array Array of unique methods
     */
    public function getAvailableMethods() {
        $sql = "SELECT DISTINCT METHOD_TYP
                FROM {$this->tableName}
                WHERE METHOD_TYP IS NOT NULL
                ORDER BY METHOD_TYP";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Import data from file
     * 
     * @param string $filePath Path to the data file
     * @param string $format File format
     * @return array Import result
     */
    public function importData($filePath, $format = 'csv') {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: $filePath");
        }
        
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Cannot open file: $filePath");
        }
        
        // Read header
        $headerLine = fgets($handle);
        if ($format === 'txt') {
            $headerLine = mb_convert_encoding($headerLine, 'UTF-8', 'UTF-16LE');
        }
        $headers = explode("\t", trim($headerLine));
        
        // Prepare insert
        $placeholders = str_repeat('?,', count($headers) - 1) . '?';
        $columns = implode(',', array_map(function($h) { return "`$h`"; }, $headers));
        $sql = "INSERT INTO {$this->tableName} ($columns) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        
        $count = 0;
        $batchSize = 1000;
        $batch = [];
        
        // Read data
        while (($line = fgets($handle)) !== false) {
            if ($format === 'txt') {
                $line = mb_convert_encoding($line, 'UTF-8', 'UTF-16LE');
            }
            $data = explode("\t", trim($line));
            while (count($data) < count($headers)) {
                $data[] = '';
            }
            $batch[] = $data;
            
            if (count($batch) >= $batchSize) {
                foreach ($batch as $row) {
                    $stmt->execute($row);
                    $count++;
                }
                $batch = [];
            }
        }
        
        // Insert remaining
        foreach ($batch as $row) {
            $stmt->execute($row);
            $count++;
        }
        
        fclose($handle);
        
        // Log import
        $logStmt = $this->pdo->prepare("INSERT INTO import_log (total_records, status, file_name) VALUES (?, 'success', ?)");
        $logStmt->execute([$count, basename($filePath)]);
        
        return [
            'status' => 'success',
            'records_imported' => $count,
            'file' => $filePath
        ];
    }
    
    /**
     * Get module info
     * 
     * @return array Module information
     */
    public function getModuleInfo() {
        return [
            'name' => 'LOINC',
            'version' => '1.0.0',
            'description' => 'Logical Observation Identifiers Names and Codes',
            'language_support' => ['en', 'id'],
            'tables' => $this->config['tables']
        ];
    }
}