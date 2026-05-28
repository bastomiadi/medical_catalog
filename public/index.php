<?php
/**
 * Catalog LOINC & SNOMED-CT - Web Interface
 * 
 * Main web interface for medical catalog with Indonesian language support.
 */

// Load configuration
$config = include __DIR__ . '/../config/modules.php';
require_once __DIR__ . '/../modules/Translator.php';

// Get module parameter (default: loinc)
$module = $_GET['module'] ?? 'loinc';
$page = $_GET['page'] ?? 'home';

// Load module-specific files based on requested module
// Use class_exists with autoloader bypass to prevent redeclaration
if ($module === 'snomed') {
    if (!class_exists('SnomedSearch', false)) {
        include __DIR__ . '/../modules/snomed/SnomedSearch.php';
    }
    if (!class_exists('SnomedModule', false)) {
        include __DIR__ . '/../modules/snomed/SnomedModule.php';
    }
} else {
    if (!class_exists('LoincSearch', false)) {
        include __DIR__ . '/../modules/loinc/LoincSearch.php';
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
            <h1 class="text-2xl font-bold text-red-600 mb-4">Error Koneksi Database</h1>
            <p class="text-gray-700 mb-4"><?= htmlspecialchars($error) ?></p>
            <p class="text-sm text-gray-500 mb-4">Pastikan database MySQL sudah dibuat dan data sudah diimpor.</p>
            <a href="?module=loinc" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Coba LOINC</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Get search parameters
$searchKeyword = $_GET['q'] ?? '';
$searchCategory = $_GET['category'] ?? '';
$searchStatus = $_GET['status'] ?? 'ACTIVE';
$loincCode = $_GET['code'] ?? '';

// Normalize status - treat empty string as null
$searchStatus = ($searchStatus === '') ? null : $searchStatus;

// Perform searches
$results = [];
$record = null;
$statistics = $catalog->getStatistics();

if ($searchKeyword) {
    $results = $catalog->searchByKeyword($searchKeyword, $searchStatus);
} elseif ($searchCategory) {
    $results = $catalog->searchByIdTerm($searchCategory, 'component');
} elseif ($loincCode) {
    $record = $catalog->getByCode($loincCode);
}

// Define colors (light mode only)
$bgColor = '#ffffff';
$bgCard = '#f8fafc';
$textPrimary = '#0f172a';
$textSecondary = '#64748b';
$borderColor = '#e2e8f0';
$hoverBg = '#f1f5f9';
$accentColor = '#3b82f6';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Catalog - Indonesian Language Filter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
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
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        /* Modern table styling */
        .dataTables_wrapper {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }
        .dataTables_wrapper .dataTable {
            border-collapse: separate;
            border-spacing: 0;
        }
        .dataTables_wrapper .dataTable th {
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .dataTables_wrapper .dataTable td {
            font-size: 0.875rem;
        }
        .dataTables_wrapper .dataTable tr:hover {
            transition: background-color 0.2s ease;
        }
        .truncate {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body class="transition-colors duration-200" style="background-color: <?= $bgColor ?>; color: <?= $textPrimary ?>;">
    <!-- Top Navigation -->
    <nav class="flex items-center justify-between px-6 py-3 border-b transition-colors duration-200" 
         style="background-color: <?= $bgCard ?>; border-color: <?= $borderColor ?>;">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <a href="#" onclick="switchModule('loinc'); return false;" 
                   class="<?= $module === 'loinc' ? 'text-white bg-blue-500' : '' ?> px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">
                    LOINC
                </a>
                <a href="#" onclick="switchModule('snomed'); return false;" 
                   class="<?= $module === 'snomed' ? 'text-white bg-blue-500' : '' ?> px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">
                    SNOMED CT
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 border-r transition-colors duration-200" style="background-color: <?= $bgCard ?>; border-color: <?= $borderColor ?>;">
            <div class="p-6">
                <h1 class="text-xl font-bold mb-6" style="color: <?= $textPrimary ?>;">
                    Medical Catalog
                </h1>
                
                <nav class="space-y-1">
                    <a href="#" onclick="switchPage('home'); return false;" 
                       class="block px-4 py-3 rounded-lg transition-colors <?= $page === 'home' ? 'text-white' : '' ?>" 
                       style="<?= $page === 'home' ? 'background-color: ' . $accentColor : 'color: ' . $textSecondary ?>; hover: background-color <?= $hoverBg ?>;">
                        <div class="flex items-center gap-3">
                            <span class="<?= $page === 'home' ? 'text-white' : '' ?>">🏠</span>
                            <span class="font-medium">Beranda</span>
                        </div>
                    </a>
                    <a href="#" onclick="switchPage('search'); return false;" 
                       class="block px-4 py-3 rounded-lg transition-colors <?= $page === 'search' ? 'text-white' : '' ?>" 
                       style="<?= $page === 'search' ? 'background-color: ' . $accentColor : 'color: ' . $textSecondary ?>; hover: background-color <?= $hoverBg ?>;">
                        <div class="flex items-center gap-3">
                            <span class="<?= $page === 'search' ? 'text-white' : '' ?>">🔍</span>
                            <span class="font-medium">Pencarian</span>
                        </div>
                    </a>
                </nav>
            </div>
        </aside>
        
        <!-- Main Content Area -->
        <main class="flex-1 p-6">
            <?php if ($page === 'home'): ?>
                <!-- Home Page -->
                <div class="max-w-4xl mx-auto">
                    <div class="mb-8">
                        <h2 class="text-3xl font-bold mb-2" style="color: <?= $textPrimary ?>;">
                            <?= $module === 'snomed' ? 'SNOMED-CT Catalog' : 'LOINC Catalog' ?>
                        </h2>
                        <p class="text-lg" style="color: <?= $textSecondary ?>;">
                            Sistem filter kode medis dengan dukungan bahasa Indonesia
                        </p>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                        <div class="p-4 rounded-xl transition-colors" style="background-color: <?= $bgCard ?>; border: 1px solid <?= $borderColor ?>;">
                            <p class="text-sm mb-1" style="color: <?= $textSecondary ?>;">Total Rekord</p>
                            <p class="text-2xl font-bold" style="color: <?= $textPrimary ?>;"><?= number_format($statistics['total_records']) ?></p>
                        </div>
                        <?php if ($module === 'loinc'): ?>
                        <div class="p-4 rounded-xl transition-colors" style="background-color: <?= $bgCard ?>; border: 1px solid <?= $borderColor ?>;">
                            <p class="text-sm mb-1" style="color: <?= $textSecondary ?>;">Rekord Aktif</p>
                            <p class="text-2xl font-bold" style="color: <?= $textPrimary ?>;"><?= number_format($statistics['active_records']) ?></p>
                        </div>
                        <div class="p-4 rounded-xl transition-colors" style="background-color: <?= $bgCard ?>; border: 1px solid <?= $borderColor ?>;">
                            <p class="text-sm mb-1" style="color: <?= $textSecondary ?>;">Kelas</p>
                            <p class="text-2xl font-bold" style="color: <?= $textPrimary ?>;"><?= number_format($statistics['class_count']) ?></p>
                        </div>
                        <?php else: ?>
                        <div class="p-4 rounded-xl transition-colors" style="background-color: <?= $bgCard ?>; border: 1px solid <?= $borderColor ?>;">
                            <p class="text-sm mb-1" style="color: <?= $textSecondary ?>;">Value Sets</p>
                            <p class="text-2xl font-bold" style="color: <?= $textPrimary ?>;"><?= number_format($statistics['value_set_count']) ?></p>
                        </div>
                        <div class="p-4 rounded-xl transition-colors" style="background-color: <?= $bgCard ?>; border: 1px solid <?= $borderColor ?>;">
                            <p class="text-sm mb-1" style="color: <?= $textSecondary ?>;">Clinical Focus</p>
                            <p class="text-2xl font-bold" style="color: <?= $textPrimary ?>;"><?= number_format($statistics['clinical_focus_count']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quick Search -->
                    <div class="rounded-xl p-6 transition-colors" style="background-color: <?= $bgCard ?>; border: 1px solid <?= $borderColor ?>;">
                        <h3 class="text-lg font-semibold mb-4" style="color: <?= $textPrimary ?>;">Pencarian Cepat</h3>
                        <div class="space-y-4">
                            <div class="relative">
                                <input type="text" id="quickSearchInput" 
                                       placeholder="Cari: darah, glukosa, urine, dll..." 
                                       class="w-full px-4 py-3 pl-12 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       style="background-color: <?= $bgColor ?>; color: <?= $textPrimary ?>; border-color: <?= $borderColor ?>;">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
                            </div>
                            
                            <button onclick="performQuickSearch()" 
                                    class="w-full py-3 px-6 text-white font-medium rounded-lg transition-colors" 
                                    style="background-color: <?= $accentColor ?>;">
                                Cari Sekarang
                            </button>
                        </div>
                    </div>
                    
                    <script>
                        function performQuickSearch() {
                            const keyword = document.getElementById('quickSearchInput').value;
                            if (keyword.trim()) {
                                window.location.href = '?page=search&module=<?= $module ?>&q=' + encodeURIComponent(keyword);
                            }
                        }
                        
                        document.getElementById('quickSearchInput').addEventListener('keypress', function(e) {
                            if (e.key === 'Enter') {
                                performQuickSearch();
                            }
                        });
                    </script>
                </div>
                
            <?php elseif ($page === 'search'): ?>
                <!-- Search Page -->
                <div class="max-w-6xl mx-auto">
                    <!-- Search Bar -->
                    <div class="mb-6">
                        <div class="flex gap-4">
                            <div class="flex-1 relative">
                                <input type="text" id="searchInput" value="<?= htmlspecialchars($searchKeyword) ?>" 
                                       placeholder="Cari: darah, plasma, urine, dll..." 
                                       class="w-full px-4 py-3 pl-12 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       style="background-color: <?= $bgCard ?>; color: <?= $textPrimary ?>; border-color: <?= $borderColor ?>;">
                            </div>
                            <button onclick="performSearch()" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                Cari
                            </button>
                        </div>
                    </div>
                    
                    <script>
                        function performSearch() {
                            const keyword = document.getElementById('searchInput').value;
                            if (keyword.trim()) {
                                window.location.href = '?page=search&module=<?= $module ?>&q=' + encodeURIComponent(keyword);
                            }
                        }
                        
                        document.getElementById('searchInput').addEventListener('keypress', function(e) {
                            if (e.key === 'Enter') {
                                performSearch();
                            }
                        });
                    </script>
                </div>
                
                <!-- Results -->
                <?php if (!empty($results)): ?>
                    <div class="rounded-xl overflow-hidden transition-colors" style="background-color: <?= $bgCard ?>; border: 1px solid <?= $borderColor ?>;">
                        <div class="px-4 py-3 border-b" style="border-color: <?= $borderColor ?>;">
                            <h3 class="font-semibold" style="color: <?= $textPrimary ?>;">
                                Hasil Pencarian (<?= count($results) ?> hasil)
                            </h3>
                        </div>
                        <div class="p-4">
                            <table id="resultsTable" class="w-full display">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-semibold" style="color: <?= $textSecondary ?>;">Kode</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold" style="color: <?= $textSecondary ?>;">Deskripsi</th>
                                        <?php if ($module === 'loinc'): ?>
                                        <th class="px-4 py-3 text-left text-sm font-semibold" style="color: <?= $textSecondary ?>;">Kelas</th>
                                        <?php else: ?>
                                        <th class="px-4 py-3 text-left text-sm font-semibold" style="color: <?= $textSecondary ?>;">Value Set</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold" style="color: <?= $textSecondary ?>;">Clinical Focus</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $row): ?>
                                        <tr class="cursor-pointer hover:bg-opacity-50 transition-colors" onclick="window.location='?page=search&q=<?= urlencode($row[$module === 'snomed' ? 'code' : 'loinc_num']) ?>&module=<?= $module ?>'">
                                            <td class="px-4 py-3 text-sm font-mono" style="color: <?= $textPrimary ?>;"><?= htmlspecialchars($row[$module === 'snomed' ? 'code' : 'loinc_num']) ?></td>
                                            <td class="px-4 py-3 text-sm max-w-md truncate" style="color: <?= $textPrimary ?>;" title="<?= htmlspecialchars($row[$module === 'snomed' ? 'description' : 'long_common_name']) ?>"><?= htmlspecialchars($row[$module === 'snomed' ? 'description' : 'long_common_name']) ?></td>
                                            <?php if ($module === 'loinc'): ?>
                                            <td class="px-4 py-3 text-sm" style="color: <?= $textSecondary ?>;"><?= htmlspecialchars($row['class'] ?? '-') ?></td>
                                            <?php else: ?>
                                            <td class="px-4 py-3 text-sm" style="color: <?= $textSecondary ?>;"><?= htmlspecialchars($row['value_set_name'] ?? '-') ?></td>
                                            <td class="px-4 py-3 text-sm" style="color: <?= $textSecondary ?>;"><?= htmlspecialchars($row['clinical_focus'] ?? '-') ?></td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="rounded-xl p-8 text-center transition-colors" style="background-color: <?= $bgCard ?>; border: 1px solid <?= $borderColor ?>;">
                        <div class="mb-4 text-5xl">🔍</div>
                        <h3 class="text-lg font-semibold mb-2" style="color: <?= $textPrimary ?>;">Belum ada pencarian</h3>
                        <p style="color: <?= $textSecondary ?>;">Masukkan kata kunci untuk mulai mencari</p>
                    </div>
                <?php endif; ?>
                
            <?php elseif ($page === 'stats'): ?>
                <!-- Stats Page -->
                <div class="max-w-4xl mx-auto">
                    <h2 class="text-2xl font-bold mb-6" style="color: <?= $textPrimary ?>;">Statistik</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-5 rounded-xl transition-colors" style="background-color: <?= $bgCard ?>; border: 1px solid <?= $borderColor ?>;">
                            <p class="text-sm mb-1" style="color: <?= $textSecondary ?>;">Total Rekord</p>
                            <p class="text-2xl font-bold" style="color: <?= $textPrimary ?>;"><?= number_format($statistics['total_records']) ?></p>
                        </div>
                        <?php if ($module === 'loinc'): ?>
                        <div class="p-5 rounded-xl transition-colors" style="background-color: <?= $bgCard ?>; border: 1px solid <?= $borderColor ?>;">
                            <p class="text-sm mb-1" style="color: <?= $textSecondary ?>;">Rekord Aktif</p>
                            <p class="text-2xl font-bold" style="color: <?= $textPrimary ?>;"><?= number_format($statistics['active_records']) ?></p>
                        </div>
                        <?php else: ?>
                        <div class="p-5 rounded-xl transition-colors" style="background-color: <?= $bgCard ?>; border: 1px solid <?= $borderColor ?>;">
                            <p class="text-sm mb-1" style="color: <?= $textSecondary ?>;">Value Sets</p>
                            <p class="text-2xl font-bold" style="color: <?= $textPrimary ?>;"><?= number_format($statistics['value_set_count']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function switchModule(module) {
            const currentPage = '<?= $page ?>';
            window.location.href = '?page=' + currentPage + '&module=' + module;
        }
        
        function switchPage(page) {
            const currentModule = '<?= $module ?>';
            window.location.href = '?page=' + page + '&module=' + currentModule;
        }
    </script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTables if results table exists
            const table = document.getElementById('resultsTable');
            if (table) {
                $('#resultsTable').DataTable({
                    language: {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ data per halaman',
                        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                        infoEmpty: 'Tidak ada data',
                        infoFiltered: '(disaring dari _MAX_ data total)',
                        paginate: {
                            first: 'Pertama',
                            last: 'Terakhir',
                            next: 'Selanjutnya',
                            previous: 'Sebelumnya'
                        }
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