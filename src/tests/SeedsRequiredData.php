<?php

namespace Tests;

use Database\Seeders\TestDatabaseSeeder;

/**
 * RefreshDatabase 使用時に migrate:fresh --seed で statuses と correction_statuses を自動投入するトレイト
 */
trait SeedsRequiredData
{
    protected $seed = true;

    protected $seeder = TestDatabaseSeeder::class;
}
