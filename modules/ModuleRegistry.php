<?php
/**
 * Module Registry
 * 
 * Central registry for all catalog modules (LOINC, KFA, SNOMED CT, etc.)
 */

class ModuleRegistry {
    private static $modules = [];
    private static $config = null;
    
    /**
     * Register a module
     * 
     * @param string $name Module name
     * @param string $className Class name
     * @param array $config Module configuration
     */
    public static function register($name, $className, $config) {
        self::$modules[$name] = [
            'class' => $className,
            'config' => $config
        ];
    }
    
    /**
     * Get a module instance
     * 
     * @param string $name Module name
     * @return object Module instance
     */
    public static function getModule($name) {
        if (!isset(self::$modules[$name])) {
            throw new Exception("Module not found: $name");
        }
        
        $module = self::$modules[$name];
        $class = $module['class'];
        
        return new $class($module['config']);
    }
    
    /**
     * Get list of registered modules
     * 
     * @return array List of module names
     */
    public static function getModules() {
        return array_keys(self::$modules);
    }
    
    /**
     * Check if module exists
     * 
     * @param string $name Module name
     * @return bool
     */
    public static function hasModule($name) {
        return isset(self::$modules[$name]);
    }
}