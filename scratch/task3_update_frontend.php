<?php

$file = __DIR__ . '/../resources/views/frontend/index.blade.php';
$content = file_get_contents($file);

// Replace navbar logo
$patternNav = '/<a href="#home" class="flex items-center shrink-0"><img class="logo"\s+src="data:image\/png;base64,[^"]+"\s+alt="Demo">\s*<\/a>/s';
$replaceNav = '<a href="#home" class="flex items-center shrink-0"><img class="logo" src="{{ asset(\'logo/logo.png\') }}" alt="Demo" style="height: auto; max-height: 48px; object-fit: contain;"></a>';

$countNav = 0;
$content = preg_replace($patternNav, $replaceNav, $content, 1, $countNav);
echo "Replaced navbar logo: $countNav occurrence(s)\n";

// Replace Footer logo
$patternFooter = '/<img class="logo" style="height:54px"\s+src="data:image\/png;base64,[^"]+"\s+alt="Demo">/s';
$replaceFooter = '<img class="logo" style="height: 54px; width: auto; object-fit: contain;" src="{{ asset(\'logo/logo.png\') }}" alt="Demo">';

$countFooter = 0;
$content = preg_replace($patternFooter, $replaceFooter, $content, 1, $countFooter);
echo "Replaced Footer logo: $countFooter occurrence(s)\n";

$remainingBase64 = preg_match_all('/data:image\/png;base64,/', $content);
echo "Remaining data:image/png;base64 occurrences in file: $remainingBase64\n";

file_put_contents($file, $content);
echo "Updated file size: " . strlen($content) . " bytes\n";
