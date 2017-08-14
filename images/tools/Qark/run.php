#! /usr/bin/php
<?php

$command =  'script --return -c "/usr/bin/python qarkMain.py ' .
'--source 1 --pathtoapk [FULL_PATH_TO_APK] --exploit 0 ' .
'--install 0 --basesdk /opt/android-sdk-linux --reportdir /tmp" /dev/null';
$working_dir = '/opt/qark/qark';
$refresh_every_sec = 5;

if (php_sapi_name() != "cli") {
  // Not in cli-mode
  exit(100);
}

/**
*  Get command-line arguments
*/
$longopts = array(
  "filepath:",    // filepath
  "timeout:",     // timeout
  "option:",        // No value
);

$options = getopt(NULL, $longopts);

// Copy the specimen to tmp folder
$filename = basename($options['filepath']);
copy($options['filepath'], '/tmp/'.$filename);
$command = str_replace("[FULL_PATH_TO_APK]", '/tmp/'.$filename, $command);

/**
*  Run the process
*/
$pipes = [];
$timer = time() + $options['timeout'];
$output = '';

$descriptorspec = array(
  0 => array("pipe", "r"),  // stdin
  1 => array("pipe", "w"),  // stdout
  2 => array("pipe", "w")   // stderr
);

$process = proc_open('exec ' . $command, $descriptorspec, $pipes, $working_dir);
$pid = proc_get_status($process)["pid"];

while(($timer >= time()) && !feof($pipes[1])) {
  // Wait for the process or the timer
  sleep ($refresh_every_sec);
  $output .= preg_replace('/[^\PC\s]/u', '', fread($pipes[1], 8192));
}

// Time limit is reached
if(($timer <= time()) && !feof($pipes[1])) {
  proc_terminate($process);
  fclose($pipes[1]); //stdout
  fclose($pipes[2]); //stderr
  proc_close($process);
  echo "Time limit reached." . PHP_EOL;
  exit(98);
}

$status = proc_get_status($process);

// Process exits with errors
if($status['exitcode'] != 0) {
  echo '**** ERROR ****'.PHP_EOL;
  echo stream_get_contents($pipes[2]);
  fclose($pipes[1]); //stdout
  fclose($pipes[2]); //stderr
  proc_close($process);

  exit(1);
}

fclose($pipes[1]); //stdout
fclose($pipes[2]); //stderr
proc_close($process);

/**
*  Parsed output
*/
$parsed_output = [];
$db_record = [];

$hay_stack = explode(PHP_EOL, $output);

$vuln = preg_grep('/VULNERABILITY -/', $hay_stack);
$warning = preg_grep('/WARNING - /', $hay_stack);
$issue = preg_grep('/ISSUES - /', $hay_stack);

$parsed_output[] = 'Vulnerability detect: ' . count($vuln);
$parsed_output[] = 'Warning : ' . count($warning);
$parsed_output[] = 'Information: ' . count($issue);
$parsed_output[] = 'Please refer to QARK output for more information' . PHP_EOL;

$db_record['vulnerability_count'] = count($vuln);
$db_record['warning_count'] = count($warning);
$db_record['information_count'] = count($issue);

$weak_crypto = '/getInstance should not be called with ECB as the cipher mode, as it is insecure.|getInstance should not be called without setting the cipher mode because the default mode on android is ECB, which is insecure. /';
$cert_pinning = '/It appears there is a private key embedded in your application in the following file:/';
$bad_cert_verification = '/Instance of checkServerTrusted, with no body found in|Instance of checkServerTrusted, which only returns|Custom verify method used in|Potential Man-In-The-Middle vulnerability/';
$data_leakage = '/A broadcast is sent from this class:|A broadcast, as a specific user, is sent from this class:|An ordered broadcast, as a specific user, is sent from this class:|A sticky broadcast is sent from this class:|A sticky ordered broadcast is sent from this class:/';
$hardcoded_api_key = "/The string 'API_KEY' appears in the file:/";
$hardcoded_password = "/The string 'pass' appears in the file/";
$pending_intent = "/PendingIntent created with implicit intent.|Implicit Intent:/";

$db_record['weak_crypto'] = preg_match($weak_crypto, $output);

if(preg_match($cert_pinning, $output)) {
  $db_record['cert_pinning_mitm'] = 1;
  $db_record['cert_pinning_mitm_cvss'] = 5.9;

  $parsed_output[] = 'The application is vulnerable to man-in-the-middle attack '.
  'by bypassing certificate pinning' . PHP_EOL .
  'It is done by sending a certificate chain with a certificate from a '.
  'non-pinned trusted CA and the pinned certificate.' . PHP_EOL .
  'CVSS score at least 5.9 (Medium)' . PHP_EOL .
  'CVSS:3.0/AV:N/AC:H/PR:N/UI:N/S:U/C:N/I:H/A:N' . PHP_EOL .
  'Reference: https://nvd.nist.gov/vuln/detail/CVE-2016-2402, ' .
  'https://www.synopsys.com/blogs/software-security/ineffective-certificate-pinning-implementations/, ' .
  'https://koz.io/pinning-cve-2016-2402/'. PHP_EOL;
}

if(preg_match($bad_cert_verification, $output)) {
  $db_record['mitm'] = 1;
  $db_record['mitm_cvss'] = 5.9;

  $parsed_output[] = 'The app fails to properly validate SSL certificates. ' . PHP_EOL .
  'It may be vulnerable to man-in-the-middle attack.' . PHP_EOL .
  'CVSS score at least 5.9 (Medium)' . PHP_EOL .
  'CVSS:3.0/AV:N/AC:H/PR:N/UI:N/S:U/C:H/I:N/A:N' . PHP_EOL .
  'Reference: https://nvd.nist.gov/vuln/detail/CVE-2017-2103'. PHP_EOL;
}

$db_record['vulnerable_leak'] = preg_match($data_leakage, $output);

if(preg_match($hardcoded_api_key, $output)) {
  $db_record['api_key'] = 1;

  $parsed_output[] = 'Detected API key(s) hardcoded in the application.' . PHP_EOL .
  'It may allow remote attackers to inject arbitrary information' .
  'via the API Key'. PHP_EOL;
}

if(preg_match($hardcoded_password, $output)) {
  $db_record['password'] = 1;

  $parsed_output[] = 'Detected password(s) hardcoded in the application.' . PHP_EOL .
  'It may allow remote attackers to use this credential to exploit the server';
}

if(preg_match($pending_intent, $output)) {
  $db_record['privilege_escalation'] = 1;
  $db_record['privilege_escalation_cvss'] = 6.1;

  $parsed_output[] = 'The app is vulnerable to privilege escalation attack, ' .
  'Allows complete confidentiality, integrity,' . PHP_EOL .
  'and availability violation.' . PHP_EOL .
  'Temporary CVSS:3.0 score at least 6.1 (Medium)' . PHP_EOL .
  'CVSS:2.0 score at least 7.2 (High)' . PHP_EOL .
  'CVSS:2.0/AV:L/AC:L/Au:N/C:C/I:C/A:C' . PHP_EOL .
  'Reference: https://nvd.nist.gov/vuln/detail/CVE-2014-8609' . PHP_EOL;
}

$parsed_output = array_merge($parsed_output, $vuln);
$parsed_output = array_merge($parsed_output, $warning);
$parsed_output = array_merge($parsed_output, $issue);

$parsed_output['raw'] = $output;
$parsed_output['db'] = $db_record;

// Print out result
echo json_encode($parsed_output);
exit(0);
?>
