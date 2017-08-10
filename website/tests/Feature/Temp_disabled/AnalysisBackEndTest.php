<?php

namespace Tests\Feature;

use Laravel\BrowserKitTesting\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\CreatesApplication;
use Mockery;
use App\UploadApp;
use App\Jobs\AnalyseApps;
use App\AnalysisToolSetting;
use App\AnalysisToolDefaultRule;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

use Exception;

class AnalysisBackEndTest extends BaseTestCase
{

  use WithoutMiddleware, DatabaseMigrations, CreatesApplication;

  public $baseUrl = 'http://localhost:8080';

  public function setUp() {
    parent::setUp();
  }

  public function tearDown(){
    parent::tearDown();
    Mockery::close();
  }

  /**
  * Test analysis process that is timeout
  *
  * @return void
  */

  /** @large */
  public function testAnalysisProcessWithTimeoutExcetionThrown()
  {
    // Will cause the analysis to failed, due to timelimit
    $timeout = 5;

    // The test should expect an exception (timeout)
    $this->expectException(Exception::class);

    $full_path = dirname(__FILE__) . '/dummy.apk';
    $filename = 'test_apk.apk';

    // Save a dummy file
    Storage::disk('apk')->put($filename, fopen($full_path, 'r+'));

    // Insert a dummy record
    $fileRecord = new UploadApp();
    $fileRecord->package_name = 'test.package';
    $fileRecord->apk_label = 'testName';
    $fileRecord->version = '2';
    $fileRecord->min_sdk_level = '2';
    $fileRecord->min_sdk_platform = '2';
    $fileRecord->originalFilename = 'dummy.apk';
    $fileRecord->filename = $filename;
    $fileRecord->size = '2';
    $fileRecord->sha256 = 'test';
    $fileRecord->sha1 = 'test';
    $fileRecord->md5 = 'test';
    $fileRecord->save();

    $dummy_rules = [
      'rule_name'              => 'Default',
      'comments'               => 'This is the default rule',
      'timeout'                => $timeout,
      'api_misuse'             => '1',
      'vulnerability_scan'     => '1',
      'custom_policy'          => 'policy_api.pol',

      'taint_analysis'        => '1',
      'taint_aplength'        => '5',
      'taint_nocallbacks'     => '0',
      'taint_sysflows'        => '0',
      'taint_implicit'        => '0',
      'taint_static'          => '0',
    ];

    $insert_rules = new AnalysisToolSetting($dummy_rules);
    $insert_rules->save();

    $default_rule = [
      'rule_id'   => $insert_rules->id,
    ];

    $insert_default_rules = new AnalysisToolDefaultRule($default_rule);
    $insert_default_rules->save();

    //  Dispatch a new background analysis task
    $job = new AnalyseApps(['id' => $fileRecord->id, 'filename' => $filename]);
    $job->tries = env('ANALYSIS_RETRIES', 1);
    $job->timeout = $timeout + 180;		// Allow worker to handle cleanup process

    dispatch(($job)->onQueue('analysis'));

    $filename_without_extension = basename($filename, '.apk');
    Storage::disk('apk')->delete($filename);
    Storage::disk('analysis')->delete($filename_without_extension.'_parsed.json');
    Storage::disk('apk_png')->delete($filename_without_extension.'_icon.png');
    Storage::disk('apk_permission')->delete($filename_without_extension.'_perm.json');
  }

}
