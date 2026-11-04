<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach (json_decode(file_get_contents(__DIR__.'/portfolio.json'), true) as $table => $rows) {
                if (DB::table($table)->exists()) {
                    continue;
                }
                foreach ($rows as $order => $row) {
                    DB::table($table)->insert($row + ['sort_order' => $order, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        });
    }
}
