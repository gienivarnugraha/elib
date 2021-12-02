<?php

namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SearchTest extends TestCase
{
  use DatabaseMigrations;

  public function testSearch()
  {
    Sanctum::actingAs(
      factory(\App\User::class)->create(),
      ['*']
    );
    factory(\App\Document::class, 10)->create([
      'aircraft_id' => factory(\App\Aircraft::class)->create()->id,
      'assignee_id' => factory(\App\User::class)->create()->id,
      'sender_id' => factory(\App\User::class)->create()->id
    ])->toArray();

    $response = $this->call('get', '/api/search', ['query' => 'a']);

    $response->assertSee('message');

    $response->assertStatus(200);
  }
}
