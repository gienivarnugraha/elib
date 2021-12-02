<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\File;
use Faker\Generator as Faker;

/*
id
type
revision_id
filename
  */

$factory->define(File::class, function (Faker $faker) {
  return [
    //'id' => uniqid("id_"),
    'type' => $faker->randomElement(array('document', 'manual')),
    'filename' => $faker->bothify('#??/#??#.###'),
  ];
});
