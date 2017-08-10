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
use voku\helper\HtmlDomParser;

class Qark extends Tool {

  public function makeCmd($path_to_file, $filename) {
    $image_name = env('QARK_IMAGE_NAME', 'secureappstore_argus');
    $application_volume = env('APPLICATION_VOLUME_CONTAINER_NAME', 'secureappstore_applications_1');
    $cpu_share = env('ANALYSIS_TOOLS_CPU_SHARES', '950');

    $filename_without_ext = basename($filename, '.apk');
    $container_name = $filename_without_ext . '_temporary_container';
    $full_file_path = $path_to_file.$filename;

    $this->cmd = "docker run -i --rm --volumes-from $application_volume:ro " .
    "--cpu-shares=$cpu_share --name $container_name $image_name " .
    $full_file_path . " " . $filename;

    $this->cmd_on_timeout = "docker stop $container_name";
  }

  public function parseResult() {

    $output = explode("---- HTML ----\n", $this->stdout);

    $html = HtmlDomParser::str_get_html($output[1]);

    $db_record = [];
    $issues = [];

    // Find vulnerability
    $use_weak_cryptographic_algorithms = '/getInstance should not be called with ECB as the cipher mode, as it is insecure.|getInstance should not be called without setting the cipher mode because the default mode on android is ECB, which is insecure. /';
    $implement_certificate_pinning = '/It appears there is a private key embedded in your application in the following file:/';
    $bad_ssl_implementation = '/Instance of checkServerTrusted, with no body found in|Instance of checkServerTrusted, which only returns|Custom verify method used in|Potential Man-In-The-Middle vulnerability/';
    $data_leakage = '/A broadcast is sent from this class:|A broadcast, as a specific user, is sent from this class:|An ordered broadcast, as a specific user, is sent from this class:|A sticky broadcast is sent from this class:|A sticky ordered broadcast is sent from this class:/';
    $hardcoded_api_key = "/The string 'API_KEY' appears in the file:/";
    $hardcoded_password = "/The string 'pass' appears in the file/";
    $vulnerable_to_privilege_escalation_attack = "/PendingIntent created with implicit intent.|Implicit Intent:/";

    // Get vulnerability count
    $vuln_count = $html->find('h1[id="vulnerability_count"]', 0)->plaintext;
    $warning_count = $html->find('h1[id="warning_count"]', 0)->plaintext;
    $info_count = $html->find('h1[id="vulnerability_count"]', 0)->plaintext;

    $result['vulnerability_count'] = trim($vuln_count);
    $result['warning_count'] = trim($warning_count);
    $result['information_count'] = trim($info_count);

    // Get details analysis
    $find_id = 'appcomponents|webviews|x509|filepermission|pendingintent|crypto|osbugs';
    $find_id = explode('|', $find_id);

    foreach ($find_id as $id):
      $outter_div = $html->find('div[id='.$id.']');
      $i = 0;

      foreach ($outter_div as $div):
        $j = 0;
        $result[$id][$i]['info'] = trim($div->find('strong', 0)->plaintext);

        foreach ($div->find('li') as $li):
          $result[$id][$i][$j] = trim($li->plaintext);
          $j++;
        endforeach; // li

        $i++;
      endforeach; // div

    endforeach; // id

    // Intepret results

    $db_record['weak_crypto'] = preg_match($use_weak_cryptographic_algorithms, $output[1]);

    $db_record['vulnerability_count'] = $result['vulnerability_count'];
    $db_record['warning_count'] = $result['warning_count'];
    $db_record['information_count'] = $result['information_count'];

    $issues[] = 'Vulnerability detect: ' . $db_record['vulnerability_count'];
    $issues[] = 'Warning : ' . $db_record['vulnerability_count'];
    $issues[] = 'Information: ' . $db_record['vulnerability_count'];
    $issues[] = 'Please refer to QARK output for more information' . PHP_EOL;

    if(preg_match($implement_certificate_pinning, $output[1])) {
      $db_record['cert_pinning_mitm'] = 1;
      $db_record['cert_pinning_mitm_cvss'] = 5.9;

      $issues[] = 'The application allows man-in-the-middle attackers to bypass certificate pinning' . PHP_EOL .
      'It is done by sending a certificate chain with a certificate from a non-pinned trusted CA and the pinned certificate.' . PHP_EOL .
      'CVSS score at least 5.9 (Medium)' . PHP_EOL .
      'CVSS:3.0/AV:N/AC:H/PR:N/UI:N/S:U/C:N/I:H/A:N' . PHP_EOL .
      'Reference: https://nvd.nist.gov/vuln/detail/CVE-2016-2402, https://www.synopsys.com/blogs/software-security/ineffective-certificate-pinning-implementations/, https://koz.io/pinning-cve-2016-2402/';
      }

      if(preg_match($bad_ssl_implementation, $output[1])) {
        $db_record['mitm'] = 1;
        $db_record['mitm_cvss'] = 5.9;

        $issues[] = 'The app fails to properly validate SSL certificates. ' . PHP_EOL .
        'It may be vulnerable to man-in-the-middle attack.' . PHP_EOL .
        'CVSS score at least 5.9 (Medium)' . PHP_EOL .
        'CVSS:3.0/AV:N/AC:H/PR:N/UI:N/S:U/C:H/I:N/A:N' . PHP_EOL .
        'Reference: https://nvd.nist.gov/vuln/detail/CVE-2017-2103';
      }

      $db_record['vulnerable_leak'] = preg_match($data_leakage, $output[1]);

      if(preg_match($hardcoded_api_key, $output[1])) {
        $db_record['api_key'] = 1;

        $issues[] = 'Detected API key(s) hardcoded in the application.' . PHP_EOL .
        'It may allow remote attackers to inject arbitrary information via the API Key';
      }

      if(preg_match($hardcoded_password, $output[1])) {
        $db_record['password'] = 1;

        $issues[] = 'Detected password(s) hardcoded in the application.' . PHP_EOL .
        'It may allow remote attackers to use this credential to exploit the server';
      }

      if(preg_match($vulnerable_to_privilege_escalation_attack, $output[1])) {
        $db_record['privilege_escalation'] = 1;
        $db_record['privilege_escalation_cvss'] = 6.1;

        $issues[] = 'The app is vulnerable to privilege escalation attack, Allows complete confidentiality, integrity,' . PHP_EOL .
        'and availability violation.' . PHP_EOL .
        'Temporary CVSS:3.0 score at least 6.1 (Medium)' . PHP_EOL .
        'CVSS:2.0 score at least 7.2 (High)' . PHP_EOL .
        'CVSS:2.0/AV:L/AC:L/Au:N/C:C/I:C/A:C' . PHP_EOL .
        'Reference: https://nvd.nist.gov/vuln/detail/CVE-2014-8609';
      }

      $hay_stack = explode(PHP_EOL, $output[0]);

      $issues = array_merge($issues, preg_grep($use_weak_cryptographic_algorithms, $hay_stack));
      $issues = array_merge($issues, preg_grep($implement_certificate_pinning, $hay_stack));
      $issues = array_merge($issues, preg_grep($bad_ssl_implementation, $hay_stack));
      $issues = array_merge($issues, preg_grep($data_leakage, $hay_stack));
      $issues = array_merge($issues, preg_grep($hardcoded_api_key, $hay_stack));
      $issues = array_merge($issues, preg_grep($vulnerable_to_privilege_escalation_attack, $hay_stack));

      $issues = array_merge($issues, preg_grep('/VULNERABILITY -/', $hay_stack));
      $issues = array_merge($issues, preg_grep('/ISSUES - /', $hay_stack));
      $issues = array_merge($issues, preg_grep('/WARNING - /', $hay_stack));

      $issues['raw'] = $output[0];
      $this->parsedResultDBEntry = $db_record;
      $this->parseResult = $issues;
    }
  }
