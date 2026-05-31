<?php
/**
 * Catalog LOINC & SNOMED-CT - Web Interface
 * 
 * Main web interface for medical catalog with Indonesian language support.
 * LOINC module supports both REST API and MySQL database sources.
 * SNOMED-CT module uses MySQL database.
 */

// Load configuration
$config = include __DIR__ . '/../config/modules.php';
require_once __DIR__ . '/../modules/Translator.php';

// Get module parameter (default: loinc)
$module = $_GET['module'] ?? 'loinc';

// Load module-specific files based on requested module
if ($module === 'snomed') {
    if (!class_exists('SnomedSearch', false)) {
        include __DIR__ . '/../modules/snomed/SnomedSearch.php';
    }
    if (!class_exists('SnomedModule', false)) {
        include __DIR__ . '/../modules/snomed/SnomedModule.php';
    }
} else {
    // Load LOINC files (supports both API and database modes)
    if (!class_exists('LoincApi', false)) {
        include __DIR__ . '/../modules/loinc/LoincApi.php';
    }
    if (!class_exists('LoincSearch', false)) {
        include __DIR__ . '/../modules/loinc/LoincSearch.php';
    }
    if (!class_exists('LoincDbSearch', false)) {
        include __DIR__ . '/../modules/loinc/LoincDbSearch.php';
    }
    if (!class_exists('LoincModule', false)) {
        include __DIR__ . '/../modules/loinc/LoincModule.php';
    }
}

// Initialize module
try {
    if ($module === 'snomed') {
        $moduleConfig = include __DIR__ . '/../modules/snomed/config.php';
        $catalog = new SnomedModule($moduleConfig);
    } else {
        $moduleConfig = include __DIR__ . '/../modules/loinc/config.php';
        $catalog = new LoincModule($moduleConfig);
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Error - Catalog LOINC & SNOMED-CT</title>
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

// Define colors
$bgColor = '#ffffff';
$bgCard = '#f8fafc';
$textPrimary = '#0f172a';
$textSecondary = '#64748b';
$borderColor = '#e2e8f0';
$accentColor = '#3b82f6';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Catalog - Indonesian Language Filter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
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
        .sidebar-active { background-color: #3b82f6; color: white; }
        .sidebar-hover:hover { background-color: #f1f5f9; }
        .dataTables_wrapper .dataTable th { font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
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
    <!-- Top Navigation -->
    <nav class="flex items-center justify-between px-6 py-3 bg-white border-b border-gray-200">
        <a href="catalog.php?module=loinc" class="text-lg font-bold text-gray-800">
            Medical Catalog
        </a>
    </nav>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200">
            <div class="p-6">
                <nav class="space-y-1">
                    <a href="catalog.php?module=loinc" 
                       class="block px-4 py-3 rounded-lg text-gray-700 sidebar-hover <?= $module === 'loinc' ? 'sidebar-active' : '' ?>">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h4m5-6v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v8a4 4 0 004 4h6a4 4 0 004-4v-2"></path>
                            </svg>
                            <span class="font-medium">LOINC</span>
                        </div>
                    </a>
                    <a href="catalog.php?module=snomed" 
                       class="block px-4 py-3 rounded-lg text-gray-700 sidebar-hover <?= $module === 'snomed' ? 'sidebar-active' : '' ?>">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-5.5c0-1.5-1.5-3-3-3H8c-1.5 0-3 1.5-3 3v5.5c0 1.5 1.5 3 3 3h8.5c1.5 0 3-1.5 3-3z"></path>
                            </svg>
                            <span class="font-medium">SNOMED CT</span>
                        </div>
                    </a>
                </nav>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 p-6">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold mb-2 text-gray-800">
                        <?= $module === 'snomed' ? 'SNOMED-CT Catalog' : 'LOINC Catalog' ?>
                    </h1>
                    <p class="text-lg text-gray-600">
                        Sistem filter kode medis dengan dukungan bahasa Indonesia
                    </p>
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
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 cursor-pointer" onclick="copyCode('<?= htmlspecialchars($code) ?>')">
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
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script>
        // Copy code to clipboard
        function copyCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                alert('Kode berhasil disalin: ' + code);
            });
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
                                    if (!e.target.closest('svg')) {
                                        copyCode(code);
                                    }
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
        
        $(document).ready(function() {
            const table = document.getElementById('resultsTable');
            if (table) {
                $('#resultsTable').DataTable({
                    language: {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ data per halaman',
                        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                        infoEmpty: 'Tidak ada data',
                        infoFiltered: '(disaring dari _MAX_ data total)',
                        paginate: { first: 'Pertama', last: 'Terakhir', next: 'Selanjutnya', previous: 'Sebelumnya' }
                    },
                    responsive: true,
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
                    order: [[0, 'asc']]
                });
            }
        });
    </script>
</body>
</html>