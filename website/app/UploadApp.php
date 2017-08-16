<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UploadApp extends Model
{

  /*
  |--------------------------------------------------------------------------
  | Upload App Model
  |--------------------------------------------------------------------------
  |
  | This model is responsible for handling information of Android application.
  |
  */
  protected $guarded = array('id', 'updated_at', 'created_at',
  'isBeingAnalyzed', 'isFailedAnalysis', 'attempts', 'isAnalyzed');

  /**
  * Find Android application by SHA2556
  *
  * @param string Application SHA256
  * @return Illuminate\Support\Collection
  */
  public static function selectAppHash($hash) {

    return static::where('sha256', '=', $hash);

  }

  /**
  * Find Android application by ID
  *
  * @param integer Application ID
  * @return Illuminate\Support\Collection
  */
  public static function selectAppID($id) {

    return static::where('id', '=', $id);

  }

  /**
  * Find Android application by ID, and SHA256
  *
  * @param integer Application ID
  * @param string Application SHA256
  * @return Illuminate\Support\Collection
  */
  public static function selectPendingApp($id, $hash) {

    return static::where([
      ['id', '=', $id],
      ['sha256', '=', $hash],
    ]);

  }

  /**
  * Find Android applications that are pending for analysis
  *
  * @return Illuminate\Support\Collection
  */
  public static function selectAllPendingApps() {

    return static::where([
      ['isAnalyzed', '=', false],
      ])->orderBy('id', 'ASC');

    }

    /**
    * Find Android applications that are being analyzed
    *
    * @param integer Application ID
    * @param string Application SHA256
    * @return Illuminate\Support\Collection
    */
    public static function setBeingAnalyze($id, $vaule) {

      $update = static::findOrFail($id);
      $update->isBeingAnalyzed = $vaule;
      $update->save();

    }

    /**
    * Set the android application to analyzed
    *
    * @param integer Application ID
    */
    public static function setAnalyzed($id) {

      $update = static::findOrFail($id);
      $update->isAnalyzed = '1';
      $update->save();

    }

    /**
    * Increase the number of analysis attempt
    *
    * @param integer Application ID
    */
    public static function increaseAttempts($id) {

      static::findOrFail($id)->increment('attempts');

    }

    /**
    * Increase the number of failed attempt
    *
    * @param integer Application ID
    */
    public static function increaseFailedAttempts($id) {

      static::findOrFail($id)->increment('isFailedAnalysis');

    }

  }
