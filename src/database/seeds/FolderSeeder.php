<?php

use Illuminate\Database\Seeder;
use App\Models\Folder;
use App\Models\Document;

class FolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Folder::truncate();
        Document::truncate();
        $faker = Faker\Factory::create();

        $folder = new Folder;
        $folder->owner_id = 1;
        $folder->name = '企業間取引';
        $folder->save();

        $folder = new Folder;
        $folder->owner_id = 2;
        $folder->name = '訪問介護';
        $folder->save();

        factory(Folder::class, 20)->create();
        factory(Document::class, 20)->create();

    }
}
