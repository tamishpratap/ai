<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\IraNavigationController;
use App\Services\AI\IraNavigationService;
use Illuminate\Http\Request;

session()->put('MEMBER_ID', 1);
session()->put('name', 'John Doe');

$controller = app(IraNavigationController::class);
$navService = app(IraNavigationService::class);

echo "=== STEP 1: Query section pages ===\n";
$req1 = Request::create(
    '/ira/intent',
    'POST',
    [],
    [],
    [],
    ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
    json_encode([
        'speech' => 'Earning section mein kaun-kaun se pages hain?',
        'current_page' => '/member/dashboard',
        'context' => []
    ])
);
$req1->setLaravelSession(session()->driver());
$resp1 = $controller->resolve($req1, $navService);
$data1 = json_decode($resp1->getContent(), true);
echo "Intent: {$data1['intent']}\n";
echo "Speech: {$data1['speech']}\n";
echo "Pages found: " . implode(', ', $data1['section_discovery']['pages'] ?? []) . "\n\n";

echo "=== STEP 2: User chooses 'Direct Income' with context ===\n";
$req2 = Request::create(
    '/ira/intent',
    'POST',
    [],
    [],
    [],
    ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
    json_encode([
        'speech' => 'Direct Income',
        'current_page' => '/member/dashboard',
        'context' => $data1['context']
    ])
);
$req2->setLaravelSession(session()->driver());
$resp2 = $controller->resolve($req2, $navService);
$data2 = json_decode($resp2->getContent(), true);
echo "Intent: {$data2['intent']}\n";
echo "Target URL: {$data2['target_url']}\n";
echo "Speech: {$data2['speech']}\n\n";

echo "=== STEP 3: User chooses ordinal 'Pehla wala' with context ===\n";
$req3 = Request::create(
    '/ira/intent',
    'POST',
    [],
    [],
    [],
    ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
    json_encode([
        'speech' => 'Pehla wala',
        'current_page' => '/member/dashboard',
        'context' => $data1['context']
    ])
);
$req3->setLaravelSession(session()->driver());
$resp3 = $controller->resolve($req3, $navService);
$data3 = json_decode($resp3->getContent(), true);
echo "Intent: {$data3['intent']}\n";
echo "Target URL: {$data3['target_url']}\n";
echo "Speech: {$data3['speech']}\n";
