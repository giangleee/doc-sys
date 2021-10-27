<?php

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
            RoleSeeder::class,
            PositionSeeder::class,
            OfficeProductionSeeder::class,
            UserProductionSeeder::class,
            MailTemplateSeeder::class,
            DocumentObjectSeeder::class,
            DocumentTypeObjectSeeder::class,
            GeneralSettingSeeder::class
        ]);
    }
}
