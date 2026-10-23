<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$navService = app(App\Services\AI\IraNavigationService::class);

$refMethod = new ReflectionMethod($navService, 'normalizeUtterance');
$refMethod->setAccessible(true);

$input = "Earning section mein kaun-kaun se pages hain?";
$normalized = $refMethod->invoke($navService, $input);
echo "Input: $input\n";
echo "Normalized: '$normalized'\n";

foreach (['earnings', 'income', 'income section', 'earning section', 'kamai'] as $alias) {
    $matched = str_contains($normalized, strtolower($alias)) ? 'YES' : 'NO';
    echo "  Alias '$alias': $matched\n";
}
