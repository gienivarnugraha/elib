<?php

namespace App\Observers;

use App\Manual;
use App\Notifications\DataNotification;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class ManualObserver
{
  protected $users;
  protected $currentUser;

  public function __construct()
  {
    $this->users = User::active()->get();
    $this->currentUser = Auth::user();
  }
  public function created(Manual $manual)
  {
    Notification::send($this->users, new DataNotification($manual, 'created', $this->currentUser));
  }
  public function updated(Manual $manual)
  {
    Notification::send($this->users, new DataNotification($manual, 'updated', $this->currentUser));
  }
  public function deleted(Manual $manual)
  {
    Notification::send($this->users, new DataNotification($manual, 'deleted', $this->currentUser));
  }
}
