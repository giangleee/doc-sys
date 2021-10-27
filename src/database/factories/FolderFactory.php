<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use App\Models\Folder;
use Faker\Generator as Faker;

$factory->define(Folder::class, function (Faker $faker) {
    return [
        'owner_id' => User::all()->random()->id,
        'parent_id' => Folder::all()->random()->id,
        'name' => $faker->name,
    ];
});
