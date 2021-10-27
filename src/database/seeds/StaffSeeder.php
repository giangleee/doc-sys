<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Position;
use App\Models\Branch;
use App\Models\Division;
use App\Models\Office;
use App\Models\Store;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $staff = User::where('employee_id', '999900')->first();

        if (empty($staff)) {
            $staff = new User();
            $staff->role_id = Role::where('code', Role::STAFF)->first()->id;
            $staff->position_id = Position::where('code', '65')->first()->id;
            $staff->branch_id = Branch::where('code', '04')->first()->id;
            $staff->division_id = Division::where('code', '0310')->first()->id;
            $staff->office_id = Office::where('code', '031010')->first()->id;
            $staff->store_id = Store::where('code', '03101010')->first()->id;
            $staff->name = "SSO認証";
            $staff->employee_id = "999900";
            $staff->email = "shigenobu.koufugata@gmail.com";
            $staff->is_first_login = 0;
            $staff->status = 1;
            $staff->save();

            $profileStaff = new \App\Models\Profile();
            $profileStaff['user_id'] = $staff->id;
            $profileStaff->save();
        }
    }
}
