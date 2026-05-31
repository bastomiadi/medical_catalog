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

// Load module configs
$loincConfig = include __DIR__ . '/../modules/loinc/config.php';
$snomedConfig = include __DIR__ . '/../modules/snomed/config.php';

// Build unified config
$unifiedConfig = [
    'active_module' => $module,
    'loinc' => $loincConfig,
    'snomed' => $snomedConfig
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
                
                <!-- Module Toggle -->
                <div class="mb-6">
                    <div class="inline-flex rounded-md shadow-sm" role="group">
                        <a href="catalog.php?module=loinc<?= $searchKeyword ? '&q=' . urlencode($searchKeyword) : '' ?>" 
                           class="px-6 py-2 text-sm font-medium rounded-l-lg <?= $module === 'loinc' ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                            LOINC
                        </a>
                        <a href="catalog.php?module=snomed<?= $searchKeyword ? '&q=' . urlencode($searchKeyword) : '' ?>" 
                           class="px-6 py-2 text-sm font-medium rounded-r-lg <?= $module === 'snomed' ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                            SNOMED CT
                        </a>
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
                                    <?php else: ?>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Value Set</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Clinical Focus</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $row): ?>
                                    <?php 
                                    $code = $row['loinc_num'] ?? $row['code'] ?? '-';
                                    $description = $row['long_common_name'] ?? $row['text'] ?? $row['description'] ?? '-';
                                    $class = $row['class'] ?? $row['CLASS'] ?? '-';
                                    ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 cursor-pointer" onclick="copyCode('<?= htmlspecialchars($code) ?>', event)">
                                        <td class="px-4 py-3 text-sm font-mono text-gray-800"><?= htmlspecialchars($code) ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-800 max-w-md truncate" title="<?= htmlspecialchars($description) ?>"><?= htmlspecialchars($description) ?></td>
                                        <?php if ($module === 'loinc'): ?>
                                        <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($class) ?></td>
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
                                // Handle both LOINC and SNOMED data structures
                                const code = item.loinc_num || item.code || '-';
                                const text = item.text || item.description || '-';
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
    
    <!-- Floating Home Button -->
    <a href="index.php" class="fixed bottom-6 right-6 z-50 flex items-center gap-2 px-4 py-3 bg-blue-500 text-white rounded-full shadow-lg hover:bg-blue-600 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l9-9 9 9M5 10v10a1 1 0 001 1h3m10 0h3a1 1 0 001-1V10m-2-2l2 2m-8-2v10"></path>
        </svg>
        <span class="text-sm font-medium">Beranda</span>
    </a>
</body>
</html>