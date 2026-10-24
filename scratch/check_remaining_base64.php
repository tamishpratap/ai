<?php

$file = __DIR__ . '/../resources/views/frontend/index.blade.php';
$content = file_get_contents($file);

preg_match_all('/data:image\/png;base64,[^"\'\s)]+/', $content, $matches, PREG_OFFSET_CAPTURE);

foreach ($matches[0] as $i => $m) {
    $offset = $m[1];
    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
    $snippetStart = max(0, $offset - 100);
    $snippet = substr($content, $snippetStart, 250);
    echo "Match #$i at line $line:\n" . str_replace("\n", " ", $snippet) . "\n\n";
}
