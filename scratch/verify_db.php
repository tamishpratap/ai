<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = [
    'member_details',
    'admin_details',
    'staking_details',
    'withdrawal_requests',
    'p2p_history',
    'level_transactions',
    'binary_transactions',
    'daily_transactions'
];

echo "Database verification:\n";
foreach ($tables as $t) {
    if (Illuminate\Support\Facades\Schema::hasTable($t)) {
        $count = Illuminate\Support\Facades\DB::table($t)->count();
        echo "Table: $t => Count: $count\n";
    }
}

// Check sample member IDs
$members = Illuminate\Support\Facades\DB::table('member_details')->take(5)->pluck('member_id');
echo "\nSample Member IDs:\n";
foreach ($members as $id) {
    echo " - $id\n";
}
