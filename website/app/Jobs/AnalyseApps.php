<?php
/**
* Secure Android App Store's background worker
*
* An implementation of a Secure Android App Store's background worker.
* It extandes Laravel 5.4's ShouldQueue class and use multiple Laravel's Facades.
* We used it to invoke Android application analysis process.
*
* @copyright  Copyright (c) 2017 Rom Luengwattanapong (s1567783@ed.ac.uk)
*/


namespace App\Jobs;

use Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Exception;

# Models
use App\AnalysisToolSetting;
use App\AnalysisToolDefaultRule;
use App\UploadApp;
use App\AnalysisResult;
use App\SiteConfig;

# Traits
use App\SecureAppStore\Traits\CMDHelper;

# Plugins
use Carbon\Carbon;

# Tools
use App\SecureAppStore\Tools\Tool;
use App\SecureAppStore\Tools\ArgusAPIMisuse;
use App\SecureAppStore\Tools\ArgusTaint;
use App\SecureAppStore\Tools\Flowdroid;
use App\SecureAppStore\Tools\EviCheck;
use App\SecureAppStore\Tools\Mallodroid;
use App\SecureAppStore\Tools\Qark;

class AnalyseApps implements ShouldQueue
{
  use CMDHelper, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  // APK file information
  protected $data;

  public $tries;
  public $timeout;

  /**
  * Create a new job instance. Store useful information about the APK files
  *
  * @param array $data APK file information
  * @return void
  */
  public function __construct($data)
  {
    $this->data = $data;
  }

  /**
  * Invokes the background analysis process and parse the output. It also
  * store the results on the database and save it in json format.
  *
  * @param none
  * @return void
  */
  public function handle() {
    // Verify job
    $app = UploadApp::where('id', $this->data['id'])->first();
    $isFileExists = Storage::disk('apk')->exists($this->data['filename']);
    $currentDefault = AnalysisToolDefaultRule::getDefaultRuleID()->latest()->first();

    if ($currentDefault === null) {
      throw new Exception('[AnalyseApps] No active rules.');
    }

    $rule = AnalysisToolSetting::where('id', $currentDefault->rule_id)->first();

    if ($rule === null) {
      throw new Exception('[AnalyseApps] No active rules.');
    }

    if(!$isFileExists) {
      throw new Exception('[AnalyseApps] File missing.');
    }

    if($app === null) {
      throw new Exception('[AnalyseApps] No app with the ID of ' . $this->data['id'] . '.');
    }

    Log::info('[AnalyseApps] Starting a new app analysis job (App #'
    . $this->data['id'] . ').');

    $filename_without_ext = basename($this->data['filename'], '.apk');
    UploadApp::increaseAttempts($app->id);

    // Get full path
    $path_to_apk = Storage::disk('apk')
    ->getDriver()->getAdapter()->getPathPrefix();

    // Get time limit
    $time_limit = Carbon::now()->addSeconds($rule->timeout);

    // Create commands
    $cmds = $this->make_cmds($path_to_apk, $this->data['filename'], $rule);
    $analysis_record = array();

    // Prepare record to be inserted into DB
    $parsed_result_db['checked_by_rule_no'] = $rule->id;
    $parsed_result_db['app_id'] = $app->id;

    foreach ($cmds as $cmd):
      UploadApp::setBeingAnalyze($app->id, '1');

      Log::info('[AnalyseApps] Testing (App #' . $app->id . '): '
      .$cmd->getTestName());

      $cmd->run($time_limit);

      $parseResult[$cmd->getTestName()] = $cmd->getParsedResult();
      $parsed_result_db = array_merge($cmd->getParsedResultDBEntry(), $parsed_result_db);

      // Log::info($parsed_result_db);

      UploadApp::setBeingAnalyze($app->id, '0');
    endforeach;

    // Save analysis results
    $parsed_result_filename = $filename_without_ext.'_parsed.json';
    Storage::disk('analysis')->put($parsed_result_filename, json_encode($parseResult));

    // Check if default rule is changed or not
    $isNewDefault = AnalysisToolDefaultRule::getDefaultRuleID()->latest()->first();

    // Rules changes
    if($isNewDefault->rule_id != $currentDefault->rule_id) {
      throw new Exception('New rules detected');
    }

    // Mark as analysed and save results
    AnalysisResult::where('app_id', $app->id)->delete();

    $analysis_result = new AnalysisResult($parsed_result_db);
    $analysis_result->isVisible = SiteConfig::isAppVisible($analysis_result);
    $analysis_result->save();

    $update_record = UploadApp::findOrFail($app->id);
    $update_record->isBeingAnalyzed = 0;
    $update_record->isFailedAnalysis = 0;
    $update_record->isAnalyzed = 1;
    $update_record->save();
  }

  public function failed($exception) {

    UploadApp::setBeingAnalyze($this->data['id'], '0');
    UploadApp::increaseFailedAttempts($this->data['id']);

  }

}
