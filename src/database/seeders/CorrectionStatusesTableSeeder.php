<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CorrectionStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $correctionStatuses = [
            ['name' => '承認待ち', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '承認済み', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('correction_statuses')->insert($correctionStatuses);
    }
}
