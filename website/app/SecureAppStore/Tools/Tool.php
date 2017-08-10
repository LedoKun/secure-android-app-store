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

abstract class Tool {

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
  * Parse analysis result from each tool.
  *
  * @return void
  */
  abstract public function parseResult();

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
    $this->stdout = $process->getOutput();
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
    return $this->parseResult;
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
