<?php

use Illuminate\Database\Seeder;

class RefreshDatabaseProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            OfficeProductionSeeder::class,
            RoleSeeder::class,
            PositionSeeder::class,
            UserProductionSeeder::class,
            DocumentTypeSeeder::class,
            DocumenTypePhase2Seeder::class,
            DocumentObjectSeeder::class,
            DocumentTypeObjectSeeder::class,
            AttributeSeeder::class,
            AttributePhase2Seeder::class,
            MailTemplateSeeder::class,
            GeneralSettingSeeder::class,
        ]);
    }
}
