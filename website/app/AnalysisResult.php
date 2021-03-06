<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class AnalysisResult extends Model
{

  /*
  |--------------------------------------------------------------------------
  | Analysis Result Model
  |--------------------------------------------------------------------------
  |
  | This model is responsible for handling analysis results.
  |
  */
  protected $guarded = [
    'id', 'created_at', 'updated_at'
  ];

  /**
  * List all analysed applications
  *
  * @return Illuminate\Support\Collection
  */
  public static function listAnalysed() {

    $results = DB::table('upload_apps AS apps')
    ->join('analysis_results AS analysed', "apps.id", "=", "analysed.app_id")
    ->where('apps.isAnalyzed', '1')
    ->select(['apps.id as id', 'apps.sha256', 'apps.size', 'analysed.isVisible',
    'apps.created_at', 'apps.updated_at', 'apps.package_name'])
    ->orderBy('id');

    return $results;

  }

  /**
  * List all analysed and passed the publishing criteria applications
  *
  * @return Illuminate\Support\Collection
  */
  public static function listInStore() {

    $results = DB::table('upload_apps AS apps')
    ->join('analysis_results AS analysed', "apps.id", "=", "analysed.app_id")
    ->where([
      ['apps.isAnalyzed', '1'],
      ['analysed.isVisible', '1']
    ])
    ->select(['apps.id as id', 'apps.size', 'analysed.isVisible',
    'apps.created_at', 'apps.package_name', 'apps.apk_label',
    'apps.version', 'apps.min_sdk_platform', 'apps.filename'])
    ->orderBy('id');

    return $results;

  }

  /**
  * Get the analysis result
  *
  * @param integer $id
  * @return Illuminate\Support\Collection
  */
  public static function getAnalysisResult($id) {

    return static::where('app_id', $id);

  }

}
