<?php

namespace App\Observers;

use App\Notifications\DataNotification;
use App\Order;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class OrderObserver
{
  protected $users;
  protected $currentUser;

  public function __construct()
  {
    $this->users = User::active()->get();
    $this->currentUser = Auth::user();
  }

  public function created(Order $order)
  {
    $user = User::findOrFail($order->user_id);
    Notification::send($this->users, new DataNotification($order, 'created', $user));
  }

  public function updated(Order $order)
  {
    Notification::send($this->users, new DataNotification($order, 'status', $this->currentUser));
  }
}
