<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AI\AppNavigationOntology;

$discrepancies = AppNavigationOntology::validateConsistency();

if (empty($discrepancies)) {
    echo "SUCCESS: All " . count(AppNavigationOntology::getAllDestinations()) . " canonical destinations matched registered Laravel routes!" . PHP_EOL;
} else {
    echo "DISCREPANCIES FOUND:" . PHP_EOL;
    print_r($discrepancies);
}
