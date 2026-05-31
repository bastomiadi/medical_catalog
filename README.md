# Catalog LOINC & SNOMED-CT

A web-based medical catalog system with Indonesian language support for searching and filtering LOINC and SNOMED-CT medical codes.

## Features

- **LOINC Catalog**: Search and browse Logical Observation Identifiers Names and Codes (REST API or MySQL database)
- **SNOMED-CT Catalog**: Search and browse Systematized Nomenclature of Medicine Clinical Terms (MySQL database)
- **ICD-10 Catalog**: Search and browse International Classification of Diseases, 10th Revision (REST API)
- **ICD-11 Codes Catalog**: Search and browse International Classification of Diseases, 11th Revision (REST API)
- **ICD-9 Procedure Catalog**: Search and browse International Classification of Diseases, 9th Revision, Clinical Modification - Procedures (REST API)
- **ICD-9 Diagnoses Catalog**: Search and browse International Classification of Diseases, 9th Revision, Clinical Modification - Diagnoses (REST API)
- **HCPCS Catalog**: Search and browse Healthcare Common Procedure Coding System (REST API)
- **HPO Catalog**: Search and browse Human Phenotype Ontology (REST API)
- **Major Surgeries and Implants Catalog**: Search and browse major surgeries and implants procedures (REST API)
- **Medical Conditions Catalog**: Search and browse medical conditions from Regenstrief Institute Medical Gopher program (REST API)
- **Indonesian Language Support**: Filter and search using Indonesian terminology with Google Translate API
- **Responsive Design**: Modern UI with Tailwind CSS
- **Enhanced Search Results**: SNOMED-CT results include Clinical Focus column
- **Click-to-Copy**: Click any row to copy LOINC/SNOMED code to clipboard
- **Table-style Autocomplete**: Dropdown shows results in table format with Kode, Deskripsi, and Salin columns

## Demo Screenshots

<div align="center">

| Home Page LOINC | Home Page SNOMED |
|-----------|-------------|
| ![Home Page LOINC](screenshoot/screenshoot_1.png) | ![Home Page SNOMED](screenshoot/screenshoot_2.png) |

| Search Page SNOMED | Search Page LOINC |
|-----------------|----------------|
| ![Search Page SNOMED](screenshoot/screenshoot_4.png) | ![Search Page LOINC](screenshoot/screenshoot_3.png) |

</div>

## Data Sources

| Module | Source | Description |
|--------|--------|-------------|
| LOINC | REST API or MySQL Database | Configurable via `use_database` setting in config |
| SNOMED-CT | [MySQL Database](database/sql/snomed_db.sql) | SNOMED-CT database schema with local MySQL storage |

## Requirements

- PHP 7.3+ (XAMPP recommended)
- MySQL 5.7+ (for SNOMED-CT only)
- Apache HTTP Server
- Internet connection (for LOINC API access)

## Installation

