<?php

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::truncate();
        $roles = ['1' => 'システム管理者', '2' => '管理者', '3' => '役職者', '4' => '一般従業員'];
        foreach ($roles as $code => $role) {
            $item = [
                'name' => $role,
                'code' => $code,
                'description' => ''
            ];
            Role::create($item);
        }
    }
}
