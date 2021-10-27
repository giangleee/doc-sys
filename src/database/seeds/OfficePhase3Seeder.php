<?php

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Division;
use App\Models\Office;
use App\Models\Store;

class OfficePhase3Seeder extends Seeder
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
        //Division
        Division::truncate();
        $division = new Division();
        $division->branch_id = $branch->id;
        $division->name = '総合サポート部';
        $division->code = '0310';
        $division->save();
        //Office
        Office::truncate();
        $office = new Office();
        $office->division_id = $division->id;
        $office->name = '総合サポート課';
        $office->code = '031010';
        $office->email = 'support@yasashiite.com';
        $office->is_system = 1;
        $office->save();
        //Store
        Store::truncate();
        $store = new Store();
        $store->office_id = $office->id;
        $store->name = '総合サポート';
        $store->code = '03101010';
        $store->email = 'store_support@yasashiite.com';
        $store->is_system = 1;
        $store->save();
    }
}
