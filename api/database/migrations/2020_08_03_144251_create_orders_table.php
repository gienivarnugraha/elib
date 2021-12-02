<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('orders', function (Blueprint $table) {
      $table->string('id')->unique();
      $table->string('manual_id');
      $table->foreign('manual_id')->references('id')->on('manuals');
      $table->string('user_id');
      $table->foreign('user_id')->references('id')->on('users');
      $table->date('send_date')->nullable();
      $table->date('return_date')->nullable();
      $table->boolean('is_closed')->default(1);
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
    Schema::table('orders', function (Blueprint $table) {
      $table->dropForeign(['manual_id']);
      $table->dropColumn('manual_id');
      $table->dropForeign(['user_id']);
      $table->dropColumn('user_id');
    });
    Schema::dropIfExists('orders');
  }
}
