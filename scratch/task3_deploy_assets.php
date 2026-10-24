<?php

$sourceDir = __DIR__ . '/../docs/Demo_Brand_Assets';
$backupDir = __DIR__ . '/../storage/backups/task3_brand_backup_20261024';

echo "=== TASK 3 ASSET DEPLOYMENT ===\n";

// Ensure destination directories exist
$dirsToEnsure = [
    __DIR__ . '/../public/logo/favicon',
    __DIR__ . '/../public/mem_assets/images/brand-logos/favicon',
    __DIR__ . '/../public/mem_assets/images/favicon',
    __DIR__ . '/../public/adm_assets/assets/img/logo',
    __DIR__ . '/../public/front_assets/assets',
    __DIR__ . '/../public/lyra-assets',
];

foreach ($dirsToEnsure as $d) {
    if (!is_dir($d)) {
        mkdir($d, 0755, true);
        echo "Created directory: " . realpath($d) . "\n";
    }
}

// 1. Deploy logo.png
$logoSource = $sourceDir . '/logo.png';
$logoTargets = [
    __DIR__ . '/../public/logo/logo.png',
    __DIR__ . '/../public/mem_assets/images/brand-logos/logo.png',
    __DIR__ . '/../public/adm_assets/assets/img/logo.png',
    __DIR__ . '/../public/adm_assets/assets/img/logo/erect-black-logo.png',
    __DIR__ . '/../public/adm_assets/assets/img/logo/erect-white-logo.png',
    __DIR__ . '/../public/front_assets/assets/logo-un.png',
    __DIR__ . '/../public/front_assets/assets/logo-un - Copy.png',
    __DIR__ . '/../public/front_assets/assets/logo-fl-tr.png',
    __DIR__ . '/../public/lyra-assets/logo1.png',
];

foreach ($logoTargets as $target) {
    copy($logoSource, $target);
    echo "Copied logo.png -> " . str_replace('\\', '/', substr($target, strlen(__DIR__ . '/../'))) . " (" . filesize($target) . " bytes)\n";
}

// 2. Deploy favicon.ico
$icoSource = $sourceDir . '/favicon.ico';
$icoTargets = [
    __DIR__ . '/../public/favicon.ico',
    __DIR__ . '/../public/logo/favicon/favicon.ico',
    __DIR__ . '/../public/mem_assets/images/brand-logos/favicon/favicon.ico',
    __DIR__ . '/../public/mem_assets/images/favicon/favicon.ico',
];

foreach ($icoTargets as $target) {
    copy($icoSource, $target);
    echo "Copied favicon.ico -> " . str_replace('\\', '/', substr($target, strlen(__DIR__ . '/../'))) . " (" . filesize($target) . " bytes)\n";
}

// 3. Deploy favicon.png (512x512)
$favPngSource = $sourceDir . '/favicon.png';
$favPngTargets = [
    __DIR__ . '/../public/front_assets/assets/favicon.png',
    __DIR__ . '/../public/logo/favicon/favicon.png',
    __DIR__ . '/../public/mem_assets/images/brand-logos/favicon/favicon.png',
];

foreach ($favPngTargets as $target) {
    copy($favPngSource, $target);
    echo "Copied favicon.png -> " . str_replace('\\', '/', substr($target, strlen(__DIR__ . '/../'))) . " (" . filesize($target) . " bytes)\n";
}

// 4. Deploy resolution variants
$variants = [
    'favicon-16x16.png',
    'favicon-32x32.png',
    'favicon-48x48.png',
    'favicon-96x96.png',
    'apple-touch-icon.png',
];

$variantDirs = [
    __DIR__ . '/../public/logo/favicon',
    __DIR__ . '/../public/mem_assets/images/brand-logos/favicon',
    __DIR__ . '/../public/mem_assets/images/favicon',
];

foreach ($variants as $varFile) {
    $src = $sourceDir . '/' . $varFile;
    if (file_exists($src)) {
        foreach ($variantDirs as $vDir) {
            $dest = $vDir . '/' . $varFile;
            copy($src, $dest);
            echo "Copied {$varFile} -> " . str_replace('\\', '/', substr($dest, strlen(__DIR__ . '/../'))) . "\n";
        }
    } else {
        echo "WARNING: {$src} not found!\n";
    }
}

// 5. Deploy manifest images (with and without 'x192'/'x512')
$manifestPairs = [
    'web-app-manifest-192.png' => ['web-app-manifest-192.png', 'web-app-manifest-192x192.png'],
    'web-app-manifest-512.png' => ['web-app-manifest-512.png', 'web-app-manifest-512x512.png'],
];

$manifestDirs = [
    __DIR__ . '/../public',
    __DIR__ . '/../public/logo/favicon',
    __DIR__ . '/../public/mem_assets/images/brand-logos/favicon',
];

foreach ($manifestPairs as $srcName => $targetNames) {
    $src = $sourceDir . '/' . $srcName;
    if (file_exists($src)) {
        foreach ($manifestDirs as $mDir) {
            foreach ($targetNames as $tName) {
                $dest = $mDir . '/' . $tName;
                copy($src, $dest);
                echo "Copied manifest icon -> " . str_replace('\\', '/', substr($dest, strlen(__DIR__ . '/../'))) . "\n";
            }
        }
    }
}

// 6. Generate clean SVG favicon embedding the Demo D icon
$favPngData = base64_encode(file_get_contents($sourceDir . '/favicon.png'));
$svgContent = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" width="512" height="512">
    <image width="512" height="512" href="data:image/png;base64,{$favPngData}"/>
</svg>
SVG;

$svgTargets = [
    __DIR__ . '/../public/logo/favicon/favicon.svg',
    __DIR__ . '/../public/mem_assets/images/brand-logos/favicon/favicon.svg',
    __DIR__ . '/../public/adm_assets/assets/src/img/logo.svg',
];

foreach ($svgTargets as $svgTarget) {
    $dir = dirname($svgTarget);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($svgTarget, $svgContent);
    echo "Generated favicon.svg -> " . str_replace('\\', '/', substr($svgTarget, strlen(__DIR__ . '/../'))) . " (" . filesize($svgTarget) . " bytes)\n";
}

echo "=== DEPLOYMENT COMPLETED SUCCESSFULLY ===\n";
