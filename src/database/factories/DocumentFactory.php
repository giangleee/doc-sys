<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Document;
use App\Models\Office;
use App\Models\User;
use App\Models\DocumentType;
use App\Models\ServiceUser;
use App\Models\Folder;
use Faker\Generator as Faker;

$factory->define(Document::class, function (Faker $faker) {
    return [
        'office_id' => Office::all()->random()->id,
        'owner_id' => User::all()->random()->id,
        'folder_id' => Folder::all()->random()->id,
        'document_type_id' => DocumentType::all()->random()->id,
        'service_user_id' => ServiceUser::all()->random()->id,
        'name' => $faker->name,
    ];
});
