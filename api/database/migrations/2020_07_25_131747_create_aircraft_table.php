<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAircraftTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('aircraft', function (Blueprint $table) {
      $table->string('id')->unique();
      $table->string('type');
      $table->string('serial_num');
      $table->string('reg_code');
      $table->string('effectivity');
      $table->string('owner')->nullable();
      $table->year('manuf_date')->nullable();
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
    Schema::table('aircraft', function (Blueprint $table) {
      $table->dropSoftDeletes();
    });
    Schema::dropIfExists('aircraft');
  }
}
