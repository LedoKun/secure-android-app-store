#! /usr/bin/php
<?php

$command = <<<EOL
/usr/bin/java \
-Xmx16g \
-jar argus-saf-assembly.jar \
[API_OR_TAINT] \
-o /tmp \
[FULL_PATH_TO_APK]
EOL;

$working_dir = '/opt/argus';
$refresh_every_sec = 5;

if (php_sapi_name() != 'cli') {
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

// Convert Extra option from JSON to arguments
if(isset($options['option']) && strlen($options['option']) > 0) {
  $extra_arguments = json_decode(rtrim($options['option'], "\0"), true);
  $api_or_taint = $extra_arguments['api_or_taint'];
  $command = str_replace("[API_OR_TAINT]", $api_or_taint, $command);
}

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
  $output .= fread($pipes[1], 8192);
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

$broken_SSL_implementation = '/Use bad TrustManager!|Use bad SSLSocketFactory!|Using wrong SSL hostname configuration!/';
$hide_app_icon = '/Hide app icon./';
$use_weak_cryptographic_algorithms = '/Use ECB mode!|Use non-random IV!/';
$data_leakage = "/vulnerability:information_leak|vulnerability:capability_leak|vulnerability:confused_deputy/";
$data_theft = "/maliciousness:information_theft/";

if(preg_match($broken_SSL_implementation, $output)) {
  $db_record['mitm'] = 1;
  $db_record['mitm_cvss'] = 5.9;

  $parsed_output[] = 'The app fails to validate SSL certificates properly. ' .
  'It may be vulnerable to man-in-the-middle attack.' .
  'CVSS score at least 5.9 - Medium ' .
  '(CVSS:3.0/AV:N/AC:H/PR:N/UI:N/S:U/C:H/I:N/A:N)' .
  'Reference: https://nvd.nist.gov/vuln/detail/CVE-2017-2103' . PHP_EOL;
}

if(preg_match($hide_app_icon, $output)) {
  $db_record['hide_app_icon'] = 1;
  $parsed_output[] = 'This app hides its icon after installation.' . PHP_EOL;
}

if(preg_match($use_weak_cryptographic_algorithms, $output)) {
  $db_record['weak_crypto'] = 1;
  $parsed_output[] = 'This app uses weak cryptographic scheme. '.
  'Please refer to Argus-SAF output for more information' . PHP_EOL;
}

$hay_stack = explode(PHP_EOL, $output);

$parsed_output = array_merge($parsed_output, preg_grep($data_leakage, $hay_stack));
$parsed_output = array_merge($parsed_output, preg_grep($data_theft, $hay_stack));

$db_record['vulnerable_leak'] = preg_match($data_leakage, $output);
$db_record['malicious_leak'] = preg_match($data_theft, $output);

if(count($parsed_output) == 0) {
  $parsed_output[] = "No issues found.";
}

$parsed_output['raw'] = $output;
$parsed_output['db'] = $db_record;

// Print out result
echo json_encode($parsed_output);
exit(0);
?>
