<?php
/**
 * Catalog LOINC & SNOMED-CT - Unified Web Interface
 * 
 * Unified web interface for medical catalog with Indonesian language support.
 * Combines LOINC and SNOMED-CT modules in a single view without sidebar/navbar.
 */

// Load configuration
$config = include __DIR__ . '/../config/modules.php';
require_once __DIR__ . '/../modules/Translator.php';
require_once __DIR__ . '/../modules/MedicalCatalogModule.php';

// Get module parameter (default: loinc)
$module = $_GET['module'] ?? 'loinc';

// Load module-specific files
if (!class_exists('LoincModule', false)) {
    include __DIR__ . '/../modules/loinc/LoincModule.php';
}
if (!class_exists('SnomedModule', false)) {
    include __DIR__ . '/../modules/snomed/SnomedModule.php';
}
if (!class_exists('IcdModule', false)) {
    include __DIR__ . '/../modules/icd10/IcdModule.php';
}
if (!class_exists('Icd9ProcedureModule', false)) {
    include __DIR__ . '/../modules/icd9_procedure/Icd9ProcedureModule.php';
}
if (!class_exists('HcpcsModule', false)) {
    include __DIR__ . '/../modules/hcpcs/HcpcsModule.php';
}
if (!class_exists('HpoModule', false)) {
    include __DIR__ . '/../modules/hpo/HpoModule.php';
}
if (!class_exists('Icd9DiagnoseModule', false)) {
    include __DIR__ . '/../modules/icd9_diagnose/Icd9DiagnoseModule.php';
}
if (!class_exists('Icd11Module', false)) {
    include __DIR__ . '/../modules/icd11_codes/Icd11Module.php';
}
if (!class_exists('MajorSurgeriesModule', false)) {
    include __DIR__ . '/../modules/major_surgeries_and_implants/MajorSurgeriesModule.php';
}
if (!class_exists('MedicalConditionsModule', false)) {
    include __DIR__ . '/../modules/medical_conditions/MedicalConditionsModule.php';
}
if (!class_exists('UcumModule', false)) {
    include __DIR__ . '/../modules/ucum/UcumModule.php';
}
if (!class_exists('RxTermsModule', false)) {
    include __DIR__ . '/../modules/prescribable_drug_ingredients_RxTerms/RxTermsModule.php';
}
if (!class_exists('KfaModule', false)) {
    include __DIR__ . '/../modules/kfa/KfaModule.php';
}

// Load module configs
$loincConfig = include __DIR__ . '/../modules/loinc/config.php';
$snomedConfig = include __DIR__ . '/../modules/snomed/config.php';
$icd10Config = include __DIR__ . '/../modules/icd10/config.php';
$icd9ProcedureConfig = include __DIR__ . '/../modules/icd9_procedure/config.php';
$icd9DiagnoseConfig = include __DIR__ . '/../modules/icd9_diagnose/config.php';
$icd11Config = include __DIR__ . '/../modules/icd11_codes/config.php';
$hcpcsConfig = include __DIR__ . '/../modules/hcpcs/config.php';
$hpoConfig = include __DIR__ . '/../modules/hpo/config.php';
$majorSurgeriesConfig = include __DIR__ . '/../modules/major_surgeries_and_implants/config.php';
$medicalConditionsConfig = include __DIR__ . '/../modules/medical_conditions/config.php';
$ucumConfig = include __DIR__ . '/../modules/ucum/config.php';
$rxTermsConfig = include __DIR__ . '/../modules/prescribable_drug_ingredients_RxTerms/config.php';
$kfaConfig = include __DIR__ . '/../modules/kfa/config.php';

// Build unified config
$unifiedConfig = [
    'active_module' => $module,
    'loinc' => $loincConfig,
    'snomed' => $snomedConfig,
    'icd10' => $icd10Config,
    'icd9_procedure' => $icd9ProcedureConfig,
    'icd9_diagnose' => $icd9DiagnoseConfig,
    'icd11_codes' => $icd11Config,
    'hcpcs' => $hcpcsConfig,
    'hpo' => $hpoConfig,
    'major_surgeries_and_implants' => $majorSurgeriesConfig,
    'medical_conditions' => $medicalConditionsConfig,
    'ucum' => $ucumConfig,
    'prescribable_drug_ingredients_RxTerms' => $rxTermsConfig,
    'kfa' => $kfaConfig
];

