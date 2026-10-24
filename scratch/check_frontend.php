<?php
$file = 'resources/views/frontend/index.blade.php';
$content = file_get_contents($file);
preg_match_all('/[^\n\r]*(?:quantum|qunatum)[^\n\r]*/i', $content, $matches, PREG_OFFSET_CAPTURE);
echo "Occurrences in frontend/index.blade.php:\n";
foreach ($matches[0] as $match) {
    echo "Line: " . substr_count(substr($content, 0, $match[1]), "\n") + 1 . ": " . trim($match[0]) . "\n";
}
