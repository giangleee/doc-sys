<?php

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Branch::truncate();
        $branch = new Branch();
        $branch->name = 'Branch Sample';
        $branch->code = '00001';
        $branch->save();
    }
}
