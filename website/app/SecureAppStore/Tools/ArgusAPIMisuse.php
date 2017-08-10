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

class ArgusAPIMisuse extends Tool {

  public function makeCmd($path_to_file, $filename) {
    $image_name = env('ARGUS_IMAGE_NAME', 'secureappstore_argus');
    $application_volume = env('APPLICATION_VOLUME_CONTAINER_NAME', 'secureappstore_applications_1');
    $cpu_share = env('ANALYSIS_TOOLS_CPU_SHARES', '950');

    $filename_without_ext = basename($filename, '.apk');
    $container_name = $filename_without_ext . '_temporary_container';
    $full_file_path = $path_to_file.$filename;

    $this->cmd = "docker run -i --rm --volumes-from $application_volume:ro " .
    "--cpu-shares=$cpu_share --name $container_name $image_name " .
    "a -o /tmp " . $full_file_path;

    $this->cmd_on_timeout = "docker stop $container_name";
  }

  public function parseResult() {

    $broken_SSL_implementation = '/Use bad TrustManager!|Use bad SSLSocketFactory!|Using wrong SSL hostname configuration!/';
    $hide_app_icon = '/Hide app icon./';
    $use_weak_cryptographic_algorithms = '/Use ECB mode!|Use non-random IV!/';

    $db_record = [];
    $issues = [];

    if(preg_match($broken_SSL_implementation, $this->stdout)) {
      $db_record['mitm'] = 1;
      $db_record['mitm_cvss'] = 5.9;

      $issues[] = 'The app fails to validate SSL certificates properly. ' .
      'It may be vulnerable to man-in-the-middle attack.' .
      'CVSS score at least 5.9 - Medium ' .
      '(CVSS:3.0/AV:N/AC:H/PR:N/UI:N/S:U/C:H/I:N/A:N)' .
      'Reference: https://nvd.nist.gov/vuln/detail/CVE-2017-2103';
    }

    if(preg_match($hide_app_icon, $this->stdout)) {
      $db_record['hide_app_icon'] = 1;
      $issues[] = 'This app hides its icon after installation.';
    }

    if(preg_match($use_weak_cryptographic_algorithms, $this->stdout)) {
      $db_record['weak_crypto'] = 1;
      $issues[] = 'This app uses weak cryptographic scheme. Please refer to Argus-SAF output for more information';
    }

    if(count($issues) == 0) {
      $issues[] = "No issues found.";
    }

    $issues['raw'] = $this->stdout;
    $this->parsedResultDBEntry = $db_record;
    $this->parseResult = $issues;
  }
}
