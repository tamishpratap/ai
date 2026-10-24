<?php

$file = __DIR__ . '/../resources/views/frontend/index.blade.php';
$content = file_get_contents($file);

preg_match_all('/<img[^>]+data:image\/png;base64,[^>]+>/s', $content, $matches);

echo "Found " . count($matches[0]) . " base64 img tags\n";

foreach ($matches[0] as $i => $tag) {
    if (strpos($tag, 'w-40') !== false) {
        $replacement = '<img src="{{ asset(\'logo/logo.png\') }}" alt="Demo" class="w-40 mx-auto mb-6" style="height: auto; object-fit: contain; filter:drop-shadow(0 0 30px rgba(43,232,107,.5))">';
        $content = str_replace($tag, $replacement, $content);
        echo "Replaced tag $i (About logo)\n";
    } elseif (strpos($tag, 'w-24') !== false) {
        $replacement = '<img src="{{ asset(\'logo/logo.png\') }}" alt="Demo" class="w-24 mx-auto mb-6" style="height: auto; object-fit: contain; filter:drop-shadow(0 0 26px rgba(43,232,107,.5))">';
        $content = str_replace($tag, $replacement, $content);
        echo "Replaced tag $i (Contact logo)\n";
    } else {
        echo "Unknown base64 tag: " . substr($tag, 0, 50) . " ... " . substr($tag, -50) . "\n";
    }
}

$remainingBase64 = preg_match_all('/data:image\/png;base64,/', $content);
echo "Remaining data:image/png;base64 occurrences in file: $remainingBase64\n";

file_put_contents($file, $content);
echo "Final file size: " . strlen($content) . " bytes\n";
