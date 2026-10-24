<?php
$backupDir = dirname(__DIR__) . '/storage/backups/task3_brand_backup_20261024';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$filesToBackup = [
    'public/favicon.ico',
    'public/logo/logo.png',
    'public/logo/favicon/favicon.ico',
    'public/logo/favicon/favicon-96x96.png',
    'public/logo/favicon/favicon.svg',
    'public/logo/favicon/apple-touch-icon.png',
    'public/logo/favicon/site.webmanifest',
    'public/adm_assets/assets/img/logo/erect-black-logo.png',
    'public/adm_assets/assets/img/logo/erect-white-logo.png',
    'public/adm_assets/assets/img/logo.png',
    'public/adm_assets/assets/src/img/logo.svg',
    'public/front_assets/assets/favicon.png',
    'public/front_assets/assets/logo-fl-tr.png',
    'public/front_assets/assets/logo-rd.jpg',
    'public/front_assets/assets/logo-un - Copy.png',
    'public/front_assets/assets/logo-un.png',
    'public/lyra-assets/logo1.png',
    'public/mem_assets/images/brand-logos/favicon/favicon-96x96.png',
    'public/mem_assets/images/brand-logos/favicon/favicon.ico',
    'public/mem_assets/images/brand-logos/favicon/favicon.svg',
    'public/mem_assets/images/brand-logos/favicon/site.webmanifest',
    'public/mem_assets/images/brand-logos/logo.png',
    'public/mem_assets/images/favicon/favicon-96x96.png',
];

$backedUp = [];
foreach ($filesToBackup as $rel) {
    $src = dirname(__DIR__) . '/' . $rel;
    if (file_exists($src)) {
        $dest = $backupDir . '/' . str_replace('/', '__', $rel);
        copy($src, $dest);
        $backedUp[] = ['rel' => $rel, 'size' => filesize($src), 'dest' => $dest];
        echo "Backed up: $rel (" . filesize($src) . " bytes)\n";
    } else {
        echo "Source file does not exist (skipped): $rel\n";
    }
}

echo "\nTotal files backed up: " . count($backedUp) . "\n";
