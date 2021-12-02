<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Document;
use Faker\Generator as Faker;


$factory->define(Document::class, function (Faker $faker) {
  $office = $faker->randomElement(array('DOA', 'AMO'));
  $aircraft = $faker->randomElement(array('235', '212', '332', '412'));
  $type = $faker->randomElement(array('EO', 'TD', 'JE', 'ES'));
  $year = $faker->numberBetween('10', '25');
  $number = str_pad($faker->randomDigitNotNull(), 3, "0", STR_PAD_LEFT);

  if ($type == 'JE') {
    $no = "JE/"  .  $number . "/MS1000/" . $faker->month . "/" . $faker->year;
  } else if ($office == 'DOA') {
    $no = $aircraft . ".AS."  .  $type . "." . $year . "." . $number;
  } else {
    $no = $type . "-"  .  $aircraft . "-" . $faker->year . "-" . $number;
  }
  /*
id
no
office
type
subject
reference
aircraft_id
assignee_id
is_closed
kpi_status
 */


  // JE/018/MS1000/05/19
  return [
    //'id' => uniqid("id_"),
    'no' => $no,
    'office' => $office,
    'type' => $type,
    'subject' => $faker->catchPhrase,
    'reference' => strtoupper($faker->bothify('#??/#??#/###')),
    'assignee_id'  => App\User::all()->random()->id,
    'aircraft_id' =>  App\Aircraft::all()->random()->id,
  ];
});
