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
      $cmd = new Tool("argus_api_misuse");
      $cmd->makeCmd(env('ARGUS_IMAGE_NAME', 'secureappstore_argus'), $path_to_apk, $this->data['filename'], ['api_or_taint' => 'a']);
      $cmds[$cmd->getTestName()] = $cmd;

      // Run Mallodroid
      $cmd = new Tool("mallodroid_api_misuse");
      $cmd->makeCmd(env('MALLODROID_IMAGE_NAME', 'secureappstore_mallodroid'), $path_to_apk, $this->data['filename']);
      $cmds[$cmd->getTestName()] = $cmd;
    }

    if( ($rule->custom_policy !== null) && (strlen($rule->custom_policy) != 0)) {
      // Run EviCheck with custom policy
      $cmd = new Tool("evicheck_custom");
      $cmd->makeCmd(env('EVICHECK_IMAGE_NAME', 'secureappstore_evicheck'), $path_to_apk, $this->data['filename'], ['policy' => $rule->custom_policy]);
      $cmds[$cmd->getTestName()] = $cmd;
    }

    if($rule->vulnerability_scan) {
      // Run QARK
      $cmd = new Tool("qark_vuln_scan");
      $cmd->makeCmd(env('QARK_IMAGE_NAME', 'secureappstore_qark'), $path_to_apk, $this->data['filename']);
      $cmds[$cmd->getTestName()] = $cmd;
    }

    if($rule->taint_analysis) {
      // Run Argus_taint
      $cmd = new Tool("argus_taint");
      $cmd->makeCmd(env('ARGUS_IMAGE_NAME', 'secureappstore_argus'), $path_to_apk, $this->data['filename'], ['api_or_taint' => 't']);
      $cmds[$cmd->getTestName()] = $cmd;

      // Run FlowDroid
      $flowdroid_extra_arg = [];
      $flowdroid_extra_arg[] = 'APLENGTH ' . $rule->taint_aplength;

      if($rule->taint_nocallbacks) {
        $flowdroid_extra_arg[] = 'NOCALLBACKS';
      }

      if($rule->taint_sysflows) {
        $flowdroid_extra_arg[] = 'SYSFLOWS';
      }

      if($rule->taint_implicit) {
        $flowdroid_extra_arg[] = 'IMPLICIT';
      }

      if($rule->taint_static) {
        $flowdroid_extra_arg[] = 'NOSTATIC';
      }

      $cmd = new Tool("flowdroid_taint");
      $cmd->makeCmd(env('FLOWDROID_IMAGE_NAME', 'secureappstore_flowdroid'), $path_to_apk, $this->data['filename'], $flowdroid_extra_arg);
      $cmds[$cmd->getTestName()] = $cmd;
    }

    return $cmds;
  }
}
