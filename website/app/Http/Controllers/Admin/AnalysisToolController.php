<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use DB;

# Traits
use App\Traits\ToolsConfigHelper;

# Models
use App\AnalysisToolSetting;
use App\AnalysisToolDefaultRule;
use App\UploadApp;

# Queue
use App\Jobs\AnalyseApps;

class AnalysisToolController extends Controller
{
  use ToolsConfigHelper;

  /**
  * Create a new controller instance.
  *
  * @return void
  */
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    $existingRules = AnalysisToolSetting::listRules()
    ->paginate(4, ['id', 'rule_name', 'api_misuse', 'custom_policy',
    'custom_policy', 'taint_analysis', 'updated_at',
    'created_at'], 'existingRules');

    $currentDefault = AnalysisToolDefaultRule::getDefault()->latest()->first();
    $historyDefault = AnalysisToolDefaultRule::listHistory()->paginate(4, ['*'], 'history');

    return view('admin.rules', compact('existingRules', 'currentDefault', 'historyDefault'));
  }

  /**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function create()
  {
    // Create a new analysis rule
    $disableableFields = $this->disableableFields();
    return view('admin.tools_settings_create', compact('disableableFields'));
  }

  /**
  * Store a newly created resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return \Illuminate\Http\Response
  */
  public function store(Request $request)
  {
    $rules = $this->formRules();

    $validator = Validator::make($request->all(), $rules);

    if($validator->fails()) {
      return redirect('/admin/rules/create')
      ->withErrors($validator)
      ->withInput();
    } else {

      $newRule = new AnalysisToolSetting($validator->valid());
      $newRule->save();

      return redirect('/admin/rules');
    }
  }

  /**
  * Display the specified resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function show()
  {
    //
    return redirect('/admin/rules');
  }

  /**
  * Show the form for editing the specified resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function edit($id)
  {
    //
    $oldValues = AnalysisToolSetting::findOrFail($id);
    $disableableFields = $this->disableableFields();

    $currentDefault = AnalysisToolDefaultRule::getDefaultRuleID()->latest()->first();

    if(($currentDefault === null) || ($id != $currentDefault->rule_id)) {
      $isDefault = false;
    } else {
      $isDefault = true;
      $info = ['default' => 'The default rule cannot be modified.'];
    }

    return view('admin.tools_settings_edit', compact('id', 'disableableFields', 'oldValues', 'isDefault', 'info'));
  }

  /**
  * Update the specified resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return \Illuminate\Http\Response
  */
  public function update(Request $request, $id)
  {
    $rules = $this->formRules();
    // $attributes = $this->formAttributes();

    $validator = Validator::make($request->all(), $rules);
    // $validator->setAttributeNames($attributes);


    if($validator->fails()) {

      return back()->withErrors($validator)->withInput();

    } else {

      $currentDefault = AnalysisToolDefaultRule::getDefaultRuleID()->latest()->first();

      if(($currentDefault === null) || ($id != $currentDefault->rule_id)) {

        AnalysisToolSetting::where('id', $id)->update($request->except(['_token', '_method']));
        return redirect('/admin/rules');

      } else {

        $validator->errors()->add('submit', 'The default rule cannot be modified.');
        return back()->withErrors($validator)->withInput();

      }

    }
  }

  /**
  * Remove the specified resource from storage.
  *
  * @return \Illuminate\Http\Response
  */
  public function destroy(Request $request, $id)
  {
    //
    $currentDefault = AnalysisToolDefaultRule::select('id', 'rule_id')->latest()->first();

    if(($currentDefault === null) || ($id != $currentDefault->rule_id)) {

      $response['success'] = AnalysisToolSetting::findOrFail($id)->delete();
      $response['msg'] = 'Rule removed';

    } else {

      $response['success'] = false;
      $response['msg'] = 'Unable to remove the rule as it might not exists or it is a default rule';

    }

    return \Response::json($response);
  }

  public function setDefault(Request $request, $id) {

    $currentDefault = AnalysisToolDefaultRule::getDefaultRuleID()->latest()->first();

    if(($currentDefault === null) || ($id != $currentDefault->rule_id)) {

      $ruleExists = AnalysisToolSetting::where('id', $id)->first();

      if($ruleExists !== null) {
        $newDefault = new AnalysisToolDefaultRule();
        $newDefault->rule_id = $id;

        $newDefault->save();

        // Reanalyze all finished jobs
        $finished_jobs = UploadApp::where('isAnalyzed', 1)
        ->select('id', 'filename')->get();

        UploadApp::where('isAnalyzed', 1)
        ->update(['isAnalyzed' => 0]);

        // Push a new background analysis task
        foreach ($finished_jobs as $app) {
          $job = (new AnalyseApps([
            'id'        => $app->id,
            'filename'  => $app->filename]));

            dispatch($job);
          }

          $response['msg'] = 'Default rule set';
          $response['success'] = true;
        } else {

          $response['msg'] = 'There is no such rule';
          $response['success'] = false;

        }
      } else {

        $response['msg'] = 'This rule is already a default rule';
        $response['success'] = false;

      }

      return \Response::json($response);

    }
  }
