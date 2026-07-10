<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Seed the 9 joining packages from the compensation plan.
     *
     * Uses updateOrCreate() keyed by name so re-running this seeder never duplicates rows
     * or overwrites a package's historical amount if it was ever changed by mistake.
     */
    public function run(): void
    {
        $packages = [
            ['name' => 'Starter', 'amount' => 25, 'sort_order' => 1],
            ['name' => 'Basic', 'amount' => 50, 'sort_order' => 2],
            ['name' => 'Standard', 'amount' => 100, 'sort_order' => 3],
            ['name' => 'Silver', 'amount' => 250, 'sort_order' => 4],
            ['name' => 'Gold', 'amount' => 500, 'sort_order' => 5],
            ['name' => 'Platinum', 'amount' => 1000, 'sort_order' => 6],
            ['name' => 'Diamond', 'amount' => 2000, 'sort_order' => 7],
            ['name' => 'Elite', 'amount' => 5000, 'sort_order' => 8],
            ['name' => 'Crown', 'amount' => 10000, 'sort_order' => 9],
        ];

        foreach ($packages as $package) {
            Package::updateOrCreate(
                ['name' => $package['name']],
                [
                    'amount' => $package['amount'],
                    'sort_order' => $package['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
