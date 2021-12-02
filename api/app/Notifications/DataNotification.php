<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DataNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
  use Queueable;

  public $model;
  public $event;
  public $changes;
  public $original;
  public $user;
  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct(Model $model, $event, $user)
  {
    $this->model = $model;
    $this->event = $event;
    $this->user = $user;
    $this->changes = $model->getChanges();
    $this->original = $model->getOriginal();
  }

  /**
   * Get the notification's delivery channels.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function via($notifiable)
  {
    return ['database', 'broadcast'];
  }

  public function getBeforeAfter()
  {
    $changes = $this->changes;
    $original = $this->original;
    $dirty = [];
    foreach ($changes as $key => $value) {
      if ($key != 'updated_at') {
        $dirty[$key] = $original[$key];
        //$dirty[$key] = ['from' =>  $original[$key], 'to' => $value];
      }
    }
    return $dirty;
  }

  /**
   * Get the array representation of the notification.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function toArray($notifiable)
  {
    return [
      'event' => $this->event,
      'model' => strtolower(class_basename($this->model)),
      'data' => class_basename($this->model) == 'Revision' ? $this->model->revisable : $this->model,
      'changes' => $this->getBeforeAfter(),
      'user' => $this->user,
    ];
  }
}
