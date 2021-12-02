<?php

namespace App\Observers;

use App\Aircraft;
use App\Notifications\DataNotification;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class AircraftObserver
{
  /**
   * Handle the aircraft "updated" event.
   *
   *  DONT FORGET TO INSERT IN APPSERVICEPROVIDE.PHP
   * DONT USE WHERE ON UPDATING https://github.com/laravel/framework/issues/11777
   *
   *
   * @param  \App\Aircraft  $document
   * @return void
   */
  protected $users;
  protected $currentUser;

  public function __construct()
  {
    $this->users = User::active()->get();
    $this->currentUser = Auth::user();
  }

  public function updated(Aircraft $aircraft)
  {
    //broadcast(new DataChange($aircraft, 'updated')); //->toOthers();
    Notification::send($this->users, new DataNotification($aircraft, 'updated', $this->currentUser));
  }

  public function created(Aircraft $aircraft)
  {
    //broadcast(new DataChange($aircraft, 'created')); //->toOthers, $this->currentUser();
    Notification::send($this->users, new DataNotification($aircraft, 'created', $this->currentUser));
  }

  public function deleted(Aircraft $aircraft)
  {
    //broadcast(new DataChange($aircraft, 'deleted'))->toOthers, $this->currentUser();
    Notification::send($this->users, new DataNotification($aircraft, 'deleted', $this->currentUser));
  }
}
