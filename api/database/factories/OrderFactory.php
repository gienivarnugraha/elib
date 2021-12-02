<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Order;
use Faker\Generator as Faker;

$factory->define(Order::class, function (Faker $faker) {
  $send_date = $faker->date;
  $return_date = $faker->randomElement(array(null, $faker->date));

  if ($return_date != null) {
    $is_closed = 1;
  } else {
    $is_closed = 0;
  }

  return [
    'id' => uniqid("id_"),
    'manual_id' => App\manual::all()->random()->id,
    'user_id' => App\User::all()->random()->id,
    'send_date' => $send_date,
    'return_date' => $return_date,
    'is_closed' => $is_closed
  ];
});
