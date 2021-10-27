<?php

use Illuminate\Database\Seeder;

class RefreshDatabaseDevSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            OfficePhase3Seeder::class,
            RoleSeeder::class,
            PositionSeeder::class,
            UserPhase3Seeder::class,
            StaffSeeder::class,
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
