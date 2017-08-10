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

class Flowdroid extends Tool {

  public function makeCmd($path_to_file, $filename, $flowdroid_extra_arg) {
    $image_name = env('FLOWDROID_IMAGE_NAME', 'secureappstore_flowdroid');
    $application_volume = env('APPLICATION_VOLUME_CONTAINER_NAME', 'secureappstore_applications_1');
    $cpu_share = env('ANALYSIS_TOOLS_CPU_SHARES', '950');

    $filename_without_ext = basename($filename, '.apk');
    $container_name = $filename_without_ext . '_temporary_container';
    $full_file_path = $path_to_file.$filename;

    $this->cmd = "docker run -i --rm --volumes-from $application_volume:ro " .
    "--cpu-shares=$cpu_share --name $container_name $image_name " .
    $full_file_path . " /opt/android-sdk-linux/platforms " .
    $flowdroid_extra_arg;

    $this->cmd_on_timeout = "docker stop $container_name";
  }

  public function parseResult() {
    $start_index = -1;
    $stop_index = -1;
    $count = 0;

    $db_record = [];
    $issues = [];

    $hay_stack = explode(PHP_EOL, $this->stdout);

    $start_rule = "/Found a flow to sink/";
    $end_rule = "/Maximum memory consumption/";

    // Find the beginning and ending of the output
    foreach ($hay_stack as $key => $value) {

      if(($start_index == -1) && preg_match($start_rule, $value)) {
        $start_index = $key;
      } else if(($stop_index == -1) && preg_match($end_rule, $value)) {
        $stop_index = $key;
      }

      if(preg_match($start_rule, $value)) {
        $count++;
      }

    }

    // Copy output
    if($count == 0 || $start_index == -1 || $stop_index == -1) {
      $issues[] = "No flows found.";
    } else {
      for($i = $start_index; $i < $stop_index; $i++) {
        $issues[] = trim(str_replace(array("\r\n", "\r", "\n", " - ", "- "), '', $hay_stack[$i]));
      }
      array_unshift($issues, 'Number of flows detected: ' . $count . PHP_EOL);
    }

    if( isset($count) && ($count > 0) ) {
      $db_record['no_flow'] = $count;
      $db_record['vulnerable_leak'] = 1;
    }

    $issues['raw'] = $this->stdout;
    $this->parsedResultDBEntry = $db_record;
    $this->parseResult = $issues;
  }
}
