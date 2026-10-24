<?php
$dir = dirname(__DIR__);
$targetDirs = ['resources/views', 'public/logo', 'public/mem_assets', 'public/adm_assets'];

$results = [];

foreach ($targetDirs as $td) {
    $fullDir = $dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $td);
    if (!is_dir($fullDir)) continue;

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($fullDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $path => $item) {
        if ($item->isDir()) continue;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($ext, ['php', 'blade.php', 'html', 'json', 'webmanifest', 'css'])) continue;

        $content = file_get_contents($path);
        $rel = str_replace($dir . DIRECTORY_SEPARATOR, '', $path);
        
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            if (preg_match('/(?:logo|favicon|apple-touch|webmanifest)/i', $line)) {
                // Ignore pure logout or other irrelevant terms
                if (preg_match('/(?:logout|logo-text|log-out)/i', $line) && !preg_match('/(?:logo\.(?:png|svg|jpg)|favicon|\/logo|brand-logo|logo-img|logo-centered)/i', $line)) {
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
}

echo "Filtered logo/favicon references: " . count($results) . "\n\n";
foreach ($results as $r) {
    echo "{$r['file']}:{$r['line']} -> {$r['text']}\n";
}
