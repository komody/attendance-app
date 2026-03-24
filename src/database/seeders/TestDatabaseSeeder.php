<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * テスト用シーダー（statuses と correction_statuses のみ）
 * RefreshDatabase の migrate:fresh --seed で使用
 */
class TestDatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            StatusesTableSeeder::class,
            CorrectionStatusesTableSeeder::class,
        ]);
    }
}
