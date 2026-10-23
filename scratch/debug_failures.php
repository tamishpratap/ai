<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\AI\IraNavigationService::class);

echo "Test 2: \n";
print_r($service->resolveIntent('Add Fund section mein kya-kya available hai?', 'member', '/member/dashboard'));

echo "Test 27: \n";
print_r($service->resolveIntent('Funds Management ke baare mein batao', 'member', '/member/dashboard'));
