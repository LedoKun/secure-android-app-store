<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnalysisToolSettingsTable extends Migration
{
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up()
  {
    Schema::create('analysis_tool_settings', function (Blueprint $table) {
      $table->increments('id');
      $table->string('rule_name');
      $table->string('comments')->nullable();

      $table->string('timeout');

      $table->boolean('api_misuse');
      $table->boolean('vulnerability_scan');

      $table->string('custom_policy')->nullable()->default(null);

      $table->boolean('taint_analysis');
      $table->integer('taint_aplength');
      $table->boolean('taint_nocallbacks');
      $table->boolean('taint_sysflows');
      $table->boolean('taint_implicit');
      $table->boolean('taint_static');

      $table->timestamps();
    });

    DB::table('analysis_tool_settings')->insert(
      array(
        'rule_name'              => 'Default',
        'comments'               => 'This is the default rule',
        'timeout'                => '3600',
        'api_misuse'             => '1',
        'vulnerability_scan'     => '1',
        'custom_policy'          => null,

        'taint_analysis'        => '1',
        'taint_aplength'        => '5',
        'taint_nocallbacks'     => '0',
        'taint_sysflows'        => '0',
        'taint_implicit'        => '0',
        'taint_static'          => '0',
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
    Schema::dropIfExists('analysis_tool_settings');
  }
}
