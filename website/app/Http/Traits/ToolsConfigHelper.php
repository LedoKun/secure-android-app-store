<?php
namespace App\Traits;

trait ToolsConfigHelper
{
  public function disableableFields() {
    $taint = [
      'taint_aplength',
      'taint_nocallbacks',
      'taint_sysflows',
      'taint_implicit',
      'taint_static',
    ];

    return compact('taint');
  }

  public function formRules() {
    $rules = [
      'rule_name'                     => 'required',
      'comments'                      => 'nullable',

      'timeout'                       => 'required|integer|min:1',

      'api_misuse'                    => 'required|in:1,0',
      'vulnerability_scan'            => 'required|in:1,0',

      'custom_policy'                 => 'sometimes',

      'taint_analysis'                => 'required|in:1,0',
      'taint_aplength'                => 'sometimes|integer|min:1',
      'taint_nocallbacks'             => 'sometimes|in:1,0',
      'taint_sysflows'                => 'sometimes|in:1,0',
      'taint_implicit'                => 'sometimes|in:1,0',
      'taint_static'                  => 'sometimes|in:1,0',
    ];

    return $rules;
  }

}
