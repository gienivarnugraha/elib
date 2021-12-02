<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   *
   * @return void
   */
  public function run()
  {

    factory(App\User::class, 5)->create();

    factory(App\Aircraft::class, 10)->create()->each(function ($aircraft) {
      factory(App\Manual::class, 2)->create([
        'aircraft_id' => $aircraft->id,
      ])->each(function ($manual) {
        $manual->revision()->save(factory(App\Revision::class)->make());
        $manual->revision->each(function ($revision) {
          factory(App\File::class)->create([
            'revision_id' => $revision->id
          ]);
        });
      });
    });
    factory(App\Document::class, 50)->create()->each(function ($document) {
      $document->revision()->save(factory(App\Revision::class)->make());
      $document->revision->each(function ($revision) {
        factory(App\File::class)->create([
          'revision_id' => $revision->id
        ]);
      });
    });
    factory(App\Order::class, 5)->create();
  }
}
