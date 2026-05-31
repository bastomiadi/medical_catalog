<?php
/**
 * LOINC Module Class
 * 
 * Main module class for LOINC catalog system with Indonesian language support.
 * Supports both REST API and MySQL database sources.
 */

require_once __DIR__ . '/LoincSearch.php';
require_once __DIR__ . '/LoincDbSearch.php';

class LoincModule extends LoincSearch {
    private $config;
    private $dbSearch;
    private $useDatabase;
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array
     */
    public function __construct($config) {
        $this->config = $config;
        $this->useDatabase = $config['use_database'] ?? false;
        
        if ($this->useDatabase) {
            // Initialize database connection
            $host = $config['db']['host'] ?? 'localhost';
            $port = $config['db']['port'] ?? 3306;
            $dbname = $config['db']['dbname'];
            $username = $config['db']['username'];
            $password = $config['db']['password'];
            
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            $pdo = new PDO($dsn, $username, $password, $options);
            $this->dbSearch = new LoincDbSearch($pdo);
            parent::__construct($config);
        } else {
            parent::__construct($config);
        }
    }
    
    /**
     * Search LOINC by keyword
     * 
     * @param string $keyword Search keyword
     * @param string|null $status Status filter
     * @return array Search results
     */
    public function searchByKeyword($keyword, $status = null) {
        if ($this->useDatabase && $this->dbSearch) {
            return $this->dbSearch->searchByKeyword($keyword, $status);
        }
        return parent::searchByKeyword($keyword, $status);
    }
    
    /**
     * Get LOINC record by code
     * 
     * @param string $loincNum LOINC code
     * @return array|null LOINC record or null if not found
     */
    public function getByCode($loincNum) {
        if ($this->useDatabase && $this->dbSearch) {
            return $this->dbSearch->getByCode($loincNum);
        }
        return $this->api->getByCode($loincNum);
    }
    
    /**
     * Get available classes
     * 
     * @return array Array of unique classes
     */
    public function getAvailableClasses() {
        if ($this->useDatabase && $this->dbSearch) {
            $results = $this->dbSearch->getAvailableClasses();
            return array_column($results, 'CLASS');
        }
        return $this->api->getAvailableClasses();
    }
    
    /**
     * Get statistics
     * 
     * @return array Statistics data
     */
    public function getStatistics() {
        if ($this->useDatabase && $this->dbSearch) {
            return $this->dbSearch->getStatistics();
        }
        return $this->api->getStatistics();
    }
    
    /**
     * Get LOINC records by class
     * 
     * @param string $class Class name
     * @param int $limit Limit number of results
     * @return array Array of LOINC records
     */
    public function getByClass($class, $limit = 100) {
        if ($this->useDatabase && $this->dbSearch) {
            $params = [
                'terms' => '*',
                'type' => 'question',
                'count' => $limit,
                'q' => 'CLASS:' . $class,
                'ef' => 'text,LOINC_NUM,PROPERTY,METHOD_TYP,SYSTEM,STATUS,LONG_COMMON_NAME,COMPONENT'
            ];
            
            $results = $this->api->search($params);
            return $this->normalizeResults($results['data']);
        }
        return parent::getByClass($class, $limit);
    }
    
    /**
     * Get LOINC records by status
     * 
     * @param string $status Status value
     * @return array Array of LOINC records
     */
    public function getByStatus($status = 'ACTIVE') {
        if ($this->useDatabase && $this->dbSearch) {
            $params = [
                'terms' => '*',
                'type' => 'question',
                'count' => 1000,
                'q' => 'STATUS:' . $status,
                'ef' => 'text,LOINC_NUM,PROPERTY,METHOD_TYP,SYSTEM,STATUS,LONG_COMMON_NAME,COMPONENT'
            ];
            
            $results = $this->api->search($params);
            return $this->normalizeResults($results['data']);
        }
        return parent::getByStatus($status);
    }
    
    /**
     * Get panel information
     * 
     * @return array Array of panel records
     */
    public function getPanels() {
        return $this->api->getPanels();
    }
    
    /**
     * Get related LOINC codes for a panel
     * 
     * @param string $loincNum Parent LOINC code
     * @return array Array of related LOINC records
     */
    public function getPanelContents($loincNum) {
        return $this->api->getPanelContents($loincNum);
    }
    
    /**
     * Get available systems
     * 
     * @return array Array of unique systems
     */
    public function getAvailableSystems() {
        return $this->api->getAvailableSystems();
    }
    
    /**
     * Get available methods
     * 
     * @return array Array of unique methods
     */
    public function getAvailableMethods() {
        return $this->api->getAvailableMethods();
    }
    
    /**
     * Import data from file (not supported for API-based module)
     * 
     * @param string $filePath Path to the data file
     * @param string $format File format
     * @return array Import result
     * @throws Exception Always throws exception as this is not supported
     */
    public function importData($filePath, $format = 'csv') {
        throw new Exception("Import is not supported for API-based LOINC module. The module now uses REST API from clinicaltables.nlm.nih.gov");
    }
    
    /**
     * Get module info
     * 
     * @return array Module information
     */
    public function getModuleInfo() {
        return [
            'name' => 'LOINC',
            'version' => '3.0.0',
            'description' => 'Logical Observation Identifiers Names and Codes (API/Database-based)',
            'language_support' => ['en', 'id'],
            'data_source' => $this->useDatabase ? 'MySQL Database' : 'https://clinicaltables.nlm.nih.gov/api/loinc_items/v3/',
            'tables' => $this->config['tables']
        ];
    }
    
    /**
     * Get answer list for a question
     * 
     * @param string $loincNum LOINC code
     * @return array Answer list
     */
    public function getAnswerList($loincNum) {
        return $this->api->getAnswers($loincNum);
    }
    
    /**
     * Get form definition
     * 
     * @param string $loincNum LOINC code for the form
     * @return array Form definition
     */
    public function getFormDefinition($loincNum) {
        return $this->api->getFormDefinition($loincNum);
    }
    
    /**
     * Search forms
     * 
     * @param string $keyword Search keyword
     * @param int $limit Limit number of results
     * @return array Search results
     */
    public function searchForms($keyword, $limit = 100) {
        return $this->api->searchForms($keyword, $limit);
    }
    
    /**
     * Search forms and sections
     * 
     * @param string $keyword Search keyword
     * @param int $limit Limit number of results
     * @return array Search results
     */
    public function searchFormsAndSections($keyword, $limit = 100) {
        return $this->api->searchFormsAndSections($keyword, $limit);
    }
}