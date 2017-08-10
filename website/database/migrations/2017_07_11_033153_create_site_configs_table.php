<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSiteConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_configs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('site_name')->default('Secure App Store');
            $table->float('max_cvss');
            $table->boolean('allow_mitm');
            $table->boolean('allow_hide_icon');
            $table->boolean('allow_weak_cryptographic_api');
            $table->boolean('allow_vulnerable_leak');
            $table->boolean('allow_malicious_leak');
            $table->integer('max_no_rules_broken');
            $table->integer('max_no_flow');
            $table->boolean('allow_cert_pinning_mitm');
            $table->boolean('allow_api_key');
            $table->boolean('allow_password');
            $table->boolean('allow_privilege_escalation');
            $table->integer('max_vulnerability_count');
            $table->timestamps();
        });

        DB::table('site_configs')->insert(
          array(
            'site_name' => 'Secure Android App Store',
            'max_cvss' => '-1',
            'allow_mitm' => '3.9',
            'allow_hide_icon' => '1',
            'allow_weak_cryptographic_api' => '0',
            'allow_vulnerable_leak' => '0',
            'allow_malicious_leak' => '0',
            'max_no_rules_broken' => '-1',
            'max_no_flow' => '10',
            'allow_cert_pinning_mitm' => '0',
            'allow_api_key' => '0',
            'allow_password' => '0',
            'allow_privilege_escalation' => '0',
            'max_vulnerability_count' => '0',
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
        Schema::dropIfExists('site_configs');
    }
}
