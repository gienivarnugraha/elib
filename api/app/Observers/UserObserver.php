<?php

namespace App\Observers;

use App\Notifications\AuthNotification;
use App\Notifications\DataNotification;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class UserObserver
{
  protected $users;
  protected $currentUser;

  public function __construct()
  {
    $this->users = User::active()->get();
    //$this->currentUser = Auth::user();
  }

  public function updated(User $user)
  {
    //broadcast(new userCreated($user)); //->toOthers();
    //Notification::send($this->users, new AuthNotification('Auth', $user));
    Notification::send($this->users, new DataNotification($user, 'updated', $this->currentUser));
  }
}
