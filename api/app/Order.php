<?php

namespace App;

use App\Manual;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class Order extends Model implements Searchable
{

  use SoftDeletes;

  protected $with = ['user', 'manual'];

  public function getSearchResult(): SearchResult
  {
    $url = route('orders.show', $this->id);

    return new SearchResult(
      $this,
      $this->getUserName(),
      $url
    );
  }

  protected $guarded = ['id'];

  public $incrementing = false;

  public static function boot()
  {
    parent::boot();
    self::creating(function ($model) {
      $model->id = uniqid("id_");
    });
  }

  public function user()
  {
    return $this->belongsTo(User::class)->select(['id', 'name']);
  }
  public function manual()
  {
    return $this->belongsTo(Manual::class)->select(['id',  'type', 'subject'])->without('order', 'aircraft', 'revision');
  }
}
