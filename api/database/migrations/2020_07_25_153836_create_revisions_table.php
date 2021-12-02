<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRevisionsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('revisions', function (Blueprint $table) {
      $table->string('id')->unique();
      $table->string('revisable_id');
      $table->string('revisable_type');
      $table->string('changes')->nullable();
      $table->string('index')->default('NE');
      $table->date('index_date')->nullable();
      $table->boolean('is_closed')->default(0);
      $table->boolean('kpi_status')->default(0);
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
    Schema::table('revisions', function (Blueprint $table) {
      $table->dropSoftDeletes();
    });
    Schema::dropIfExists('revisions');
  }
}
