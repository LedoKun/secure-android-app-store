<?php

namespace App\SecureAppStore\APK;

use Log;

use Exception;
use ZipArchive;

class APK {

  private $apkFilename;

  private $parser;
  private $isValid;

  private $permissions;
  private $manifest;
  private $resources;
  private $appLabel;

  public function __construct($apkPath) {
    $this->apkPath = $apkPath;
    $this->parser = new \ApkParser\Parser($apkPath);

    // Read the apk file
    // Modified from https://github.com/evozi/apk-parser-php/blob/master/examples/ApkInfo.php
    // And https://github.com/tufanbarisyildirim/php-apk-parser/blob/ba5b29c1c9ea60a629604de103976638f5204d22/examples/ApkResource.php
    try {
      $this->manifest = $this->parser->getManifest();
      $this->permissions = $this->manifest->getPermissions();

      $this->isValid = 1;
    } catch (Exception $e) {
      $this->isValid = 0;
    } catch(FatalErrorException $e) {
      $this->isValid = 0;
    }

    if($this->isValid) {
      $resourceId = $this->manifest->getApplication()->getIcon();
      $this->resources = $this->parser->getResources($resourceId);

      $labelResourceId = $this->manifest->getApplication()->getLabel();
      $this->appLabel = $this->parser->getResources($labelResourceId);
    }
  }

  public function getFilename() {
    return $this->apkFilename;
  }

  public function getPath() {
    return $this->apkPath;
  }

  public function isValid() {
    return $this->isValid;
  }

  public function getPermissions() {
    return $this->permissions;
  }

  public function getManifest() {
    return $this->manifest;
  }

  public function getResources() {
    return $this->resources;
  }

  public function getAppLabel() {
    return $this->appLabel;
  }

  public function getStreamFirstResource() {
    return $this->parser->getStream($this->resources[0]);
  }

}
