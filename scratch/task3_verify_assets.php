<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TASK 3 COMPREHENSIVE VERIFICATION ===\n\n";

$checkFiles = [
    // Main logos
    'public/logo/logo.png',
    'public/mem_assets/images/brand-logos/logo.png',
    'public/mem_assets/images/logo.png',
    'public/adm_assets/assets/img/logo.png',
    'public/adm_assets/assets/img/logo/erect-black-logo.png',
    'public/adm_assets/assets/img/logo/erect-white-logo.png',
    'public/front_assets/assets/logo-un.png',
    'public/front_assets/assets/logo-un - Copy.png',
    'public/front_assets/assets/logo-fl-tr.png',
    'public/lyra-assets/logo1.png',

    // Favicons (.ico)
    'public/favicon.ico',
    'public/logo/favicon/favicon.ico',
    'public/mem_assets/images/brand-logos/favicon/favicon.ico',
    'public/mem_assets/images/favicon/favicon.ico',

    // Favicons (.png)
    'public/front_assets/assets/favicon.png',
    'public/logo/favicon/favicon.png',
    'public/mem_assets/images/brand-logos/favicon/favicon.png',

    // Variants
    'public/logo/favicon/favicon-16x16.png',
    'public/logo/favicon/favicon-32x32.png',
    'public/logo/favicon/favicon-48x48.png',
    'public/logo/favicon/favicon-96x96.png',
    'public/logo/favicon/apple-touch-icon.png',

    'public/mem_assets/images/brand-logos/favicon/favicon-16x16.png',
    'public/mem_assets/images/brand-logos/favicon/favicon-32x32.png',
    'public/mem_assets/images/brand-logos/favicon/favicon-48x48.png',
    'public/mem_assets/images/brand-logos/favicon/favicon-96x96.png',
    'public/mem_assets/images/brand-logos/favicon/apple-touch-icon.png',

    'public/mem_assets/images/favicon/favicon-16x16.png',
    'public/mem_assets/images/favicon/favicon-32x32.png',
    'public/mem_assets/images/favicon/favicon-48x48.png',
    'public/mem_assets/images/favicon/favicon-96x96.png',
    'public/mem_assets/images/favicon/apple-touch-icon.png',

    // Manifest files & icons
    'public/logo/favicon/site.webmanifest',
    'public/mem_assets/images/brand-logos/favicon/site.webmanifest',
    'public/web-app-manifest-192.png',
    'public/web-app-manifest-192x192.png',
    'public/web-app-manifest-512.png',
    'public/web-app-manifest-512x512.png',
    'public/logo/favicon/web-app-manifest-192.png',
    'public/logo/favicon/web-app-manifest-192x192.png',
    'public/logo/favicon/web-app-manifest-512.png',
    'public/logo/favicon/web-app-manifest-512x512.png',

    // SVG favicons
    'public/logo/favicon/favicon.svg',
    'public/mem_assets/images/brand-logos/favicon/favicon.svg',
    'public/adm_assets/assets/src/img/logo.svg',
];

$allFilesValid = true;
foreach ($checkFiles as $relPath) {
    $fullPath = __DIR__ . '/../' . $relPath;
    if (!file_exists($fullPath)) {
        echo "FAIL: File missing: {$relPath}\n";
        $allFilesValid = false;
    } else {
        $size = filesize($fullPath);
        if ($size === 0) {
            echo "FAIL: File empty (0 bytes): {$relPath}\n";
            $allFilesValid = false;
        } else {
            echo "OK: {$relPath} ({$size} bytes)\n";
        }
    }
}

echo "\n--- DATABASE INTEGRITY CHECK ---\n";
$memberCount = \Illuminate\Support\Facades\DB::table('member_details')->count();
$stakingCount = \Illuminate\Support\Facades\DB::table('staking_details')->count();
echo "member_details count: {$memberCount} (Expected: 20)\n";
echo "staking_details count: {$stakingCount} (Expected: 14)\n";

if ($memberCount === 20 && $stakingCount === 14 && $allFilesValid) {
    echo "\n=== ALL VERIFICATIONS PASSED ===\n";
} else {
    echo "\n=== SOME CHECKS FAILED ===\n";
}
