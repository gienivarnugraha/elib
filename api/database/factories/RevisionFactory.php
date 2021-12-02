<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Revision;
use Faker\Generator as Faker;

/*
      id
      document_id
      changes
      index
      */


$factory->define(Revision::class, function (Faker $faker) {
  return [
    'id' => uniqid("id_"),
    'changes' => $faker->catchPhrase,
    'index_date' => $faker->date,                               //rev date
    'is_closed' => $faker->boolean,
    'kpi_status' => $faker->boolean,
  ];
});
