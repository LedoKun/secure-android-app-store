<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UploadApp extends Model
{

  protected $guarded = array('id', 'updated_at', 'created_at',
  'isBeingAnalyzed', 'isFailedAnalysis', 'attempts', 'isAnalyzed');

  public static function selectAppHash($hash) {

    return static::where('sha256', '=', $hash);

  }

  public static function selectAppID($id) {

    return static::where('id', '=', $id);

  }

  public static function selectPendingApp($id, $hash) {

    return static::where([
      ['id', '=', $id],
      ['sha256', '=', $hash],
      ]);

    }

  public static function selectAllPendingApps() {

    return static::where([
      ['isAnalyzed', '=', false],
      ])->orderBy('id', 'ASC');

  }

  public static function setBeingAnalyze($id, $vaule) {

    $update = static::findOrFail($id);
    $update->isBeingAnalyzed = $vaule;
    $update->save();

  }

  public static function setAnalyzed($id) {

    $update = static::findOrFail($id);
    $update->isAnalyzed = '1';
    $update->save();

  }

  public static function increaseAttempts($id) {

    static::findOrFail($id)->increment('attempts');

  }

  public static function increaseFailedAttempts($id) {

    static::findOrFail($id)->increment('isFailedAnalysis');

  }

}
