<?php

namespace Tests\Feature;

use App\User;
use App\Order;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class OrderTest extends TestCase
{
  use WithFaker, WithoutMiddleware, DatabaseMigrations;
  /**
   * A basic feature test example.
   *
   * @return void
   */
  public function testIndexOrders()
  {
    $data = factory(\App\Order::class)->create([
      'manual_id' => factory(\App\manual::class)->create([
        'aircraft_id' => factory(\App\Aircraft::class)->create()->id
      ])->id,
      'user_id' => factory(\App\User::class)->create()->id,
    ]);
    $response = $this->get('/api/orders');

    $response->assertSee($data->id);

    $response->assertStatus(200);
  }
  /**
   * A basic feature test create.
   *
   * @return void
   */
  public function testCreateOrder()
  {
    $data = factory(\App\Order::class)->make([
      'manual_id' => factory(\App\manual::class)->create([
        'aircraft_id' => factory(\App\Aircraft::class)->create()->id
      ])->id,
      'user_id' => factory(\App\User::class)->create()->id,
    ]);

    $response = $this->json('post', '/api/orders/', $data->toArray());

    $data->makeHidden(['id']);

    $this->assertDatabaseHas('orders', $data->toArray());

    $response->assertStatus(200);
  }
  /**
   * A basic feature test delete.
   *
   * @return void
   */
  public function testDeleteOrder()
  {
    $data = factory(\App\Order::class)->create([
      'manual_id' => factory(\App\manual::class)->create([
        'aircraft_id' => factory(\App\Aircraft::class)->create()->id
      ])->id,
      'user_id' => factory(\App\User::class)->create()->id,
    ]);
    $dataArray = $data->toArray();
    $response = $this->json('delete', "/api/orders/{$data->id}");

    $response->assertStatus(200);

    $this->assertDatabaseHas('orders', ['deleted_at' => Carbon::now()]);
    $this->assertDatabaseHas('manuals', ['id' => $dataArray['manual_id']]);
    $this->assertDatabaseHas('users', ['id' => $dataArray['user_id']]);
  }
  /**
   * A basic feature test show.
   *
   * @return void
   */
  public function testShowOrder()
  {
    $data = factory(\App\Order::class)->create([
      'manual_id' => factory(\App\manual::class)->create([
        'aircraft_id' => factory(\App\Aircraft::class)->create()->id
      ])->id,
      'user_id' => factory(\App\User::class)->create()->id,
    ]);

    $response = $this->json('get', "/api/orders/{$data->id}");

    $response->assertSee($data->id);
    $response->assertStatus(200);
  }
  /**
   * A basic feature test update.
   *
   * @return void
   */
  public function testUpdateOrder()
  {
    $data = factory(\App\Order::class)->create([
      'manual_id' => factory(\App\manual::class)->create([
        'aircraft_id' => factory(\App\Aircraft::class)->create()->id
      ])->id,
      'user_id' => factory(\App\User::class)->create()->id,
    ]);
    $update = factory(\App\Order::class)->make([
      'manual_id' => $data->manual_id,
      'user_id' =>  factory(\App\User::class)->create()->id,
    ])->toArray();
    $response = $this->json('put', "/api/orders/{$data->id}", $update);

    $this->assertDatabaseHas('orders', [
      'user_id' => $update['user_id'],
    ]);
    $response->assertStatus(200);
  }
}
