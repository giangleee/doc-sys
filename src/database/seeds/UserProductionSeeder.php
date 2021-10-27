<?php

use App\Models\Branch;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();
        $userAdmin = new User();
        $userAdmin->role_id = Role::where('code', Role::SYSTEM_ADMIN)->first()->id;
        $userAdmin->position_id = Position::where('code', '10')->first()->id;
        $userAdmin->branch_id = Branch::where('code', '04')->first()->id;
        $userAdmin->name = "systemadmin";
        $userAdmin->employee_id = "000000";
        $userAdmin->email = "katsumi.takahashi@yasashiite.com";
        $userAdmin->password = 'yst000000';
        $userAdmin->is_first_login = 0;
        $userAdmin->status = 1;
        $userAdmin->save();

        $profile = new \App\Models\Profile();
        $profile['user_id'] = $userAdmin->id;
        $profile->save();

    }
}
