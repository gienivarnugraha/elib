<?php

namespace Tests\Feature;

use App\Notifications\OrderNotification;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
  use WithoutMiddleware, DatabaseMigrations, WithFaker;
  /**
   * A basic feature test example.
   *
   * @return void
   */
  public function testNotification()
  {
    Notification::fake();

    factory(\App\User::class, 5)->create();

    $data = factory(\App\Order::class)->make([
      'manual_id' => factory(\App\manual::class)->create([
        'aircraft_id' => factory(\App\Aircraft::class)->create()->id
      ])->id,
      'user_id' => User::all()->random()->id,
    ]);
    $this->json('post', '/api/orders/', $data->toArray());


    Notification::assertSentTo(
      [User::where('is_admin', true)->get()],
      OrderNotification::class
    );

    Notification::assertNotSentTo(
      [User::where('is_admin', false)->get()],
      OrderNotification::class
    );
  }
}
