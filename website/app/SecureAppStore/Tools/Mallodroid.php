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

class Mallodroid extends Tool {

  public function makeCmd($path_to_file, $filename) {
    $image_name = env('MALLODROID_IMAGE_NAME', 'secureappstore_argus');
    $application_volume = env('APPLICATION_VOLUME_CONTAINER_NAME', 'secureappstore_applications_1');
    $cpu_share = env('ANALYSIS_TOOLS_CPU_SHARES', '950');

    $filename_without_ext = basename($filename, '.apk');
    $container_name = $filename_without_ext . '_temporary_container';
    $full_file_path = $path_to_file.$filename;

    $this->cmd = "docker run -i --rm --volumes-from $application_volume:ro " .
    "--cpu-shares=$cpu_share --name $container_name $image_name " .
    "-f " . $full_file_path;

    $this->cmd_on_timeout = "docker stop $container_name";
  }

  public function parseResult() {

    $start_hay_stack = '/Analysis result:/';
    $start_key = -1;

    $db_record = [];
    $issues = [];

    $broken_TrustManager_implementation = '/App implements custom TrustManager:|App implements (.*) custom TrustManagers(.*)/';
    $implements_insecure_SSLSocketFactory = '/App instantiates insecure SSLSocketFactory:|App instantiates (.*) insecure SSLSocketFactorys(.*)/';
    $implements_custom_HostnameVerifier = '/App implements custom HostnameVerifier:|App implements (.*) custom HostnameVerifiers(.*)/';
    $implements_AllowAllHostnameVerifier_verifier = '/App instantiates AllowAllHostnameVerifier:|App instantiates (.*) AllowAllHostnameVerifiers(.*)/';

    $hay_stack = explode(PHP_EOL, $this->stdout);

    if(preg_match($broken_TrustManager_implementation, $this->stdout) |
    preg_match($implements_insecure_SSLSocketFactory, $this->stdout) |
    preg_match($implements_custom_HostnameVerifier, $this->stdout) |
    preg_match($implements_AllowAllHostnameVerifier_verifier, $this->stdout)) {
      $db_record['mitm'] = 1;
      $db_record['mitm_cvss'] = 5.9;

      $issues[] = 'The app fails to properly validate SSL certificates. ' .
      'It may be vulnerable to man-in-the-middle attack.' .
      'CVSS score at least 5.9 - Medium ' .
      '(CVSS:3.0/AV:N/AC:H/PR:N/UI:N/S:U/C:H/I:N/A:N). ' .
      'Reference: https://nvd.nist.gov/vuln/detail/CVE-2017-2103, ' .
      'https://rt-solutions.de/en/2017/01/cve-2017-5589_xmpp_carbons/.';
    }

    foreach($hay_stack as $key => $value) {

      if(preg_match($start_hay_stack, $value)) {
        $start_key = $key;
        break;
      }

    }

    if($start_key != -1) {
      for($i = $start_key+2; $i < count($hay_stack); $i++) {
        $issues[] = $hay_stack[$key];
      }
    }

    if(count($issues) == 0) {
      $issues[] = "No issues found.";
    }

    $issues['raw'] = $this->stdout;
    $this->parsedResultDBEntry = $db_record;
    $this->parseResult = $issues;
  }

}
