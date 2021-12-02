<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
  return [
    'id' => uniqid("id_"),
    'name' => $faker->name,
    'nik' => $faker->numerify('######'),
    'org_code' => $faker->numerify('MS####'),
    'dept' => $faker->jobTitle,
    'email' => $faker->unique()->firstName . '@indonesian-aerospace.com',
    'is_admin' => $faker->boolean,
    'last_login' =>    $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i:s'),
    'password' => Hash::make('password'), // password
    'remember_token' => Str::random(10),
  ];
});
