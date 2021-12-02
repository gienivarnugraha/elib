<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManualsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('manuals', function (Blueprint $table) {
      $table->string('id')->unique();
      $table->string('type');
      $table->string('aircraft_id');
      $table->foreign('aircraft_id')->references('id')->on('aircraft');
      $table->string('part_num');
      $table->string('subject');
      $table->string('lib_call');
      $table->string('volume');
      $table->string('vendor');
      $table->string('caplist')->default(1);
      $table->string('collector')->nullable();
      $table->date('index_date')->nullable();
      $table->softDeletes();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('manuals', function (Blueprint $table) {
      $table->dropForeign(['aircraft_id']);
      $table->dropColumn('aircraft_id');
      $table->dropSoftDeletes();
    });
    Schema::dropIfExists('manuals');
  }
}
