<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Profile;
use App\Models\Office;
use App\Models\Position;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();
        $faker = Faker\Factory::create();

        // superadmin
        $superadmin = new User;
        $roleSystemAdminId = Role::where('code', Role::SYSTEM_ADMIN)->first()->id;
        $roleId = Role::where('code', Role::ADMIN)->first()->id;
        $superadmin->role_id = $roleSystemAdminId;
        $superadmin->position_id = Position::where('role_id', $roleId)->get()->random()->id;
        $superadmin->office_id = Office::all()->random()->id;
        $superadmin->name = "system admin";
        $superadmin->employee_id = "000000";
        $superadmin->email = "systemadmin@gmail.com";
        $superadmin->password = 'systemadmin@123';
        $superadmin->save();

        // admin
        $roleId = Role::where('code', Role::ADMIN)->first()->id;
        $admin = new User;
        $admin->role_id = $roleId;
        $admin->position_id = Position::where('role_id', $roleId)->get()->random()->id;
        $admin->office_id = Office::all()->random()->id;
        $admin->name = "admin";
        $admin->employee_id = "000002";
        $admin->email = "admin@gmail.com";
        $admin->password = 'admin@123';
        $admin->save();

        // executive
        $roleId = Role::where('code', Role::EXECUTIVE)->first()->id;
        $executive = new User;
        $executive->role_id = $roleId;
        $executive->position_id = Position::where('role_id', $roleId)->get()->random()->id;
        $executive->office_id = Office::all()->random()->id;
        $executive->name = "executive";
        $executive->employee_id = "000003";
        $executive->email = "executive@gmail.com";
        $executive->password = 'executive@123';
        $executive->save();

        // staff
        $roleId = Role::where('code', Role::STAFF)->first()->id;
        $staff = new User;
        $staff->role_id = $roleId;
        $staff->position_id = Position::where('role_id', $roleId)->get()->random()->id;
        $staff->office_id = Office::all()->random()->id;
        $staff->name = "staff";
        $staff->employee_id = "000004";
        $staff->email = "staff@gmail.com";
        $staff->password = 'staff@123';
        $staff->save();

        $userIds = User::all()->pluck('id')->toArray();
        Profile::truncate();
        foreach ($userIds as $id) {
            $profile = [
                'user_id' => $id,
                'full_name' => $faker->firstName,
                'katakana_name' => $faker->lastName,
                'phone' => '0123456789'
            ];
            Profile::create($profile);
        }
    }
}
