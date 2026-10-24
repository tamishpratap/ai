<?php
$files = [
    'logo.png',
    'favicon.png',
    'favicon.ico',
    'favicon-16x16.png',
    'favicon-32x32.png',
    'favicon-48x48.png',
    'favicon-96x96.png',
    'apple-touch-icon.png',
    'web-app-manifest-192.png',
    'web-app-manifest-512.png',
];

$dir = dirname(__DIR__) . '/docs/Demo_Brand_Assets';
foreach ($files as $f) {
    $path = $dir . '/' . $f;
    if (file_exists($path)) {
        $info = @getimagesize($path);
        $w = $info[0] ?? '?';
        $h = $info[1] ?? '?';
        $mime = $info['mime'] ?? 'unknown';
        echo "$f: size=" . filesize($path) . " bytes, dims={$w}x{$h}, mime=$mime\n";
    } else {
        echo "MISSING: $f\n";
    }
}
