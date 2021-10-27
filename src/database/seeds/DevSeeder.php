<?php

use Illuminate\Database\Seeder;

class DevSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $this->call([
            RoleSeeder::class,
            //  BranchSeeder::class,
            //  DivisionSeeder::class,
            //  OfficeSeeder::class,
            PositionSeeder::class,
            OfficeProductionSeeder::class,
            UserSeeder::class,
//             ServiceUserSeeder::class,
            DocumentTypeSeeder::class,
//             FolderSeeder::class,
            AttributeSeeder::class,
            MailTemplateSeeder::class,
            DocumentObjectSeeder::class,
            DocumentTypeObjectSeeder::class,
            GeneralSettingSeeder::class
         ]);
    }
}
