<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Aircraft;
use Faker\Generator as Faker;

$factory->define(Aircraft::class, function (Faker $faker) {
  return [
    'id' => uniqid("id_"),
    'type' => $faker->randomElement(array('C212', 'CN235', 'AS365', 'CN295')),
    'serial_num' =>  strtoupper($faker->bothify('#??')),
    'reg_code' => strtoupper($faker->bothify('#???')),
    'effectivity' => $faker->randomNumber(5),
    'owner' => $faker->name,
    'manuf_date' => $faker->year,
  ];
});
