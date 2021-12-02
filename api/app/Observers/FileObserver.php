<?php

namespace App\Observers;

use App\File;
use App\Notifications\DataNotification;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class FileObserver
{
  protected $users;
  protected $currentUser;

  public function __construct()
  {
    $this->users = User::active()->get();
    $this->currentUser = Auth::user();
  }

  public function updated(File $file)
  {
    Notification::send($this->users, new DataNotification($file, 'uploaded', $this->currentUser));
  }
}
