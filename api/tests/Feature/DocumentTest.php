<?php

namespace Tests\Feature;

use App\Notifications\DocumentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DocumentTest extends TestCase
{
  use RefreshDatabase, WithFaker, WithoutMiddleware;

  /**
   * A basic feature test example.
   *
   * @return void
   */
  public function testDocumentIndex()
  {
    Sanctum::actingAs(
      factory(\App\User::class)->create()
    );

    $docs = factory(\App\Document::class, 2)->create([
      'aircraft_id' => factory(\App\Aircraft::class)->create()->id,
      'assignee_id' => factory(\App\User::class)->create()->id,
    ]);
    $response = $this->get('/documents');

    $response->assertStatus(200);
  }

  /**
   * A basic feature test create.
   *
   * @return void
   */
  public function testCreateDocument()
  {
    $user = Sanctum::actingAs(
      factory(\App\User::class)->create()
    );
    Notification::fake();

    $data = factory(\App\Document::class)->make([
      'aircraft_id' => factory(\App\Aircraft::class)->create()->id,
      'assignee_id' => $user->id,
      'subject' => ['subject1'],
    ]);

    $response = $this->json('post', '/documents/', $data->toArray());

    $notif = Notification::assertSentTo($user, DocumentNotification::class);

    $data->makeHidden(['id', 'no']);

    $this->assertDatabaseHas('documents', $data->toArray());
    $response->assertStatus(201);
  }
  /**
   * A basic feature test delete.
   *
   * @return void
   */
  public function testDeleteDocument()
  {
    $user = Sanctum::actingAs(
      factory(\App\User::class)->create()
    );

    $data = factory(\App\Document::class)->create([
      'aircraft_id' => factory(\App\Aircraft::class)->create()->id,
      'assignee_id' => $user->id,
    ]);

    $response = $this->json('delete', "/documents/{$data->id}");
    $response->assertStatus(200);
    $this->assertDatabaseHas('documents', ['deleted_at' => Carbon::now()]);
  }
  /**
   * A basic feature test show.
   *
   * @return void
   */
  public function testShowDocument()
  {
    $user = Sanctum::actingAs(
      factory(\App\User::class)->create()
    );

    $data = factory(\App\Document::class)->create([
      'aircraft_id' => factory(\App\Aircraft::class)->create()->id,
      'assignee_id' => $user->id,
    ]);

    $response = $this->json('get', "/documents/{$data->id}");

    $response->assertSee($data->id);
    $response->assertStatus(200);
  }
  /**
   * A basic feature test update.
   *
   * @return void
   */
  public function testUpdateDocument()
  {
    Notification::fake();

    $user = Sanctum::actingAs(
      factory(\App\User::class)->create()
    );

    $data = factory(\App\Document::class)->create([
      'aircraft_id' => factory(\App\Aircraft::class)->create()->id,
      'assignee_id' => $user->id,
    ]);

    $update = factory(\App\Document::class)->make([
      'aircraft_id' => factory(\App\Aircraft::class)->create()->id,
      'assignee_id' => $user->id,
      'subject' => 'subject tester'
    ])->toArray();

    $response = $this->json('put', "/documents/{$data->id}", $update);

    /*     // Assert a notification was sent to the given users...
    Notification::assertSentTo(
      $user,
      DocumentNotification::class,
      function ($notification, $channels) {
        return true;
      }
    ); */

    $this->assertDatabaseHas('documents', ['subject' => 'subject tester']);
    $response->assertStatus(200);
  }
}
