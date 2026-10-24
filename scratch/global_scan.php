<?php
$dir = dirname(__DIR__);
$excludeDirs = ['.git', 'vendor', 'storage', 'scratch', 'node_modules'];
$excludeExts = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'pdf', 'zip', 'tar', 'gz', 'woff', 'woff2', 'ttf', 'eot', 'mp3', 'wav', 'lock'];

$pattern = '/\b(?:quantum|quantum\s*world|quantumarb|quantum\s*arb|qunatumarb|qunatum|crafto|developer\s*rr|the\s*crypto\s*developers|md\.\s*rakibul\s*islam|arthemic)\b/i';

$results = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $path => $item) {
    if ($item->isDir()) {
        continue;
    }
    
    // Check if in excluded directory
    $rel = str_replace($dir . DIRECTORY_SEPARATOR, '', $path);
    $parts = explode(DIRECTORY_SEPARATOR, $rel);
    if (count(array_intersect($parts, $excludeDirs)) > 0) {
        continue;
    }
    
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (in_array($ext, $excludeExts)) {
        continue;
    }

    // Skip large files (> 2MB)
    if ($item->getSize() > 2 * 1024 * 1024) {
        continue;
    }

    $content = file_get_contents($path);
    if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
        foreach ($matches[0] as $match) {
            $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;
            // Get line content
            $lines = explode("\n", $content);
            $lineContent = trim($lines[$line - 1] ?? '');
            // truncate line content if too long (e.g. base64)
            if (strlen($lineContent) > 120) {
                $lineContent = substr($lineContent, 0, 120) . '...';
            }
            $results[] = [
                'file' => $rel,
                'line' => $line,
                'match' => $match[0],
                'text' => $lineContent
            ];
        }
    }
}

echo "Total occurrences found: " . count($results) . "\n\n";
foreach ($results as $res) {
    echo "{$res['file']}:{$res['line']} [{$res['match']}] -> {$res['text']}\n";
}
