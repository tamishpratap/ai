<?php

$file = __DIR__ . '/../resources/views/frontend/index.blade.php';
$content = file_get_contents($file);

preg_match('/(<img[^>]*data:image\/png;base64,[^>]*>)/s', $content, $m0);
if ($m0) {
    echo "First img tag:\n";
    echo substr($m0[1], 0, 80) . " ... " . substr($m0[1], -80) . "\n\n";
}

preg_match_all('/(<img[^>]*data:image\/png;base64,[^>]*>)/s', $content, $mAll);
echo "Total img tags with base64: " . count($mAll[0]) . "\n";
foreach ($mAll[0] as $idx => $tag) {
    echo "Tag $idx start: " . substr($tag, 0, 80) . "\n";
    echo "Tag $idx end: " . substr($tag, -80) . "\n";
}
