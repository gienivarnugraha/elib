<?php

namespace App\Providers;

use App\Aircraft;
use App\Document;
use App\File;
use App\Manual;
use App\Observers\AircraftObserver;
use App\Observers\DocumentObserver;
use App\Observers\FileObserver;
use App\Observers\ManualObserver;
use App\Observers\OrderObserver;
use App\Observers\RevisionObserver;
use App\Observers\UserObserver;
use App\Order;
use App\Revision;
use App\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    Schema::defaultStringLength(191);
  }

  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
    File::observe(FileObserver::class);
    Document::observe(DocumentObserver::class);
    Revision::observe(RevisionObserver::class);
    Aircraft::observe(AircraftObserver::class);
    User::observe(UserObserver::class);
    Manual::observe(ManualObserver::class);
    Order::observe(OrderObserver::class);
    JsonResource::withoutWrapping();
  }
}