// Initialize unified module
try {
    $catalog = new MedicalCatalogModule($unifiedConfig);
    $catalog->setActiveModule($module);
} catch (Exception $e) {
    $error = $e->getMessage();
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Error - Medical Catalog</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-md">
            <h1 class="text-2xl font-bold text-red-600 mb-4">Error</h1>
            <p class="text-gray-700 mb-4"><?= htmlspecialchars($error) ?></p>
            <p class="text-sm text-gray-500 mb-4">Pastikan koneksi internet tersedia untuk mengakses API LOINC.</p>
            <a href="catalog.php?module=loinc" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Coba LOINC</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Get search parameters
$searchKeyword = $_GET['q'] ?? '';
$searchStatus = $_GET['status'] ?? 'ACTIVE';

// Normalize status
$searchStatus = ($searchStatus === '') ? null : $searchStatus;

// Handle autocomplete AJAX request
if (isset($_GET['ajax']) && $_GET['ajax'] === 'autocomplete') {
    header('Content-Type: application/json');
    $autocompleteResults = [];
    if ($searchKeyword) {
        $searchResults = $catalog->searchByKeyword($searchKeyword, null);
        $autocompleteResults = array_slice($searchResults, 0, 10);
    }
    echo json_encode($autocompleteResults);
    exit;
}

// Perform search if keyword provided
$results = [];
if ($searchKeyword) {
    $searchResults = $catalog->searchByKeyword($searchKeyword, $searchStatus);
    $results = $searchResults;
}

// Get module info for display
$moduleInfo = $catalog->getModuleInfo();
$currentModuleInfo = $moduleInfo[$module] ?? $moduleInfo['loinc'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Catalog - Indonesian Language Filter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#64748b',
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444'
                    }
                }
            }
        }
    </script>
    <style>
        .truncate { max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .autocomplete-header {
            position: sticky;
            top: 0;
            background-color: #f8fafc;
            z-index: 10;
            border-bottom: 2px solid #e2e8f0;
        }
        .autocomplete-header th {
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            padding: 8px 12px;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex min-h-screen flex-col">
        <!-- Main Content -->
        <main class="flex-1 p-6">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold mb-2 text-gray-800">
                        <?= htmlspecialchars($currentModuleInfo['name']) ?> Catalog
                    </h1>
                    <p class="text-lg text-gray-600">
                        Sistem filter kode medis dengan dukungan bahasa Indonesia
                    </p>
                </div>
                
                <!-- Module Selector (Compact Modern Style) -->
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-sm font-medium text-gray-600">Pilih Modul:</span>
                    </div>
                    <div class="flex flex-wrap gap-2 max-h-64 overflow-y-auto pr-1">
                        <?php
                        $modules = [
                        'loinc' => 'LOINC',
                        'snomed' => 'SNOMED CT',
                        'kfa' => 'KFA',
                        'icd10' => 'ICD-10',
                        'icd9_procedure' => 'ICD-9 Procedure',
                        'icd9_diagnose' => 'ICD-9 Diagnoses',
                        'icd11_codes' => 'ICD-11 Codes',
                        'hcpcs' => 'HCPCS',
                        'hpo' => 'HPO',
                        'major_surgeries_and_implants' => 'Major Surgeries',
                        'medical_conditions' => 'Medical Conditions',
                        'ucum' => 'UCUM',
                        'prescribable_drug_ingredients_RxTerms' => 'Drug Ingredients from RxTerms'
                    ];
                        foreach ($modules as $modKey => $modLabel): 
                            $isActive = $module === $modKey;
                        ?>
                            <a href="catalog.php?module=<?= $modKey ?><?= $searchKeyword ? '&q=' . urlencode($searchKeyword) : '' ?>" 
                               class="<?= $isActive 
                                   ? 'bg-blue-500 text-white shadow-md transform scale-105' 
                                   : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' ?> 
                                   px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 
                                   hover:shadow-sm whitespace-nowrap">
                                   <?= $modLabel ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Search Box -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 mb-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Cari Kode Medis</h3>
                    <div class="relative">
                        <input type="text" id="quickSearchInput" autocomplete="off"
                               value="<?= htmlspecialchars($searchKeyword) ?>"
                               placeholder="Ketik untuk mencari: contoh. glukosa, darah..." 
                               class="w-full px-4 py-3 pl-12 pr-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all" 
                               style="background-color: #ffffff;">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4-4m4 4H3"></path>
                        </svg>
                        <!-- Autocomplete Dropdown -->
                        <div id="autocompleteDropdown" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto" style="display: none;"></div>
                    </div>
                </div>
                
                <!-- Results Table -->
                <?php if (!empty($results)): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <table id="resultsTable" class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Kode</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Deskripsi</th>
                                    <?php if ($module === 'loinc'): ?>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Kelas</th>
                                        <?php elseif ($module === 'snomed'): ?>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Value Set</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Clinical Focus</th>
                                        <?php elseif ($module === 'kfa'): ?>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Produsen</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Harga</th>
                                        <?php elseif ($module === 'icd10' || $module === 'icd9_procedure' || $module === 'hcpcs' || $module === 'major_surgeries_and_implants' || $module === 'medical_conditions' || $module === 'ucum' || $module === 'prescribable_drug_ingredients_RxTerms'): ?>
                                        <!-- ICD-10, ICD-9 Procedure, HCPCS, Major Surgeries, Medical Conditions, UCUM, and RxTerms have only 2 columns -->
                                        <?php else: ?>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Value Set</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Clinical Focus</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $row): ?>
                                        <?php 
                                        $code = $row['loinc_num'] ?? $row['code'] ?? $row['icd_code'] ?? $row['procedure_code'] ?? $row['cs_code'] ?? $row['kfa_code'] ?? '-';
                                        $description = $row['long_common_name'] ?? $row['name'] ?? $row['text'] ?? $row['long_desc'] ?? $row['description'] ?? $row['consumer_name'] ?? $row['primary_name'] ?? $row['nama_dagang'] ?? '-';
                                        $class = $row['class'] ?? $row['CLASS'] ?? '-';
                                        $manufacturer = $row['manufacturer'] ?? '-';
                                        $fixPrice = $row['fix_price'] ?? null;
                                        $hetPrice = $row['het_price'] ?? null;
                                        ?>
                                        <tr class="border-b border-gray-100 hover:bg-gray-50 cursor-pointer" onclick="copyCode('<?= htmlspecialchars($code) ?>', event)">
                                            <td class="px-4 py-3 text-sm font-mono text-gray-800"><?= htmlspecialchars($code) ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-800 max-w-md truncate" title="<?= htmlspecialchars($description) ?>"><?= htmlspecialchars($description) ?></td>
                                            <?php if ($module === 'loinc'): ?>
                                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($class) ?></td>
                                            <?php elseif ($module === 'snomed'): ?>
                                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($row['value_set_name'] ?? '-') ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($row['clinical_focus'] ?? '-') ?></td>
                                            <?php elseif ($module === 'kfa'): ?>
                                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($manufacturer) ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-600"><?= $fixPrice ? 'Rp ' . number_format($fixPrice) : ($hetPrice ? 'Rp ' . number_format($hetPrice) : '-') ?></td>
                                            <?php elseif ($module === 'icd10'): ?>
                                            <!-- ICD-10 has only 2 columns -->
                                            <?php else: ?>
                                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($row['value_set_name'] ?? '-') ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($row['clinical_focus'] ?? '-') ?></td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif ($searchKeyword): ?>
                    <div class="bg-white rounded-xl p-6 text-center border border-gray-200">
                        <p class="text-gray-500">Tidak ada hasil ditemukan untuk "<?= htmlspecialchars($searchKeyword) ?>"</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        // Copy code to clipboard
        function copyCode(code, event) {
            // Prevent event bubbling if event is provided
            if (event) {
                event.stopPropagation();
            }
            
            // Try modern clipboard API first
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(code).then(() => {
                    alert('Kode berhasil disalin: ' + code);
                }).catch(err => {
                    // Fallback to execCommand
                    copyWithExecCommand(code);
                });
            } else {
                // Fallback for non-secure contexts
                copyWithExecCommand(code);
            }
        }
        
        // Fallback copy method using execCommand
        function copyWithExecCommand(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                alert('Kode berhasil disalin: ' + text);
            } catch (err) {
                alert('Gagal menyalin: ' + err);
            }
            document.body.removeChild(textArea);
        }
        
        // Autocomplete functionality
        let autocompleteTimeout;
        document.getElementById('quickSearchInput').addEventListener('input', function(e) {
            clearTimeout(autocompleteTimeout);
            const query = e.target.value;
            const dropdown = document.getElementById('autocompleteDropdown');
            
            if (query.length < 2) {
                dropdown.style.display = 'none';
                return;
            }
            
            autocompleteTimeout = setTimeout(() => {
                fetch('?ajax=autocomplete&module=<?= $module ?>&q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        dropdown.innerHTML = '';
                        if (data.length > 0) {
                            const table = document.createElement('table');
                            table.className = 'w-full';
                            table.innerHTML = '<thead></thead><tbody></tbody>';
                            const thead = table.querySelector('thead');
                            const tbody = table.querySelector('tbody');
                            
                            // Add header row at the top
                            const headerRow = document.createElement('tr');
                            headerRow.className = 'autocomplete-header';
                            headerRow.innerHTML = '<th class="px-4 py-2 text-sm font-semibold text-gray-600">Kode</th><th class="px-4 py-2 text-sm font-semibold text-gray-600">Deskripsi</th><th class="px-4 py-2 text-sm font-semibold text-gray-600">Aksi</th>';
                            thead.appendChild(headerRow);
                            
                            data.forEach(item => {
                                const tr = document.createElement('tr');
                                tr.className = 'border-b border-gray-100 hover:bg-gray-50 cursor-pointer';
                                // Handle LOINC, SNOMED, ICD-10, HCPCS, Major Surgeries, UCUM, and KFA data structures
                                const code = item.loinc_num || item.code || item.icd_code || item.procedure_code || item.cs_code || item.kfa_code || '-';
                                const text = item.name || item.text || item.description || item.long_desc || item.consumer_name || item.primary_name || item.nama_dagang || '-';
                                tr.innerHTML = '<td class="px-4 py-2 text-sm font-mono text-gray-800">' + code + '</td><td class="px-4 py-2 text-sm text-gray-500">' + text + '</td><td class="px-4 py-2 text-right"><svg class="w-4 h-4 text-blue-600 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg></td>';
                                tr.onclick = (e) => {
                                    e.stopPropagation();
                                    copyCode(code);
                                };
                                tbody.appendChild(tr);
                            });
                            
                            dropdown.appendChild(table);
                            dropdown.style.display = 'block';
                        } else {
                            dropdown.style.display = 'none';
                        }
                    });
            }, 300);
        });
        
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#quickSearchInput') && !e.target.closest('#autocompleteDropdown')) {
                document.getElementById('autocompleteDropdown').style.display = 'none';
            }
        });
        
        document.getElementById('quickSearchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const keyword = this.value;
                if (keyword.trim()) {
                    window.location.href = '?q=' + encodeURIComponent(keyword);
                }
            }
        });
    </script>
    
    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-8">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-400 text-xs">
                © 2024 Medical Catalog System. Untuk tujuan pendidikan dan referensi medis.
            </p>
            <p class="text-gray-500 text-xs mt-1">
                Dibuat oleh <a href="https://bastomi.my.id" target="_blank" class="text-blue-400 hover:text-blue-300">bastomi.my.id</a>
            </p>
        </div>
    </footer>
    
    <!-- Floating Home Button -->
    <a href="index.php" class="fixed bottom-6 right-6 z-50 flex items-center gap-2 px-4 py-3 bg-blue-500 text-white rounded-full shadow-lg hover:bg-blue-600 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l9-9 9 9M5 10v10a1 1 0 001 1h3m10 0h3a1 1 0 001-1V10m-2-2l2 2m-8-2v10"></path>
        </svg>
        <span class="text-sm font-medium">Beranda</span>
    </a>
</body>
</html>