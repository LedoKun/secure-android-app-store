<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnalysisToolDefaultRulesTable extends Migration
{
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up()
  {
    Schema::create('analysis_tool_default_rules', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('rule_id');

      $table->timestamps();
    });

    DB::table('analysis_tool_default_rules')->insert(
      array(
        'rule_id'   => '1',
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
    Schema::dropIfExists('analysis_tool_default_rules');
  }
}
