-- SNOMED-CT Database Schema
-- Native MySQL database for SNOMED-CT dataset with Indonesian language support

-- Create database
CREATE DATABASE IF NOT EXISTS snomed_db;
USE snomed_db;

-- Main SNOMED-CT table
CREATE TABLE IF NOT EXISTS snomed_ct (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code_system VARCHAR(50) NOT NULL DEFAULT 'SNOMEDCT',
    value_set_name VARCHAR(255),
    code VARCHAR(50) NOT NULL,
    description TEXT,
    clinical_focus TEXT,
    value_set_oid VARCHAR(255),
    code_system_oid VARCHAR(255),
    INDEX idx_code (code),
    INDEX idx_value_set (value_set_name),
    INDEX idx_description (description(200))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Indonesian language mapping table for filtering
CREATE TABLE IF NOT EXISTS snomed_id_mapping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    field_name VARCHAR(50) NOT NULL COMMENT 'Target SNOMED field name',
    id_term VARCHAR(100) NOT NULL COMMENT 'Indonesian term',
    en_term VARCHAR(100) NOT NULL COMMENT 'English term (for reference)',
    description TEXT COMMENT 'Description of the mapping',
    category VARCHAR(50) COMMENT 'Category',
    INDEX idx_field_name (field_name),
    INDEX idx_id_term (id_term),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Indonesian translation table
CREATE TABLE IF NOT EXISTS snomed_id_translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_term VARCHAR(255) NOT NULL COMMENT 'Original English term',
    translated_term VARCHAR(255) NOT NULL COMMENT 'Indonesian translation',
    context VARCHAR(100) COMMENT 'Context',
    INDEX idx_original (original_term),
    INDEX idx_context (context)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Import log table
CREATE TABLE IF NOT EXISTS snomed_import_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    import_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_records INT,
    status ENUM('success', 'error', 'partial') DEFAULT 'success',
    error_message TEXT,
    file_name VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;