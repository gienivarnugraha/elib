<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('users', function (Blueprint $table) {
      $table->string('id')->unique();
      $table->string('name');
      $table->string('nik');
      $table->string('org_code');
      $table->string('dept');
      $table->string('email')->unique();
      $table->string('password');
      $table->boolean('is_admin')->default(false);
      $table->boolean('is_active')->default(false);
      $table->dateTime('last_login')->nullable();
      $table->rememberToken();
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
    Schema::table('users', function (Blueprint $table) {
      $table->dropSoftDeletes();
    });
    Schema::dropIfExists('users');
  }
}
