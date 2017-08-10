<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class AnalysisToolDefaultRule extends Model
{
  protected $fillable = array('rule_id');

  public static function getDefault() {
    return DB::table('analysis_tool_default_rules AS default')
    ->join("analysis_tool_settings AS settings", "default.rule_id", "=", "settings.id")
    ->select(['default.id' ,'default.rule_id', 'settings.rule_name',
    'settings.api_misuse', 'settings.vulnerability_scan', 'settings.custom_policy',
    'settings.custom_policy', 'settings.taint_analysis', 'default.created_at'])
    ->orderBy('default.created_at', 'desc');
  }

  public static function getDefaultRuleID() {
    return static::select('rule_id');
  }

  public static function listHistory() {
    return DB::table('analysis_tool_default_rules AS default')
    ->join("analysis_tool_settings AS settings", "default.rule_id", "=", "settings.id")
    ->select(['default.id' ,'default.rule_id', 'settings.rule_name', 'settings.api_misuse', 'settings.vulnerability_scan',
    'settings.custom_policy', 'settings.custom_policy', 'settings.taint_analysis', 'default.created_at'])
    ->orderBy('default.created_at', 'desc');
  }
}
