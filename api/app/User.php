<?php

namespace App;

use App\Document;
use App\Order;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class User extends Authenticatable implements Searchable
{
  use Notifiable, HasApiTokens, SoftDeletes;

  public function getSearchResult(): SearchResult
  {
    $url = route('users.show', $this->id);

    return new SearchResult(
      $this,
      $this->name,
      $url
    );
  }

  /**
   * The model's default values for attributes.
   *
   * @var array
   */
  protected $attributes = [
    'is_admin' => false,
  ];

  /**
   *  disable incrementing id on user because user id is unique string
   *
   * @var bool
   */
  public $incrementing = false;

  public static function boot()
  {
    parent::boot();
    self::creating(function ($model) {
      $model->id = uniqid("id_");
    });
  }
  /**
   * Get the class list for the user.
   */
  public function assignee()
  {
    return $this->hasMany(Document::class, 'assignee_id')->select(['assignee_id', 'subject', 'type']);
  }

  /**
   * Get the class list for the user.
   */
  public function orders()
  {
    return $this->hasMany(Order::class, 'user_id');
  }


  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'name', 'email', 'password', 'nik', 'org_code', 'dept'
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [
    'password', 'remember_token', 'deleted_at', 'created_at', 'updated_at',
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'is_admin' => 'boolean',
    'is_active' => 'boolean',
  ];

  /**
   * Scope a query to only include admins.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeAdmin($query)
  {
    return $query->where('is_admin', true);
  }

  /**
   * Scope a query to only include admins.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }

  public function saveWithoutEvents(array $options = [])
  {
    return static::withoutEvents(function () use ($options) {
      return $this->save($options);
    });
  }
}
