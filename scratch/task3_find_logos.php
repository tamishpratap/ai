<?php

$file = __DIR__ . '/../resources/views/frontend/index.blade.php';
$content = file_get_contents($file);

// Find navbar logo
if (preg_match('/(<a href="#home"[^>]*>.*?<\/a>)/s', $content, $m)) {
    echo "NAVBAR MATCH:\n" . substr($m[1], 0, 150) . " ...\n";
    echo "END OF NAVBAR MATCH:\n" . substr($m[1], -50) . "\n\n";
}

// Find footer logo
if (preg_match('/(<img class="logo" style="height:54px"[^>]*>)/s', $content, $m2)) {
    echo "FOOTER MATCH:\n" . substr($m2[1], 0, 150) . " ...\n";
    echo "END OF FOOTER MATCH:\n" . substr($m2[1], -50) . "\n";
}
