<?php

use Illuminate\Database\Seeder;
use App\Models\GeneralSetting;

class GeneralSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        GeneralSetting::truncate();
        GeneralSetting::create([
            'created_at' => now()
        ]);
    }
}
