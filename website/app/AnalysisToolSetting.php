<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnalysisToolSetting extends Model
{

  /*
  |--------------------------------------------------------------------------
  | Analysis Tool Setting Model
  |--------------------------------------------------------------------------
  |
  | This model is responsible for handling analysis results.
  |
  */
  protected $fillable = array('rule_name', 'comments', 'timeout',
  'api_misuse', 'vulnerability_scan', 'custom_policy',
  'taint_analysis', 'taint_aplength', 'taint_nocallbacks',
  'taint_sysflows', 'taint_implicit',
  'taint_static');

  /**
  * List tools configurations
  *
  * @return Illuminate\Support\Collection
  */
  public static function listRules() {

    return static::orderBy('updated_at', 'desc');

  }

}
