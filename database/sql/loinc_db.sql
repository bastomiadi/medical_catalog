-- LOINC Database Schema
-- Native MySQL database for LOINC dataset with Indonesian language support

-- Create database
CREATE DATABASE IF NOT EXISTS loinc_db;
USE loinc_db;

-- Main LOINC table
CREATE TABLE IF NOT EXISTS loinc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    LOINC_NUM VARCHAR(50) NOT NULL,
    COMPONENT TEXT,
    PROPERTY TEXT,
    TIME_ASPCT VARCHAR(50),
    SYSTEM TEXT,
    SCALE_TYP VARCHAR(50),
    METHOD_TYP TEXT,
    CLASS VARCHAR(100),
    VersionLastChanged VARCHAR(50),
    CHNG_TYPE VARCHAR(20),
    DefinitionDescription TEXT,
    STATUS VARCHAR(20),
    CONSUMER_NAME TEXT,
    CLASSTYPE INT,
    FORMULA TEXT,
    EXMPL_ANSWERS TEXT,
    SURVEY_QUEST_TEXT TEXT,
    SURVEY_QUEST_SRC TEXT,
    UNITSREQUIRED VARCHAR(50),
    RELATEDNAMES2 TEXT,
    SHORTNAME VARCHAR(200),
    ORDER_OBS VARCHAR(50),
    HL7_FIELD_SUBFIELD_ID VARCHAR(200),
    EXTERNAL_COPYRIGHT_NOTICE TEXT,
    EXAMPLE_UNITS TEXT,
    LONG_COMMON_NAME TEXT,
    EXAMPLE_UCUM_UNITS TEXT,
    STATUS_REASON TEXT,
    STATUS_TEXT TEXT,
    CHANGE_REASON_PUBLIC TEXT,
    COMMON_TEST_RANK INT,
    COMMON_ORDER_RANK INT,
    HL7_ATTACHMENT_STRUCTURE TEXT,
    EXTERNAL_COPYRIGHT_LINK TEXT,
    PanelType VARCHAR(100),
    AskAtOrderEntry VARCHAR(200),
    AssociatedObservations TEXT,
    VersionFirstReleased VARCHAR(50),
    ValidHL7AttachmentRequest VARCHAR(200),
    DisplayName TEXT,
    INDEX idx_loinc_num (LOINC_NUM),
    INDEX idx_component (COMPONENT(100)),
    INDEX idx_system (SYSTEM(100)),
    INDEX idx_method (METHOD_TYP(100)),
    INDEX idx_class (CLASS),
    INDEX idx_status (STATUS),
    INDEX idx_panel_type (PanelType)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Indonesian language mapping table for filtering
CREATE TABLE IF NOT EXISTS id_mapping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    field_name VARCHAR(50) NOT NULL COMMENT 'Target LOINC field name',
    id_term VARCHAR(100) NOT NULL COMMENT 'Indonesian term',
    en_term VARCHAR(100) NOT NULL COMMENT 'English term (for reference)',
    description TEXT COMMENT 'Description of the mapping',
    category VARCHAR(50) COMMENT 'Category: component, property, system, method, class',
    INDEX idx_field_name (field_name),
    INDEX idx_id_term (id_term),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Indonesian translation table for common terms
CREATE TABLE IF NOT EXISTS id_translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_term VARCHAR(255) NOT NULL COMMENT 'Original English term',
    translated_term VARCHAR(255) NOT NULL COMMENT 'Indonesian translation',
    context VARCHAR(100) COMMENT 'Context: component, property, system, method, class, other',
    INDEX idx_original (original_term),
    INDEX idx_context (context)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- View for easy Indonesian filtering
CREATE VIEW IF NOT EXISTS loinc_id_view AS
SELECT 
    l.LOINC_NUM,
    l.COMPONENT,
    l.PROPERTY,
    l.SYSTEM,
    l.METHOD_TYP,
    l.CLASS,
    l.LONG_COMMON_NAME,
    l.SHORTNAME,
    l.STATUS,
    l.PanelType,
    COALESCE(it.translated_term, l.COMPONENT) as component_id,
    COALESCE(it2.translated_term, l.PROPERTY) as property_id,
    COALESCE(it3.translated_term, l.SYSTEM) as system_id,
    COALESCE(it4.translated_term, l.METHOD_TYP) as method_id
FROM loinc l
    LEFT JOIN id_translations it ON l.COMPONENT = it.original_term AND it.context = 'component'
    LEFT JOIN id_translations it2 ON l.PROPERTY = it2.original_term AND it2.context = 'property'
    LEFT JOIN id_translations it3 ON l.SYSTEM = it3.original_term AND it3.context = 'system'
    LEFT JOIN id_translations it4 ON l.METHOD_TYP = it4.original_term AND it4.context = 'method';

-- Import log table
CREATE TABLE IF NOT EXISTS import_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    import_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_records INT,
    status ENUM('success', 'error', 'partial') DEFAULT 'success',
    error_message TEXT,
    file_name VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;