<?php

use App\Models\Branch;
use App\Models\Division;
use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Branch
        Branch::truncate();
        $branch = new Branch();
        $branch->name = '管理本部';
        $branch->code = '04';
        $branch->save();
    }
}
