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

class ArgusTaint extends Tool {

  public function makeCmd($path_to_file, $filename) {
    $image_name = env('ARGUS_IMAGE_NAME', 'secureappstore_argus');
    $application_volume = env('APPLICATION_VOLUME_CONTAINER_NAME', 'secureappstore_applications_1');
    $cpu_share = env('ANALYSIS_TOOLS_CPU_SHARES', '950');

    $filename_without_ext = basename($filename, '.apk');
    $container_name = $filename_without_ext . '_temporary_container';
    $full_file_path = $path_to_file.$filename;

    $this->cmd = "docker run -i --rm --volumes-from $application_volume:ro " .
    "--cpu-shares=$cpu_share --name $container_name $image_name " .
    "t -o /tmp " . $full_file_path;

    // $this->cmd .= " && docker run -i --rm --volumes-from $application_volume:ro " .
    // "--cpu-shares=$cpu_share --name $container_name $image_name " .
    // "t -mo PASSWORD_TRACKING -o /tmp " . $full_file_path;
    //
    // $this->cmd .= " && docker run -i --rm --volumes-from $application_volume:ro " .
    // "--cpu-shares=$cpu_share --name $container_name $image_name " .
    // "t -mo COMMUNICATION_LEAKAGE -o /tmp " . $full_file_path;
    //
    // $this->cmd .= " && docker run -i --rm --volumes-from $application_volume:ro " .
    // "--cpu-shares=$cpu_share --name $container_name $image_name " .
    // "t -mo INTENT_INJECTION -o /tmp " . $full_file_path;
    //
    // $this->cmd .= " && docker run -i --rm --volumes-from $application_volume:ro " .
    // "--cpu-shares=$cpu_share --name $container_name $image_name " .
    // "t -mo OAUTH_TOKEN_TRACKING -o /tmp " . $full_file_path;

    $this->cmd_on_timeout = "docker stop $container_name";
  }

  public function parseResult() {
    $data_leakage = "/vulnerability:information_leak|vulnerability:capability_leak|vulnerability:confused_deputy/";
    $data_theft = "/maliciousness:information_theft/";

    $db_record = [];
    $issues = [];

    $hay_stack = explode(PHP_EOL, $this->stdout);

    foreach ($hay_stack as $key => $value) {
      $hay_stack[$key] = $value;
    }

    $issues = array_merge($issues, preg_grep($data_leakage, $hay_stack));
    $issues = array_merge($issues, preg_grep($data_theft, $hay_stack));

    $db_record['vulnerable_leak'] = preg_match($data_leakage, $this->stdout);
    $db_record['malicious_leak'] = preg_match($data_theft, $this->stdout);

    if(count($issues) == 0) {
      $issues[] = "No issues found.";
    }

    $issues['raw'] = $this->stdout;
    $this->parsedResultDBEntry = $db_record;
    $this->parseResult = $issues;
  }
}
