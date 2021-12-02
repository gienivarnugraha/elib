<?php

namespace Tests\Feature;

use App\manual;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class ManualTest extends TestCase
{
  use WithFaker, WithoutMiddleware, DatabaseMigrations;
  /**
   * A basic feature test example.
   *
   * @return void
   */
  public function testIndexmanuals()
  {
    $data = factory(\App\Manual::class)->create([
      'aircraft_id' => factory(\App\Aircraft::class)->create()->id,
    ]);
    $response = $this->get('/api/manuals');

    $response->assertSee(Manual::find(1));

    $response->assertStatus(200);
  }
  /**
   * A basic feature test create.
   *
   * @return void
   */
  public function testCreatemanual()
  {
    $data = factory(\App\Manual::class)->make([
      'aircraft_id' => factory(\App\Aircraft::class)->create()->id,
    ]);

    $response = $this->json('post', '/api/manuals/', $data->toArray());
    $data->makeHidden('id');
    $this->assertDatabaseHas('manuals', $data->toArray());

    $response->assertStatus(200);
  }
  /**
   * A basic feature test delete.
   *
   * @return void
   */
  public function testDeletemanual()
  {
    $data = factory(\App\Manual::class)->create([
      'aircraft_id' => factory(\App\Aircraft::class)->create()->id,
    ]);

    $response = $this->json('delete', "/api/manuals/{$data->id}");

    $response->assertStatus(200);
  }
  /**
   * A basic feature test show.
   *
   * @return void
   */
  public function testShowmanual()
  {
    $data = factory(\App\Manual::class)->create([
      'aircraft_id' => factory(\App\Aircraft::class)->create()->id,
    ]);
    factory(\App\Order::class)->create([
      'manual_id' => $data->id,
      'user_id' => factory(\App\User::class)->create()->id,
    ]);

    $response = $this->json('get', "/api/manuals/{$data->id}");

    $response->assertSee($data->id);
    $response->assertStatus(200);
  }
  /**
   * A basic feature test update.
   *
   * @return void
   */
  public function testUpdatemanual()
  {
    $data = factory(\App\Manual::class)->create([
      'aircraft_id' => factory(\App\Aircraft::class)->create()->id,
    ]);
    $update = factory(\App\Manual::class)->make([
      'id' => $data->id,
      'aircraft_id' => $data->aircraft_id,
      'index' => 'XZC'
    ])->toArray();
    $response = $this->json('put', "/api/manuals/{$data->id}", $update);
    $this->assertDatabaseHas('manuals', ['index' => 'XZC']);
    $response->assertStatus(200);
  }
}