### 1. Clone/Download the Project

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/
# Extract or clone the project to medical_catalog directory
```

### 2. Start XAMPP Services

Start Apache and MySQL services from XAMPP Control Panel.

### 3. Create Database (SNOMED-CT only)

```sql
CREATE DATABASE IF NOT EXISTS snomed_db;
```

### 4. Import SNOMED-CT Database Schema

The database SQL file is located in the `database/sql/` directory:
- `database/sql/snomed_db.sql` - SNOMED-CT database schema

```bash
# Import SNOMED-CT schema
mysql -u root -p snomed_db < database/sql/snomed_db.sql
```

**Note**: LOINC module uses REST API from clinicaltables.nlm.nih.gov and does not require a local database.

### 5. Configure Database Connection (SNOMED-CT only)

Edit the configuration file to match your environment:

**`modules/snomed/config.php`**:
```php
'db' => [
    'host' => '127.0.0.1',
    'port' => 3306,
    'dbname' => 'snomed_db',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8'
],
```

### 6. Access the Application

Open your browser and navigate to:
```
http://localhost/catalog_medical/public/
```

## Project Structure

```
catalog_medical/
├── config/
│   └── modules.php          # Main module configuration
├── database/
│   └── sql/
│       └── snomed_db.sql    # SNOMED-CT database schema
├── modules/
│   ├── ModuleRegistry.php   # Module registry class
│   ├── loinc/
│   │   ├── config.php       # LOINC configuration (API-based)
│   │   ├── LoincModule.php  # LOINC module class
│   │   ├── LoincSearch.php  # LOINC search functionality
│   │   ├── LoincApi.php     # LOINC API client
│   │   └── Translator.php   # Indonesian-English translator
│   ├── snomed/
│   │   ├── config.php       # SNOMED-CT configuration
│   │   ├── SnomedModule.php # SNOMED-CT module class
│   │   └── SnomedSearch.php # SNOMED-CT search functionality
│   ├── icd10/
│   │   ├── config.php       # ICD-10 configuration
│   │   ├── IcdModule.php    # ICD-10 module class
│   │   └── IcdSearch.php    # ICD-10 search functionality
│   ├── icd9_procedure/
│   │   ├── config.php       # ICD-9 Procedure configuration
│   │   ├── Icd9ProcedureModule.php  # ICD-9 Procedure module class
│   │   ├── Icd9ProcedureSearch.php  # ICD-9 Procedure search functionality
│   │   └── Icd9ProcedureApi.php     # ICD-9 Procedure API client
│   ├── hcpcs/
│   │   ├── config.php       # HCPCS configuration
│   │   ├── HcpcsModule.php  # HCPCS module class
│   │   ├── HcpcsSearch.php  # HCPCS search functionality
│   │   └── HcpcsApi.php     # HCPCS API client
│   └── hpo/
│       ├── config.php       # HPO configuration
│       ├── HpoModule.php    # HPO module class
│       ├── HpoSearch.php    # HPO search functionality
│       └── HpoApi.php       # HPO API client
│   └── major_surgeries_and_implants/
│       ├── config.php       # Major Surgeries configuration
│       ├── MajorSurgeriesModule.php  # Major Surgeries module class
│       ├── MajorSurgeriesSearch.php  # Major Surgeries search functionality
│       └── MajorSurgeriesApi.php     # Major Surgeries API client
├── public/
│   ├── index.php            # Landing page (hero section, features)
│   ├── catalog.php          # Main catalog interface (search, results)
│   └── assets/
│       └── css/
│           └── style.css    # Custom styles
└── README.md
```

## Usage

### Home Page
Displays quick search form with autocomplete suggestions.

### Search Page
- Search by keyword (e.g., "darah", "glukosa", "urine")
- Results are automatically translated from Indonesian to English
- Sortable and paginated tables
- Real-time autocomplete suggestions

### Statistics Page
View database statistics for each module.

## LOINC API Features

The LOINC module uses the REST API from `clinicaltables.nlm.nih.gov` with the following capabilities:

### Search Types
- **Questions**: Search individual LOINC questions
- **Forms**: Search LOINC forms and panels
- **Forms and Sections**: Search forms with their sections

### API Endpoints
| Endpoint | Description |
|----------|-------------|
| `/api/loinc_items/v3/search` | Search LOINC questions |
| `/loinc_answers` | Get answer lists for questions |
| `/loinc_form_definitions` | Get form definitions |

### Query Parameters
- `terms`: Search string
- `type`: Result type (question, form, panel, form_and_section)
- `count`: Number of results (max 500)
- `offset`: Pagination offset
- `q`: Additional query filters (e.g., `STATUS:ACTIVE`)
- `available`: Filter available forms
- `ef`: Extra fields to return
- `df`: Display fields
- `sf`: Search fields
- `cf`: Code field
- `excludeCopyrighted`: Exclude copyrighted content

## Configuration Options

### Module Settings
- `default_module`: Default catalog module (loinc/snomed)
- `app.name`: Application name
- `app.version`: Application version
- `app.debug`: Debug mode

### Search Settings
- `default_limit`: Default number of search results
- `max_limit`: Maximum results limit
- `enable_translation`: Enable Indonesian-English translation

## Troubleshooting

### API Connection Error
If you see "API connection failed" error:
1. Ensure internet connection is available
2. Check if `clinicaltables.nlm.nih.gov` is accessible
3. Verify firewall/proxy settings

### Database Connection Error (SNOMED-CT)
If you see "No such file or directory" error:
1. Ensure MySQL is running
2. Use `127.0.0.1` instead of `localhost` in config files
3. Verify port 3306 is correct

### Character Set Error
If you see "Unknown character set" error:
1. Change `charset` from `utf8mb4` to `utf8` in config files
2. The application will use `SET NAMES utf8mb4` after connection

## API Endpoints

| Endpoint | Description |
|----------|-------------|
| `/index.php` | Landing page |
| `/catalog.php?page=home&module=loinc` | LOINC home page |
| `/catalog.php?page=home&module=snomed` | SNOMED-CT home page |
| `/catalog.php?page=search&module=loinc&q=<term>` | Search LOINC |
| `/catalog.php?page=search&module=snomed&q=<term>` | Search SNOMED-CT |
| `/catalog.php?page=stats&module=loinc` | LOINC statistics |
| `/catalog.php?page=stats&module=snomed` | SNOMED-CT statistics |
| `/catalog.php?ajax=autocomplete&module=loinc&q=<term>` | Autocomplete suggestions |

## Technology Stack

- **Backend**: PHP 7.3+
- **Database**: MySQL 5.7+ (SNOMED-CT only)
- **Frontend**: HTML5, CSS3, JavaScript (Tailwind CSS, jQuery)
- **Translation**: Google Translate API
- **API Client**: cURL for REST API calls

## License

This project is for educational and medical reference purposes.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## Contact

For issues and feature requests, please use the project's issue tracker.