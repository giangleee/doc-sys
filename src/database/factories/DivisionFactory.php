<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Branch;
use Faker\Generator as Faker;
use App\Models\Division;

$factory->define(Division::class, function (Faker $faker) {
    return [
        'branch_id' => Branch::all()->random()->id,
        'code' => $faker->unique()->randomNumber(6),
        'name' => $faker->name,
    ];
});
