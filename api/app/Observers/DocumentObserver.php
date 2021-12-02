<?php

namespace App\Observers;

use App\Document;
use App\Notifications\DataNotification;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class DocumentObserver
{
  /**
   * Handle the document "updated" event.
   *
   *  DONT FORGET TO INSERT IN APPSERVICEPROVIDE.PHP
   * DONT USE WHERE ON UPDATING https://github.com/laravel/framework/issues/11777
   *
   *
   * @param  \App\Document  $document
   * @return void
   */
  protected $users;
  protected $currentUser;

  public function __construct()
  {
    $this->users = User::active()->get();
    $this->currentUser = Auth::user();
  }

  public function updated(Document $document)
  {
    //broadcast(new DataChange($document, 'updated')); //->toOthers();
    Notification::send($this->users, new DataNotification($document, 'updated', $this->currentUser));
  }

  public function created(Document $document)
  {
    //broadcast(new DataChange($document, 'created')); //->toOthers, $this->currentUser();
    Notification::send($this->users, new DataNotification($document, 'created', $this->currentUser));
  }

  public function deleted(Document $document)
  {
    //broadcast(new DataChange($document, 'deleted'))->toOthers, $this->currentUser();
    Notification::send($this->users, new DataNotification($document, 'deleted', $this->currentUser));
  }
}
