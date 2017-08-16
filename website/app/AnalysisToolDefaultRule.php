<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class AnalysisToolDefaultRule extends Model
{
  /*
  |--------------------------------------------------------------------------
  | Analysis Tool Default Rule Model
  |--------------------------------------------------------------------------
  |
  | This model is responsible for handling the default analysis rules.
  |
  */
  protected $fillable = array('rule_id');

  /**
  * Get the default rule
  *
  * @return Illuminate\Support\Collection
  */
  public static function getDefault() {
    return DB::table('analysis_tool_default_rules AS default')
    ->join("analysis_tool_settings AS settings", "default.rule_id", "=", "settings.id")
    ->select(['default.id' ,'default.rule_id', 'settings.rule_name',
    'settings.api_misuse', 'settings.vulnerability_scan', 'settings.custom_policy',
    'settings.custom_policy', 'settings.taint_analysis', 'default.created_at'])
    ->orderBy('default.created_at', 'desc');
  }

  /**
  * Get the ID of the default rule
  *
  * @return Illuminate\Support\Collection
  */
  public static function getDefaultRuleID() {
    return static::select('rule_id');
  }

  /**
  * List rule enforcement history
  *
  * @return Illuminate\Support\Collection
  */
  public static function listHistory() {
    return DB::table('analysis_tool_default_rules AS default')
    ->join("analysis_tool_settings AS settings", "default.rule_id", "=", "settings.id")
    ->select(['default.id' ,'default.rule_id', 'settings.rule_name', 'settings.api_misuse', 'settings.vulnerability_scan',
    'settings.custom_policy', 'settings.custom_policy', 'settings.taint_analysis', 'default.created_at'])
    ->orderBy('default.created_at', 'desc');
  }
}
