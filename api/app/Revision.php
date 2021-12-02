<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Revision extends Model
{

  /*
      id
      document_id
      changes
      index
      is_closed
      file_id
      */

  protected $with = ['file'];

  //protected $fillable = ['document_id', 'is_closed', 'index', 'file_id', 'changes'];
  protected $guarded = ['id', 'is_closed', 'kpi_status'];

  protected $touches = ['revisable'];

  /**
   * The model's default values for attributes.
   *
   * @var array
   */
  protected $attributes = [
    'index' => 'NE',
    'is_closed' => 0,
    'kpi_status' => 0,
  ];

  protected $casts = [
    'is_closed' => 'boolean',
    'kpi_status' => 'boolean',
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
      //$model->touchOwners();
    });
  }

  public function revisable()
  {
    return $this->morphTo();
  }

  public function file()
  {
    return $this->hasOne('App\File'); //
  }

  public function saveWithoutEvents(array $options = [])
  {
    return static::withoutEvents(function () use ($options) {
      return $this->save($options);
    });
  }

  public function getCreatedAtAttribute($date)
  {
    return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d');
  }

  public function getUpdatedAtAttribute($date)
  {
    return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d');
  }
}
