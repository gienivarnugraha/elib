<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AuthTest extends TestCase
{
  use DatabaseMigrations;

  public function testRegister()
  {
    $actingAs = Sanctum::actingAs(
      factory(User::class)->create(),
      ['*']
    );
    //Create user
    $users = factory(User::class)->make([
      'password_confirmation' => function (array $user) {
        return $user['password'];
      }
    ]);
    //make password visible for test purposes
    $users->makeVisible('password');
    //attempt register
    $response = $this->postJson(route('api.register'), $users->toArray());
    //Assert it was successful
    $response->assertStatus(200);
    //Assert database has users created
    $this->assertDatabaseHas('users', ['name' => $users->name]);
    //Assert we received a token
    $this->assertArrayHasKey('token', $response->json());
  }
  /**
   * @test
   * Test login
   */
  public function testLogin()
  {
    //Create user
    $user = factory(\App\User::class)->create();
    //make password visible for test purposes
    $user->makeVisible('password');

    $data = [
      'email' =>  $user->email,
      'password' =>  'password',
    ];
    //attempt login
    $response = $this->postJson(route('api.login', $data));
    //Assert it was successful and a token was received
    $response->assertStatus(200);

    $this->assertArrayHasKey('token', $response->json());
  }

  public function testWrongCredentials()
  {
    Sanctum::actingAs(
      factory(User::class)->create(),
      ['*']
    );
    //Create user
    $user = factory(\App\User::class)->create();
    //make password visible for test purposes
    $user->makeVisible('password');
    //wrong data
    $data = [
      'email' =>  $user->email,
      'password' =>  '654654654',
    ];
    //attempt login
    $response = $this->postJson(route('api.login', $data));
    //Assert it was failed and  responded with 401
    $response->assertStatus(401);
    //Assert it was failed because wrong password
    $response->assertSeeText('These credentials do not match our records');
  }

  public function testValidation()
  {
    Sanctum::actingAs(
      factory(User::class)->create(),
      ['*']
    );
    //Create user
    $users = factory(User::class)->make([
      'email' => null,
      'name' => null,
      'nik' => null,
      'org_code' => null,
      'dept' => null,
      'password' => null,
    ]);
    //make password visible for test purposes
    $users->makeVisible('password');
    //attempt register
    $response = $this->postJson(route('api.register'), $users->toArray());
    //Assert it was failed and responded with 422
    $response->assertStatus(422);
  }


  public function testLogout()
  {
    $actingAs = factory(User::class)->create();
    Sanctum::actingAs(
      $actingAs,
      ['*']
    );
    $response = $this->json('post', route('api.logout'), $actingAs->toArray());
    $response->assertSeeText('logout');
    $response->assertStatus(200);
  }

  public function testUnauthorized()
  {
    $actingAs = Sanctum::actingAs(
      factory(User::class)->create(),
      ['user:index']
    );
    //Create user
    $users = factory(User::class)->make([
      'password_confirmation' => function (array $user) {
        return $user['password'];
      }
    ]);
    //make password visible for test purposes
    $users->makeVisible('password');
    //attempt register
    $response = $this->postJson(route('api.register'), $users->toArray());

    //Assert it was successful
    $response->assertStatus(401);

    $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'unauthorized']), json_encode(['message' => 'unauthorized']));
  }
}
