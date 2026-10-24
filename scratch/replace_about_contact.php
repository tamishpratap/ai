<?php

$file = __DIR__ . '/../resources/views/frontend/index.blade.php';
$content = file_get_contents($file);

// Replace About section logo
$patternAbout = '/<img\s+src="data:image\/png;base64,[^"]+"\s+class="w-40 mx-auto mb-6"\s+style="filter:drop-shadow\(0 0 30px rgba\(43,232,107,\.5\)\)">/s';
$replaceAbout = '<img src="{{ asset(\'logo/logo.png\') }}" alt="Demo" class="w-40 mx-auto mb-6" style="height: auto; object-fit: contain; filter:drop-shadow(0 0 30px rgba(43,232,107,.5))">';

$countAbout = 0;
$content = preg_replace($patternAbout, $replaceAbout, $content, 1, $countAbout);
echo "Replaced About logo: $countAbout occurrence(s)\n";

// Replace Contact section logo
$patternContact = '/<img\s+src="data:image\/png;base64,[^"]+"\s+class="w-24 mx-auto mb-6"\s+style="filter:drop-shadow\(0 0 26px rgba\(43,232,107,\.5\)\)">/s';
$replaceContact = '<img src="{{ asset(\'logo/logo.png\') }}" alt="Demo" class="w-24 mx-auto mb-6" style="height: auto; object-fit: contain; filter:drop-shadow(0 0 26px rgba(43,232,107,.5))">';

$countContact = 0;
$content = preg_replace($patternContact, $replaceContact, $content, 1, $countContact);
echo "Replaced Contact logo: $countContact occurrence(s)\n";

$remainingBase64 = preg_match_all('/data:image\/png;base64,/', $content);
echo "Remaining data:image/png;base64 occurrences in file: $remainingBase64\n";

file_put_contents($file, $content);
echo "Updated file size: " . strlen($content) . " bytes\n";
