<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FinanceReportSampleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(BismillahMacroSalesSeeder::class);
    }
}
