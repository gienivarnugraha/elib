<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('documents', function (Blueprint $table) {
      $table->string('id')->unique();
      $table->string('no'); //->unique()
      $table->string('office');
      $table->string('type');
      $table->string('subject');
      $table->string('reference');
      $table->string('aircraft_id');
      $table->foreign('aircraft_id')->references('id')->on('aircraft');
      $table->string('assignee_id');
      $table->foreign('assignee_id')->references('id')->on('users');
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
    Schema::table('documents', function (Blueprint $table) {
      $table->dropForeign(['aircraft_id']);
      $table->dropColumn('aircraft_id');
      $table->dropForeign(['assignee_id']);
      $table->dropColumn('assignee_id');
      $table->dropSoftDeletes();
    });
    Schema::dropIfExists('documents');
  }
}
