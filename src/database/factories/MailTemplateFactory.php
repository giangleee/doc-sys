<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\MailTemplate;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(MailTemplate::class, function (Faker $faker) {
    return [
        'code' => $faker->unique()->randomNumber(6),
        'name' => $faker->firstName,
        'subject' => $faker->text(5),
        'body' => $faker->text(15),
        'user_created' => User::all()->random()->id
    ];
});
