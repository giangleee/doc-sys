<?php

use Illuminate\Database\Seeder;
use App\Models\Position;
use App\Helper\Constant;
use App\Models\Role;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Position::truncate();
        $positions = [
            '10' => '取締役',
            '11' => '監査役',
            '12' => '部長',
            '13' => '執行役員',
            '15' => '統括参与',
            '20' => '部長　1級',
            '25' => '支社長',
            '30' => '参与',
            '40' => '統括参事',
            '50' => '参事',
            '55' => '支配人',
            '56' => 'マネジャー',
            '60' => '主任',
            '63' => '店長',
            '65' => '主事',
            '66' => '主事（補）',
            '70' => '一般',
            '80' => '契約A',
            '84' => 'ｱﾙﾊﾞｲﾄ',
        ];
        foreach ($positions as $code => $position) {
            $item = [
                'name' => $position,
                'code' => $code,
            ];
            if (in_array($code, Constant::ADMIN_CODE)) {
                $item['role_id'] = Role::ADMIN;
            } else if (in_array($code, Constant::EXECUTIVE_CODE)) {
                $item['role_id'] = Role::EXECUTIVE;
            } else {
                $item['role_id'] = Role::STAFF;
            }
            Position::create($item);
        }
    }
}
