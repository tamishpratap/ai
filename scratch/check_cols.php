<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = Illuminate\Support\Facades\Schema::getColumnListing('member_details');
echo "Columns in member_details: " . implode(', ', $cols) . "\n";
$members = Illuminate\Support\Facades\DB::table('member_details')->take(5)->get();
echo "\nSample rows in member_details:\n";
foreach ($members as $m) {
    echo "ID: {$m->id}, Member ID: " . ($m->member_id ?? $m->memberid ?? 'N/A') . ", Name: {$m->name}\n";
}
