<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ServiceUser;
use Faker\Generator as Faker;

$factory->define(ServiceUser::class, function (Faker $faker) {
    return [
        'code' => $faker->unique()->randomNumber(6),
        'name' => $faker->firstName,
        'office_id' => \App\Models\Office::all()->random()->id,
        'user_created' => \App\Models\User::all()->random()->id,
    ];
});
