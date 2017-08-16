<?php

namespace Tests\Unit;

use Laravel\BrowserKitTesting\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\CreatesApplication;

use App\AnalysisToolSetting;

class AnalysisToolControllerTest extends BaseTestCase
{

  /*
  |--------------------------------------------------------------------------
  | Analysis tool controller Test
  |--------------------------------------------------------------------------
  |
  | This unit test is responsible for Analysis tool controller testing
  |
  */

  use DatabaseMigrations, WithoutMiddleware, CreatesApplication;

  public $baseUrl = 'http://localhost:8080';

  /**
  * Test AnalysisToolController@index
  *
  * @return void
  */
  public function testIndex()
  {
    $this->call('GET', 'admin/rules');

    // Page load
    $this->assertResponseOk();
    $this->assertViewHas('existingRules');
    $this->assertViewHas('currentDefault');
    $this->assertViewHas('historyDefault');
  }

  /**
  * Test AnalysisToolController@create
  *
  * @return void
  */
  public function testCreate()
  {
    $this->call('GET', 'admin/rules/create');

    // Page load
    $this->assertResponseOk();
    $this->assertViewHas('disableableFields');
  }

  /**
  * Test AnalysisToolController@store with input errors
  * Should be redirected back to the form with errors
  * @return void
  */
  public function testStoreWithErrors()
  {
    $this->call('POST', 'admin/rules');

    // Page load
    $this->assertResponseStatus(302);

    // Test for errors
    $this->assertSessionHasErrors();
    $this->assertSessionHasErrors(['api_misuse', 'vulnerability_scan']);
  }

  /**
  * Test AnalysisToolController@store without errors
  *
  * @return void
  */
  public function testStore()
  {
    $data = [
      'rule_name' => 'test',
      'comments' => 'comment',
      'timeout' => 500,
      'api_misuse' => 1,
      'vulnerability_scan' => 1,
      'custom_policy' => 'test.pol',
      'taint_analysis' => 1,
      'taint_aplength' => 5,
      'taint_nocallbacks' => 1,
      'taint_sysflows' => 1,
      'taint_implicit' => 1,
      'taint_static' => 1
    ];

    $this->call('POST', 'admin/rules', $data);

    // Page load
    $this->assertResponseStatus(302);

    // Assert redirect
    $this->assertRedirectedTo('/admin/rules');

    // Assert information on DB
    $record = AnalysisToolSetting::where('rule_name', 'test')->get()->first();
    $this->seeInDatabase('analysis_tool_settings', $data);
  }

  /**
  * Test AnalysisToolController@show
  *
  * @return void
  */
  public function testShow()
  {
    $number_of_test = 10;

    for($i = 0; $i < $number_of_test; $i++) {
      $id = rand(1,1000);
      $this->call('GET', 'admin/rules/' . $id);

      // Page load
      $this->assertResponseStatus(302);
      $this->assertRedirectedTo('admin/rules');
    }
  }

  /**
  * Test AnalysisToolController@update
  * Editing inactive rules
  * @return void
  */
  public function testUpdate()
  {
    $data = [
      'rule_name' => 'test',
      'comments' => 'comment',
      'timeout' => 500,
      'api_misuse' => 1,
      'vulnerability_scan' => 1,
      'custom_policy' => 'test.pol',
      'taint_analysis' => 1,
      'taint_aplength' => 5,
      'taint_nocallbacks' => 1,
      'taint_sysflows' => 1,
      'taint_implicit' => 1,
      'taint_static' => 1
    ];

    $rules = new AnalysisToolSetting($data);
    $rules->save();

    $data['rule_name'] = 'test-edit';

    $this->call('PATCH', 'admin/config/'.$rules->id, $data);

    var_dump(AnalysisToolSetting::findOrFail($rules->id)->get()->first());

    // Page load
    $this->assertResponseStatus(302);
    $this->assertRedirectedTo('admin/config');
    $this->seeInDatabase('analysis_tool_settings', $data);
  }

}
