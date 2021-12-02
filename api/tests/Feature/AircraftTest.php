<?php

namespace Tests\Feature;

use App\Aircraft;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AircraftTest extends TestCase
{
  use WithFaker, WithoutMiddleware, DatabaseMigrations;
  /**
   * A basic feature test example.
   *
   * @return void
   */
  public function testIndexAircrafts()
  {
    $response = $this->get('/api/aircrafts');

    $response->assertSee(Aircraft::find(1));

    $response->assertStatus(200);
  }
  /**
   * A basic feature test create.
   *
   * @return void
   */
  public function testCreateAircraft()
  {
    $data = factory(\App\Aircraft::class)->make();

    $response = $this->json('post', '/api/aircrafts/', $data->toArray());

    $data->makeHidden(['id']);

    $this->assertDatabaseHas('aircraft', $data->toArray());

    $response->assertStatus(200);
  }
  /**
   * A basic feature test delete.
   *
   * @return void
   */
  public function testDeleteAircraft()
  {
    $data = factory(\App\Aircraft::class)->create();

    $response = $this->json('delete', "/api/aircrafts/{$data->id}");

    $response->assertStatus(200);
    $this->assertDatabaseHas('aircraft', ['deleted_at' => Carbon::now()]);
  }
  /**
   * A basic feature test show.
   *
   * @return void
   */
  public function testShowAircraft()
  {
    $data = factory(\App\Aircraft::class)->create();

    $response = $this->json('get', "/api/aircrafts/{$data->id}");

    $response->assertSee($data->id);
    $response->assertStatus(200);
  }
  /**
   * A basic feature test update.
   *
   * @return void
   */
  public function testUpdateAircraft()
  {
    $data = factory(\App\Aircraft::class)->create();
    $update = factory(\App\Aircraft::class)->make([
      'reg_code' => 'A9118'
    ])->toArray();
    $response = $this->json('put', "/api/aircrafts/{$data->id}", $update);

    $this->assertDatabaseHas('aircraft', ['reg_code' => 'A9118']);
    $response->assertStatus(200);
  }
}
