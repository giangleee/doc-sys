<?php

use Illuminate\Database\Seeder;
use App\Models\Division;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Division::truncate();
        $division = new Division();
        $division->branch_id = 1;
        $division->name = 'Division Sample';
        $division->code = '00001';
        $division->save();
    }
}
