<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnalysisResultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('analysis_results', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('checked_by_rule_no');
            $table->integer('app_id')->unique();
            $table->boolean('mitm')->default(0);
            $table->float('mitm_cvss')->default(0);
            $table->boolean('hide_icon')->default(0);
            $table->boolean('weak_crypto')->default(0);
            $table->boolean('vulnerable_leak')->default(0);
            $table->boolean('malicious_leak')->default(0);
            $table->integer('no_rules_broken')->default(0);
            $table->integer('no_flow')->default(0);
            $table->boolean('cert_pinning_mitm')->default(0);
            $table->float('cert_pinning_mitm_cvss')->default(0);
            $table->boolean('api_key')->default(0);
            $table->boolean('password')->default(0);
            $table->boolean('privilege_escalation')->default(0);
            $table->float('privilege_escalation_cvss')->default(0);
            $table->integer('vulnerability_count')->default(0);
            $table->integer('warning_count')->default(0);
            $table->integer('information_count')->default(0);
            $table->boolean('isVisible')->default(false);
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
        Schema::dropIfExists('analysis_results');
    }
}
