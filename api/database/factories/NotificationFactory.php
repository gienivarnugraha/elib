 <?php

  /** @var \Illuminate\Database\Eloquent\Factory $factory */

  use Faker\Generator as Faker;

  $factory->define(Illuminate\Notifications\DatabaseNotification::class, function (Faker $faker) {
    return [
      'id' => $faker->uuid,
      'type' => "App\Nofitications",
      'notifiable_type' => "Notifiable\User",
      'notifiable_id' => random_int(8888, 9999), // id of the notifiable model
      'data' => [
        'any' => "value"
      ]
    ];
  });
