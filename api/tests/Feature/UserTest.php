<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class UserTest extends TestCase
{
  use WithFaker, RefreshDatabase, WithoutMiddleware;
  /**
   * A basic feature test example.
   *
   * @return void
   */
  public function testIndexUsers()
  {
    $response = $this->get('/api/users');

    $response->assertSee(User::find(1));

    $response->assertStatus(200);
  }

  /**
   * A basic feature test delete.
   *
   * @return void
   */
  public function testDeleteUser()
  {
    $data = factory(\App\User::class)->create();

    $response = $this->json('delete', "/api/users/{$data->id}");

    $response->assertStatus(200);

    $this->assertDatabaseHas('users', ['deleted_at' => Carbon::now()]);
  }
  /**
   * A basic feature test show.
   *
   * @return void
   */
  public function testShowUser()
  {
    $data = factory(\App\User::class)->create();

    $response = $this->json('get', "/api/users/{$data->id}");

    $response->assertSee($data->id);

    $response->assertStatus(200);
  }
  /**
   * A basic feature test update.
   *
   * @return void
   */
  public function testUpdateUser()
  {
    $data = factory(\App\User::class)->create();

    $update = factory(\App\User::class)->make([
      'name' => 'test',
      'password_confirmation' => function (array $user) {
        return $user['password'];
      }
    ]);
    $update->makeVisible('password');

    $response = $this->json('put', "/api/users/{$data->id}", $update->toArray());

    $this->assertDatabaseHas('users', ['name' => 'test']);

    $response->assertStatus(200);
  }
}
