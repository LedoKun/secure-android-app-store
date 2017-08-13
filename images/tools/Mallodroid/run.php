#! /usr/bin/php
<?php

$command = './mallodroid.py -f [FULL_PATH_TO_APK] -x';
$working_dir = '/opt/mallodroid';
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

$start_needle = '/Analysis result:/';
$start_key = -1;

$needle = '/App implements custom TrustManager:|App implements (.*) custom TrustManagers(.*)';
$needle .= '|App instantiates insecure SSLSocketFactory:|App instantiates (.*) insecure SSLSocketFactorys(.*)';
$needle .= '|App implements custom HostnameVerifier:|App implements (.*) custom HostnameVerifiers(.*)';
$needle .= '|App instantiates AllowAllHostnameVerifier:|App instantiates (.*) AllowAllHostnameVerifiers(.*)/';

$hay_stack = explode(PHP_EOL, $output);

if(preg_match($needle, $output)) {
  $db_record['mitm'] = 1;
  $db_record['mitm_cvss'] = 5.9;

  $parsed_output[] = 'The app fails to properly validate SSL certificates. ' .
  'It may be vulnerable to man-in-the-middle attack.' .
  'CVSS score at least 5.9 - Medium ' .
  '(CVSS:3.0/AV:N/AC:H/PR:N/UI:N/S:U/C:H/I:N/A:N). ' .
  'Reference: https://nvd.nist.gov/vuln/detail/CVE-2017-2103, ' .
  'https://rt-solutions.de/en/2017/01/cve-2017-5589_xmpp_carbons/.' . PHP_EOL;
}

foreach($hay_stack as $key => $value) {
  if(preg_match($start_needle, $value)) {
    $start_key = $key;
    break;
  }
}

if($start_key != -1) {
  for($i = $start_key+2; $i < count($hay_stack); $i++) {
    $parsed_output[] = $hay_stack[$key];
  }
}

if(count($parsed_output) == 0) {
  $parsed_output[] = "No issues found.";
}

$parsed_output['raw'] = $output;
$parsed_output['db'] = $db_record;

// Print out result
echo json_encode($parsed_output);
exit(0);
?>
