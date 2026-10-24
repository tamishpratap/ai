<?php
$dir = dirname(__DIR__);
$excludeDirs = ['.git', 'vendor', 'storage', 'scratch', 'node_modules', 'docs'];
$excludeExts = ['pdf', 'zip', 'tar', 'gz', 'woff', 'woff2', 'ttf', 'eot', 'mp3', 'wav', 'lock'];

$pattern = '/(?:logo|favicon|apple-touch-icon|site\.webmanifest|manifest)/i';

$results = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $path => $item) {
    if ($item->isDir()) continue;
    $rel = str_replace($dir . DIRECTORY_SEPARATOR, '', $path);
    $parts = explode(DIRECTORY_SEPARATOR, $rel);
    if (count(array_intersect($parts, $excludeDirs)) > 0) continue;
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (in_array($ext, $excludeExts)) continue;
    if ($item->getSize() > 2 * 1024 * 1024) continue;

    $content = file_get_contents($path);
    if (preg_match_all('/[^\r\n]*(?:logo|favicon|apple-touch-icon|site\.webmanifest)[^\r\n]*/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
        foreach ($matches[0] as $match) {
            $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;
            $lineContent = trim($match[0]);
            if (strlen($lineContent) > 140) {
                $lineContent = substr($lineContent, 0, 140) . '...';
            }
            $results[] = [
                'file' => $rel,
                'line' => $line,
                'text' => $lineContent
            ];
        }
    }
}

echo "Total logo/favicon references found: " . count($results) . "\n\n";
foreach ($results as $r) {
    echo "{$r['file']}:{$r['line']} -> {$r['text']}\n";
}
