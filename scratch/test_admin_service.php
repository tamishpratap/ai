<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AI\IraNavigationService;
use App\Services\Gemini\GeminiService;

$gemini = new GeminiService();
$service = new IraNavigationService($gemini);

$adminQueries = [
    'Member Management mein kaun-kaun se pages hain?' => 'member_management',
    'Payment Management ke baare mein batao' => 'payment_management',
    'Funds Management section ke pages batao' => 'funds_management',
    'Staking Section ke pages batao' => 'staking_section',
    'Income Section ke pages batao' => 'income_section',
    'Support section mein kaun se pages hain?' => 'support',
    'Dashboard Messages kholo' => 'admin_dash_messages',
    'Popup Image par le chalo' => 'admin_popup_image',
    'Admin Dashboard' => 'admin_dashboard',
];

echo "--- RUNNING ADMIN PORTAL QUERIES ---" . PHP_EOL;
foreach ($adminQueries as $speech => $expected) {
    $res = $service->resolveIntent($speech, 'admin', '/admin/dashboard', 'chat', []);
    $dest = $res['destination_key'] ?? ($res['section_discovery']['section_key'] ?? 'NONE');
    $status = ($dest === $expected) ? 'PASS' : 'FAIL';
    echo "[$status] '$speech' -> Expected: $expected, Got: $dest" . PHP_EOL;
}
