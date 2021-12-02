<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Manual;
use Faker\Generator as Faker;

$factory->define(Manual::class, function (Faker $faker) {
  $id = uniqid("id_");
  return [
    'id' => $id,
    'type' => $faker->randomElement(array('WDM', 'IPC', 'CMM')),
    'aircraft_id' => null,
    'part_num' =>  strtoupper($faker->bothify('#??-#?-??-???')),
    'lib_call' =>  strtoupper($faker->bothify('#??#??????')),
    'subject' => $faker->catchPhrase,
    'volume' => $faker->randomNumber(4),
    'vendor' => $faker->name,
    'caplist' =>  $faker->boolean,
    'collector' => $faker->randomElement(array('AMO1', 'AMO2', 'AMO3')),
  ];
});

/* Received Date
* Title/Description
* Part No
Manual No
* Revision Status (Rev & Date)
* Volume
* Manufacture
* Library Call No
* Caplist/Non Caplist
* Scan/Pdf
Collector (AMO 1, AMO 2, AMO 3, AMO 4) */
