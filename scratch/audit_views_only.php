<?php
$dir = dirname(__DIR__);

$viewsDir = $dir . '/resources/views';
$results = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $path => $item) {
    if ($item->isDir()) continue;
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($ext !== 'php') continue;

    $content = file_get_contents($path);
    $rel = str_replace($dir . DIRECTORY_SEPARATOR, '', $path);
    
    $lines = explode("\n", $content);
    foreach ($lines as $i => $line) {
        if (preg_match('/(?:logo|favicon|apple-touch|webmanifest)/i', $line)) {
            if (preg_match('/(?:logout)/i', $line) && !preg_match('/(?:logo\.(?:png|svg|jpg)|favicon|\/logo|brand-logo|logo-img|logo-centered)/i', $line)) {
                continue;
            }
            $results[] = [
                'file' => $rel,
                'line' => $i + 1,
                'text' => trim($line)
            ];
        }
    }
}

// Also check webmanifest files
$manifests = [
    'public/logo/favicon/site.webmanifest',
    'public/mem_assets/images/brand-logos/favicon/site.webmanifest'
];
foreach ($manifests as $m) {
    $full = $dir . '/' . $m;
    if (file_exists($full)) {
        $content = file_get_contents($full);
        $results[] = [
            'file' => $m,
            'line' => 1,
            'text' => $content
        ];
    }
}

echo "Found " . count($results) . " template and manifest occurrences:\n\n";
foreach ($results as $r) {
    echo "{$r['file']}:{$r['line']} -> {$r['text']}\n";
}
