<?php
namespace App\SecureAppStore\Traits;

use App\SecureAppStore\Tools\Tool;
use App\SecureAppStore\Tools\ArgusAPIMisuse;
use App\SecureAppStore\Tools\ArgusTaint;
use App\SecureAppStore\Tools\Flowdroid;
use App\SecureAppStore\Tools\EviCheck;
use App\SecureAppStore\Tools\Mallodroid;
use App\SecureAppStore\Tools\Qark;

trait CMDHelper {
  public function make_cmds($path_to_apk, $apk_name, $rule) {
    $filename_without_ext = basename($this->data['filename'], '.apk');
    $cmds = [];

    if($rule->api_misuse) {
      // Run Argus_API_Misuse
      $cmd = new ArgusAPIMisuse("argus_api_misuse");
      $cmd->makeCmd($path_to_apk, $this->data['filename']);

      $cmds[$cmd->getTestName()] = $cmd;

      // Run Mallodroid
      $cmd = new Mallodroid("mallodroid_api_misuse");
      $cmd->makeCmd($path_to_apk, $this->data['filename']);

      $cmds[$cmd->getTestName()] = $cmd;
    }

    if( ($rule->custom_policy !== null) && (strlen($rule->custom_policy) != 0)) {
      // Run EviCheck with custom policy
      $cmd = new EviCheck("evicheck_custom");
      $cmd->makeCmd($path_to_apk, $this->data['filename'], $rule->custom_policy);

      $cmds[$cmd->getTestName()] = $cmd;
    }

    if($rule->vulnerability_scan) {
      // Run QARK
      $cmd = new Qark("qark_vuln_scan");
      $cmd->makeCmd($path_to_apk, $this->data['filename']);

      $cmds[$cmd->getTestName()] = $cmd;
    }

    if($rule->taint_analysis) {
      // Run Argus_taint
      $cmd = new ArgusTaint("argus_taint");
      $cmd->makeCmd($path_to_apk, $this->data['filename']);

      $cmds[$cmd->getTestName()] = $cmd;

      // Run FlowDroid
      $flowdroid_extra_arg = "--APLENGTH $rule->taint_aplength"
            . ($rule->taint_nocallbacks ? ' --NOCALLBACKS' : '')
            . ($rule->taint_sysflows ? ' --SYSFLOWS' : '')
            . ($rule->taint_implicit ? ' --IMPLICIT' : '')
            . ($rule->taint_static ? ' --NOSTATIC' : '');

      $cmd = new Flowdroid("flowdroid_taint");
      $cmd->makeCmd($path_to_apk, $this->data['filename'], $flowdroid_extra_arg);

      $cmds[$cmd->getTestName()] = $cmd;
    }

    return $cmds;
  }
}
