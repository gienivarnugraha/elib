<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserAuth implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public $user;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct($user)
  {
    $this->user = $user;
  }

  /**
   * The event's broadcast name.
   *
   * @return string
   */
  /*   public function broadcastAs()
  {
    return 'user-auth';
  } */

  /**
   * Get the data to broadcast.
   *
   * @return array
   */
  /*   public function broadcastWith()
  {
    return $this->user;
  }
 */
  /**
   * Get the channels the event should broadcast on.
   *
   * @return \Illuminate\Broadcasting\Channel|array
   */
  public function broadcastOn()
  {
    return new PresenceChannel('user');
  }
}
