<?php

/**
* Secure Android App Store's Tool wrapper abstract class
*
* This is an wrapper class for containerized tools we used in the
* project. It provides an easy way to integrate several tools which
* have different commands structures and allow the worker to easily invoke
* the command.
*
* @copyright  Copyright (c) 2017 Rom Luengwattanapong (s1567783@ed.ac.uk)
*/

namespace App\SecureAppStore\Tools;

use Exception;
use Symfony\Component\Process\Process;
use Carbon\Carbon;

use Log;

class Tool {

  // Name of the test
  protected $testName;

  // Command to run the test
  protected $cmd;

  // Commnad to stop the test, in casse of timeout
  protected $cmd_on_timeout;

  // Output
  protected $stdout;

  // Parsed results and its corresponding database records
  protected $parsedResult;
  protected $parsedResultDBEntry;

  /**
  * Construct a new Tool object
  *
  * @return Tool object
  */
  public function __construct($testName) {
    $this->testName = $testName;
    $this->parsedResult = null;
    $this->parsedResultDBEntry = null;
  }

  /**
  * Create new commands to run the containerized tools
  *
  * @param image_name Tool's image
  * @param path_to_apk Path to APK
  * @param filename APK filename
  * @param extra_option Array of extra options
  * @return none
  */
  public function makeCmd($image_name, $path_to_file, $filename, $extra_option = null) {
    $application_volume = env('APPLICATION_VOLUME_CONTAINER_NAME', 'secureappstore_applications_1');
    $cpu_share = env('ANALYSIS_TOOLS_CPU_SHARES', '950');

    $filename_without_ext = basename($filename, '.apk');
    $container_name = $filename_without_ext . '_temporary_container';
    $full_file_path = $path_to_file.$filename;

    $this->cmd = "docker run -i --rm --volumes-from $application_volume:ro " .
    "--cpu-shares=$cpu_share --name $container_name $image_name " .
    "--filepath " . $full_file_path . " ";

    $this->cmd_on_timeout = "docker stop $container_name";

    if($extra_option !== null) {
      $this->cmd .= "--option \"". addslashes(json_encode($extra_option)) ."\"";
    }

  }

  /**
  * Run commands to invoke the analysis process, or run the command in
  * cmd_on_timeout in case of timeout.
  *
  * @param Carbon time limit
  * @throws exception Time limit reached
  * @throws exception Command failed
  * @return void
  */
  public function run(Carbon $timeLimit) {

    // Check if time is up or not
    if ($timeLimit->lt(Carbon::now())) {
      Log::error('[Tool] Time limit passed.');
      throw new Exception('[Tool] Time limit passed');
    }

    // Start an asynchronus process
    $timeout = Carbon::now()->diffInSeconds($timeLimit, false);
    $this->cmd = $this->cmd . " --timeout $timeout";

    Log::debug('[Tool] Running command: ' . $this->cmd);

    $process = new Process($this->cmd);
    $process->start();

    // Wait for time limit
    while( ($timeLimit->gte(Carbon::now())) && $process->isRunning()) {
      sleep(10);
    };

    // Throws error if time limit is reached
    if( ($timeLimit->lt(Carbon::now())) && $process->isRunning()) {
      // Time limit reached
      $process_timeout = new Process($this->cmd_on_timeout);

      try {
        $process_timeout->start();
      } catch(Exception $e) {
        unset($e);
        throw new Exception('[Tool] Error executing command on timeout.');
      }

      Log::info('[Tool] Time task failed: ' . $timeLimit->diffInSeconds(Carbon::now()));
      Log::error($process->getOutput());
      Log::error($process->getErrorOutput());

      throw new Exception('[Tool] Time limit passed');
    }

    // If the command failed
    if(!$process->isSuccessful()) {
      Log::error('[Tool] Unexpected exit. Test - ' . $this->testName . '.');
      Log::error($process->getOutput());
      Log::error($process->getErrorOutput());
      throw new Exception('[Tool] The command failed!');
    }

    // Save the stdout
    $stdout = json_decode(rtrim($process->getOutput(), "\0"), true);

    // If the command failed
    if((json_last_error() != JSON_ERROR_NONE) || !isset($stdout['db']) || !isset($stdout['raw'])) {
      Log::error('[Tool] Malformed return - ' . $this->testName . '.');
      Log::error($process->getOutput());
      Log::error($stdout);
      throw new Exception('[Tool] Malformed return!');
    }

    $this->stdout = $stdout;
    $this->parsedResultDBEntry = $stdout['db'];
    unset($stdout['db']);
    $this->parsedResult = $stdout;
  }

  /**
  * Return test name
  *
  * @return string test name
  */
  public function getTestName() {
    return $this->testName;
  }

  /**
  * Return parsed result
  *
  * @return array parsed results
  */
  public function getParsedResult() {
    return $this->parsedResult;
  }

  /**
  * Return parsed result to be inserted into DB
  *
  * @return array parsed results
  */
  public function getParsedResultDBEntry() {
    return $this->parsedResultDBEntry;
  }
}
