<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Branch;
use Faker\Generator as Faker;

$factory->define(Branch::class, function (Faker $faker) {
    return [
        'code' => $faker->unique()->randomNumber(4),
        'name' => $faker->name,
    ];
});
