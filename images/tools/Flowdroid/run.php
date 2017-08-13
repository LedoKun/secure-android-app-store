#! /usr/bin/php
<?php

$command = <<<EOL
java -Xmx16g -cp \
soot-trunk.jar:\
soot-infoflow.jar:\
soot-infoflow-android.jar:\
slf4j-api-1.7.5.jar:\
slf4j-simple-1.7.5.jar:\
axml-2.0.jar \
soot.jimple.infoflow.android.TestApps.Test \
[FULL_PATH_TO_APK] \
/opt/android-sdk-linux/platforms
EOL;

$working_dir = '/opt/flowdroid';
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
  foreach ($extra_arguments as $value) {
    $command .= ' --$value';
  }
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

$start_index = -1;
$stop_index = -1;
$count = 0;

$hay_stack = explode(PHP_EOL, $output);

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
  $parsed_output[] = "No flows found.";
} else {
  for($i = $start_index; $i < $stop_index; $i++) {
    $parsed_output[] = trim(str_replace(array("\r\n", "\r", "\n", " - ", "- "), '', $hay_stack[$i]));
  }
  array_unshift($parsed_output, 'Number of flows detected: ' . $count . PHP_EOL);
}

if( isset($count) && ($count > 0) ) {
  $db_record['no_flow'] = $count;
  $db_record['vulnerable_leak'] = 1;
}

$parsed_output['raw'] = $output;
$parsed_output['db'] = $db_record;

// Print out result
echo json_encode($parsed_output);
exit(0);
?>
