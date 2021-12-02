<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class Manual extends Model implements Searchable
{
  use SoftDeletes;


  public function getSearchResult(): SearchResult
  {
    $url = route('manuals.show', $this->id);

    return new SearchResult(
      $this,
      $this->type,
      $url
    );
  }


  protected $with = ['aircraft', 'order', 'revision'];

  protected $guarded = ['id'];

  /**
   * The model's default values for attributes.
   *
   * @var array
   */
  protected $attributes = [
    'caplist' => 0,
  ];


  protected $casts = [
    'caplist' => 'boolean',
  ];
  /*
  disable incrementing id on user because user id is unique string
   */
  public $incrementing = false;

  public static function boot()
  {
    parent::boot();
    self::creating(function ($model) {
      $model->id = uniqid("id_");
    });
  }

  public function aircraft()
  {
    return $this->belongsTo('App\Aircraft')->withTrashed()->without('document', 'manual');
  }

  /**
   * revision
   *
   * @return void
   */
  public function revision()
  {
    return $this->morphMany('App\Revision', 'revisable')->orderBy('created_at', 'desc'); //
  }

  public function order()
  {
    return $this->HasMany('App\Order')->without('manual')->orderBy('created_at', 'desc')->withTrashed();
  }

  public function latestOrder()
  {
    return $this->hasOne('App\Order')->latest()->without('manual')->first();
  }


  /**
   * Scope a query to only include types.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeType()
  {
    return $this->type;
  }


  /**
   * getCreatedAtAttribute
   *
   * @param  mixed $date
   * @return void
   */
  public function getCreatedAtAttribute($date)
  {
    return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d');
  }

  /**
   * getUpdatedAtAttribute
   *
   * @param  mixed $date
   * @return void
   */
  public function getUpdatedAtAttribute($date)
  {
    return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d');
  }
}
