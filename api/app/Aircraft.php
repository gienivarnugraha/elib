<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class Aircraft extends Model implements Searchable
{
  use SoftDeletes;

  //protected $with = ['manual'];

  public function getSearchResult(): SearchResult
  {
    $url = route('aircrafts.show', $this->id);

    return new SearchResult(
      $this,
      $this->type,
      $url
    );
  }

  /* protected $with = ['document', 'manual']; */

  protected $guarded = ['id'];
  /*
  disable incrementing id on user because user id is unique string
   */
  public $incrementing = false;

  protected $cast = [];

  public static function boot()
  {
    parent::boot();
    self::creating(function ($model) {
      $model->id = uniqid("id_");
    });
  }

  public function document()
  {
    return $this->hasMany('App\Document')->select(['id', 'subject']);
  }

  public function manual()
  {
    return $this->hasMany('App\Manual')->select(['id', 'type']);
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
}
