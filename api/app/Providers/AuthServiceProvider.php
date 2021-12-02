<?php

namespace App\Providers;

use App\Document;
use App\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
  /**
   * The policy mappings for the application.
   *
   * @var array
   */
  protected $policies = [
    // 'App\Model' => 'App\Policies\ModelPolicy',
  ];

  /**
   * Register any authentication / authorization services.
   *
   * @return void
   */
  public function boot()
  {
    $this->registerPolicies();

    Gate::define('docBelongsTo', function (User $user, Document $document) {
      return $user->id === $document->assignee_id || $user->is_admin
        ? Response::allow()
        : Response::deny('You do not own this document.');
    });

    Gate::define('currentUser', function (User $user) {
      return $user->id === Auth::user()->id
        ? Response::allow()
        : Response::deny('You cant change others properties.');
    });

    Gate::define('isAdmin', function (User $user) {
      return $user->is_admin
        ? Response::allow()
        : Response::deny('You must be a administrator.');
    });
  }
}
