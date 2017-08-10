<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnalysisToolSetting extends Model
{
  protected $fillable = array('rule_name', 'comments', 'timeout',
  'api_misuse', 'vulnerability_scan', 'custom_policy',
  'taint_analysis', 'taint_aplength', 'taint_nocallbacks',
  'taint_sysflows', 'taint_implicit',
  'taint_static');

  public static function listRules() {

    return static::orderBy('updated_at', 'desc');

  }

}
