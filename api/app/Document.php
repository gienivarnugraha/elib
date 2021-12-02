<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class Document extends Model implements Searchable
{
  use SoftDeletes;

  protected $with = ['aircraft', 'assignee', 'revision'];

  public function getSearchResult(): SearchResult
  {
    $url = route('documents.show', $this->id);

    return new SearchResult(
      $this,
      $this->subject,
      $url
    );
  }

  /**
   * The model's default values for attributes.
   *
   * @var array
   */
  protected $attributes = [
    'reference' => 'No Ref',
  ];

  /**
   * get mass assignable attribute
   * attributes that not listed in here is $guarded
   *
   * @var array
   */
  protected $fillable = ['id', 'office', 'type', 'subject', 'aircraft_id', 'assignee_id', 'reference'];
  //protected $guarded = ['id', 'kpi_status'];

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

  public function saveWithoutEvents(array $options = [])
  {
    return static::withoutEvents(function () use ($options) {
      return $this->save($options);
    });
  }
  /**
   * define relationship document with aircraft
   *
   * @return void
   */
  public function aircraft()
  {
    return $this->belongsTo('App\Aircraft')->select(['id', 'type', 'serial_num', 'reg_code', 'owner', 'manuf_date'])->withTrashed(); //
  }

  /**
   *define relations ship with  assignee (user)
   *
   * @return void
   */
  public function assignee()
  {
    return $this->belongsTo('App\User', 'assignee_id')->select(['id', 'name'])->withTrashed();
  }

  /**
   * revision
   *
   * @return void
   */
  public function revision()
  {
    return $this->morphMany('App\Revision', 'revisable')->latest();
  }


  /**
   * Scope a query to only documents  of a given type.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @param  mixed  $type
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeOfType($query, $type)
  {
    return $query->where('type', $type);
  }


  /* make relationship not always eager load

  public function scopeWithImages()
  {
    return static::with('images')->get();
  } */


  /**
   * revision
   *
   * @return void
   */
  /*   public function getIndex()
  {
    return $this->revision->first()->index; //
  } */

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
