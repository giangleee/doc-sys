<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Office;
use Faker\Generator as Faker;

$factory->define(Office::class, function (Faker $faker) {
    return [
        'division_id' => \App\Models\Division::all()->random()->id,
        'code' => $faker->unique()->randomNumber(8),
        'name' => $faker->name,
        'email' => $faker->email,
    ];
});
