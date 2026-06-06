<?php
/**
 * Catalog LOINC & SNOMED-CT - Landing Page
 * 
 * Modern landing page for medical catalog system.
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Catalog - Indonesian Language Filter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .feature-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .feature-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Hero Section -->
    <div class="gradient-bg text-white min-h-screen flex items-center justify-center">
        <div class="container mx-auto px-4 py-16">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">
                    Medical Catalog System
                </h1>
                <p class="text-lg md:text-xl mb-8 opacity-90">
                    Sistem katalog kode medis dengan dukungan bahasa Indonesia untuk pencarian dan filter kode medis
                </p>
                
                <!-- Module Selector (Compact Modern Style) -->
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-sm font-medium text-white opacity-90">Daftar Katalog:</span>
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
                        ?>
                            <a href="catalog.php?module=<?= $modKey ?>" 
                               class="bg-white bg-opacity-10 text-white border border-white border-opacity-20 hover:bg-opacity-20 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 hover:shadow-md whitespace-nowrap">
                               <?= $modLabel ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Features Section -->
    <div class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">Fitur Unggulan</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="feature-card bg-gray-50 rounded-xl p-6 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4-4m4 4H3"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold mb-2 text-gray-800">Pencarian Cerdas</h3>
                        <p class="text-gray-600 text-sm">Cari kode medis menggunakan bahasa Indonesia dengan terjemahan otomatis</p>
                    </div>
                    <div class="feature-card bg-gray-50 rounded-xl p-6 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M3 9h12m-3 8h3m4-6v6m-1-9a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold mb-2 text-gray-800">Dukungan Bahasa</h3>
                        <p class="text-gray-600 text-sm">Terjemahan otomatis dari bahasa Indonesia ke bahasa Inggris untuk pencarian yang tepat</p>
                    </div>
                    <div class="feature-card bg-gray-50 rounded-xl p-6 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold mb-2 text-gray-800">Data Terkini</h3>
                        <p class="text-gray-600 text-sm">Data LOINC diperoleh langsung dari API resmi clinicaltables.nlm.nih.gov</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-400 text-sm">
                © 2024 Medical Catalog System. Untuk tujuan pendidikan dan referensi medis.
            </p>
            <p class="text-gray-500 text-xs mt-2">
                Dibuat oleh <a href="https://bastomi.my.id" target="_blank" class="text-blue-400 hover:text-blue-300">bastomi.my.id</a>
            </p>
        </div>
    </div>
</body>
</html>