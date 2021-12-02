<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use phpDocumentor\Reflection\Types\Nullable;

class CreateFilesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('files', function (Blueprint $table) {
      $table->string('id')->unique();
      $table->string('type')->nullable();
      $table->string('revision_id');
      $table->foreign('revision_id')->references('id')->on('revisions');
      $table->string('filepath')->nullable();
      $table->string('filename')->nullable();
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
    Schema::table('files', function (Blueprint $table) {
      $table->dropForeign(['revision_id']);
      $table->dropColumn('revision_id');
      $table->dropSoftDeletes();
    });
    Schema::dropIfExists('files');
  }
}
