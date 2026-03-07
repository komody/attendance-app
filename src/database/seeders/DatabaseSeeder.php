<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            StatusesTableSeeder::class,
            UsersTableSeeder::class,
            AdminsTableSeeder::class,
            CorrectionStatusesTableSeeder::class,
            AttendancesTableSeeder::class,
            BreaksTableSeeder::class,
        ]);
    }
}
