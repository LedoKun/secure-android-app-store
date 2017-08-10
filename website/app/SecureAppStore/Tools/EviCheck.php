<?php

/**
* Argus
*
* @author     Rom Luengwattanapong <s1567783@sms.ed.ac.uk>
*
* Handle Argus_SAF
*/

namespace App\SecureAppStore\Tools;

use App\SecureAppStore\Tools\Tool;

class EviCheck extends Tool {

  public function makeCmd($path_to_file, $filename, $policy_name) {
    $image_name = env('EVICHECK_IMAGE_NAME', 'secureappstore_evicheck');
    $application_volume = env('APPLICATION_VOLUME_CONTAINER_NAME', 'secureappstore_applications_1');
    $cpu_share = env('ANALYSIS_TOOLS_CPU_SHARES', '950');

    $filename_without_ext = basename($filename, '.apk');
    $container_name = $filename_without_ext . '_temporary_container';
    $full_file_path = $path_to_file.$filename;

    $this->cmd = "docker run -i --rm --volumes-from $application_volume:ro " .
    "--cpu-shares=$cpu_share --name $container_name $image_name -f " . $full_file_path .
    " -g -p /tmp/policy/" . $policy_name . " -m";

    $this->cmd_on_timeout = "docker stop $container_name";
  }

  public function parseResult() {
    $rule = "/Number of violated rules:[^0-9]+/";
    $rule_violated = "/^Policy violated!/";

    $db_record = [];
    $issues = [];

    $hay_stack = explode(PHP_EOL, $this->stdout);

    $number_of_rules = preg_grep($rule, $hay_stack);

    if(is_array($number_of_rules) && count($number_of_rules) > 0) {
      $db_record['no_rules_broken'] = end($number_of_rules);
      $issues[] = $db_record['no_rules_broken'];
      $issues[] = 'Please refer to EviCheck output for more information.' . PHP_EOL;

      $details = preg_grep($rule_violated, $hay_stack);
      $issues = array_merge($issues, $details);
    } else {
      $db_record['no_rules_broken'] = 0;
      $issues[] = 'No rules violated.';
    }

    $issues['raw'] = $this->stdout;
    $this->parsedResultDBEntry = $db_record;
    $this->parseResult = $issues;
  }
}
