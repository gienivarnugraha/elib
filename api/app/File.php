<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{

  /*
id
type
filename
  */

  protected $fillable = ['id'];

  public $incrementing = false;

  protected $touches = ['revision'];


  public static function boot()
  {
    parent::boot();
    self::creating(function ($model) {
      if ($model->type == 'document') {
        $model->id = uniqid("doc_");
      }
      if ($model->type == 'manual') {
        $model->id = uniqid("man_");
      }
    });
  }

  public function saveWithoutEvents(array $options = [])
  {
    return static::withoutEvents(function () use ($options) {
      return $this->save($options);
    });
  }

  public function revision()
  {
    return $this->belongsTo('App\Revision'); //
  }


  /**
   * revision
   *
   * @return void
   */
  /*   public function getRevision()
  {
    return $this->revision()->latest()->first(); //
  } */
}
