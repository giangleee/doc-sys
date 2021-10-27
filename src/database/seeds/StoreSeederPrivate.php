<?php

use Illuminate\Database\Seeder;

class StoreSeederPrivate extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        // create store
//        $store = new \App\Models\Store();
//        $store->office_id = 1;
//        $store->name = 'Store Sample';
//        $store->code = '00001';
//        $store->email = 'sample@gmail.com';
//        $store->is_system = 1;
//        $store->save();
//
//        // create folder store
//        $parent = \App\Models\Folder::where([
//            'office_id' => 1,
//            'is_system' => 1,
//            'service_user_id' => null
//        ])->first();
//
//        $folder = new \App\Models\Folder();
//        $folder->owner_id = 1;
//        $folder->store_id = 1;
//        $folder->parent_id = $parent->id;
//        $folder->name = 'Store Sample';
//        $folder->is_system = 1;
//        $folder->save();
//
//        // dump data staff
//        $user = \App\Models\User::where('employee_id', 999900)->first();
//        $user->role_id = 4;
//        $user->branch_id = 1;
//        $user->division_id = 1;
//        $user->office_id = 1;
//        $user->store_id = 1;
//        $user->position_id = 15;
//        $user->save();

//        for ($i = 1; $i <= 12; $i++) {
//            $store = new \App\Models\Store();
//            $store->office_id = $i;
//            $store->name = 'Example Store ' . $i;
//            $store->code = '0000' . $i;
//            $store->email = 'example' . $i .'@gmail.com';
//            $store->is_system = 1;
//            $store->save();
//
//            $folderParent1 = \App\Models\Folder::where([
//                'office_id' => $i,
//                'is_system' => 1,
//                'service_user_id' => null
//            ])->first();
//            if ($folderParent1) {
//                $folderStore1 = new \App\Models\Folder();
//                $folderStore1->name = $store->name;
//                $folderStore1->owner_id = 1;
//                $folderStore1->parent_id = $folderParent1->id;
//                $folderStore1->store_id = $store->id;
//                $folderStore1->is_system = 1;
//                $folderStore1->save();
//            }
//
//            $store2 = new \App\Models\Store();
//            $store2->office_id = $i;
//            $store2->name = 'Store ' . $i;
//            $store2->code = '00000' . $i;
//            $store2->email = 'sample' . $i .'@gmail.com';
//            $store2->is_system = 1;
//            $store2->save();
//
//            if ($folderParent1) {
//                $folderStore2 = new \App\Models\Folder();
//                $folderStore2->name = $store2->name;
//                $folderStore2->owner_id = 1;
//                $folderStore2->parent_id = $folderParent1->id;
//                $folderStore2->store_id = $store2->id;
//                $folderStore2->is_system = 1;
//                $folderStore2->save();
//            }
//        }

        $folderBranch = new \App\Models\Folder();
        $folderBranch->name = '管理本部';
        $folderBranch->owner_id = 1;
        $folderBranch->branch_id = 1;
        $folderBranch->is_system = 1;
        $folderBranch->save();

        $folderDivision = new \App\Models\Folder();
        $folderDivision->name = '総合サポート部';
        $folderDivision->owner_id = 1;
        $folderDivision->parent_id = 1;
        $folderDivision->division_id = 1;
        $folderDivision->is_system = 1;
        $folderDivision->save();

        $folderOffice = new \App\Models\Folder();
        $folderOffice->name = '総合サポート課';
        $folderOffice->owner_id = 1;
        $folderOffice->parent_id = 2;
        $folderOffice->office_id = 1;
        $folderOffice->is_system = 1;
        $folderOffice->save();

        $folderStore = new \App\Models\Folder();
        $folderStore->name = '総合サポート';
        $folderStore->owner_id = 1;
        $folderStore->parent_id = 3;
        $folderStore->store_id = 1;
        $folderStore->is_system = 1;
        $folderStore->save();
    }
}
