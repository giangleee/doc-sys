<?php

use Illuminate\Database\Seeder;

class DevPhase2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AttributePhase2Seeder::class,
            DocumentObjectSeeder::class,
            DocumenTypePhase2Seeder::class,
            DocumentTypeObjectSeeder::class,
            MailTemplateSeeder::class
        ]);
    }
}
