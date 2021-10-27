<?php

use Illuminate\Database\Seeder;
use App\Models\DocumentObject;
use App\Helper\Constant;
use Carbon\Carbon;

class DocumentObjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DocumentObject::truncate();
        $documentObjects = Constant::DOCUMENT_OBJECTS;
        $dataSave = [] ;
        foreach ($documentObjects as $code => $documentObject) {
            $dataSave[] = [
                'code' => $code,
                'name' => $documentObject,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }
        DocumentObject::insert($dataSave);

    }
}
