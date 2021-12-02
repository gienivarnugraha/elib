<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class DataChange implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public $model;

  public $data;

  public $event;

  public $changes;

  public $user;
  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(Model $model, $event)
  {
    $this->event = $event;
    $this->model = class_basename($model);
    $this->data = $model;
    $this->changes = $model->getChanges();
    $this->user = Auth::user();
  }

  /**
   * The event's broadcast name.
   *
   * @return string
   */
  public function broadcastAs()
  {
    return 'data-change';
  }

  /**
   * Get the channels the event should broadcast on.
   *
   * @return \Illuminate\Broadcasting\Channel|array
   */
  public function broadcastOn()
  {
    return new Channel('user.' . $this->user->id);
  }
}
