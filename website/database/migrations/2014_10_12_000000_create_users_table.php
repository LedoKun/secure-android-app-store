<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
      $table->increments('id');
      $table->string('name');
      $table->string('username')->unique();
      $table->string('email')->unique();
      $table->string('password');
      $table->rememberToken();
      $table->timestamps();
    });

    // Add default admin user
    // More users can be added here
    DB::table('users')->insert(
      array(
        'name'                          => 'admin',
        'username'                      => 'admin',
        'email'                         => 'admin@none.com',
        'password'                      => \Hash::make('secretpassword'),
        )
      );
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
      Schema::dropIfExists('users');
    }
  }
