<?php

use Illuminate\Database\Seeder;
use App\Models\Office;
use App\Helper\Constant;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Office::truncate();
//        $support = new Office();
//        $support->division_id = 1;
//        $support->name = 'Support';
//        $support->code = Constant::SUPPORT_OFFICE_CODE;
//        $support->email = Constant::SUPPORT_OFFICE_EMAIL;
//        $support->is_system = Office::IS_SYSTEM;
//        $support->save();

        $office = new Office();
        $office->division_id = 1;
        $office->name = 'Office Sample';
        $office->code = '00002';
        $office->email = 'office_sample@gmail.com';
        $office->is_system = 0;
        $office->save();
    }
}
