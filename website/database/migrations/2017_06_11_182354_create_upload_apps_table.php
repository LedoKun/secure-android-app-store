<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUploadAppsTable extends Migration
{
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up()
  {
    Schema::create('upload_apps', function (Blueprint $table) {
      $table->increments('id');

      $table->string('package_name');
      $table->string('apk_label')->nullable();
      $table->string('version');
      $table->string('min_sdk_level');
      $table->string('min_sdk_platform');

      $table->string('originalFilename');
      $table->string('filename')->unique();
      $table->integer('size')->unsigned();
      $table->string('md5');
      $table->string('sha1');
      $table->string('sha256')->unique();

      $table->boolean('isBeingAnalyzed')->default(false);
      $table->boolean('isFailedAnalysis')->default(false);
      $table->integer('attempts')->default(0);
      $table->boolean('isAnalyzed')->default(false);
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
    Schema::dropIfExists('upload_apps');
  }
}
