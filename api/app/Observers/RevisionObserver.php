<?php

namespace App\Observers;

use App\Notifications\DataNotification;
use App\Revision;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class RevisionObserver
{
  protected $users;
  protected $currentUser;

  public function __construct()
  {
    $this->users = User::active()->get();
    $this->currentUser = Auth::user();
  }

  public function created(Revision $revision)
  {
    Notification::send($this->users, new DataNotification($revision, 'created', $this->currentUser));
  }

  public function updated(Revision $revision)
  {
    Notification::send($this->users, new DataNotification($revision, 'status', $this->currentUser));
  }
}
