#! /usr/bin/php
<?php

$command = <<<EOL
/usr/bin/python \
/opt/EviCheck/EviCheck.py \
-f [FULL_PATH_TO_APK] -g \
-p [FULL_PATH_TO_POLICY] -m
EOL;

$working_dir = '/opt/EviCheck';
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
  $extra_arguments = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $options['option']), true);
  $policy_path = $extra_arguments['policy'];

  $command = str_replace("[FULL_PATH_TO_POLICY]", '/tmp/policy/'.$policy_path, $command);
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

$rule = "/Number of violated rules:\s+\d+/";
$rule_extract_number = "/\d+/";
$rule_violated = "/^Policy violated!/";

$hay_stack = explode(PHP_EOL, $output);

$number_of_rules = preg_grep($rule, $hay_stack);

if(is_array($number_of_rules) && count($number_of_rules) > 0) {
  preg_match($rule_extract_number, end($number_of_rules), $tmp);
  $db_record['no_rules_broken'] = end($tmp);
  $parsed_output[] = $number_of_rules;
  $parsed_output[] = 'Please refer to EviCheck output for more information.' .
  PHP_EOL;

  $details = preg_grep($rule_violated, $hay_stack);
  $parsed_output = array_merge($parsed_output, $details);
} else {
  $db_record['no_rules_broken'] = 0;
  $parsed_output[] = 'No rules violated.';
}

$parsed_output['raw'] = $output;
$parsed_output['db'] = $db_record;

// Print out result
echo json_encode($parsed_output);
exit(0);
?>
