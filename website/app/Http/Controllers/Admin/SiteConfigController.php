<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;

# Models
use App\SiteConfig;
use App\AnalysisResult;

class SiteConfigController extends Controller
{

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
    //
    $oldValues = SiteConfig::orderBy('created_at', 'desc')->first();

    return view('admin.web_settings', compact('oldValues'));
  }

  /**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function create(Request $request)
  {
    //
    return redirect('/admin/config');

  }

  /**
  * Store a newly created resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return \Illuminate\Http\Response
  */
  public function store(Request $request)
  {
    //
    $rules = [
      'site_name' => 'required',
      'max_cvss' => 'required|regex:/^\d*(\.\d{1})?$/',
      'allow_mitm' => 'required|in:1,0',
      'allow_hide_icon' => 'required|in:1,0',
      'allow_weak_cryptographic_api' => 'required|in:1,0',
      'allow_vulnerable_leak' => 'required|in:1,0',
      'allow_malicious_leak' => 'required|in:1,0',
      'max_no_rules_broken' => 'required|min:-1',
      'max_no_flow' => 'required|min:-1',
      'allow_cert_pinning_mitm' => 'required|in:1,0',
      'allow_api_key' => 'required|in:1,0',
      'allow_password' => 'required|in:1,0',
      'allow_privilege_escalation' => 'required|in:1,0',
      'max_vulnerability_count' => 'required|min:-1',
    ];

    $validator = Validator::make($request->all(), $rules);


    if($validator->fails()) {

      return redirect('/admin/config')
      ->withErrors($validator)
      ->withInput();

    } else {

      $id = SiteConfig::orderBy('created_at', 'desc')->first()->id;
      SiteConfig::where('id', $id)->update($request->except(['_token', '_method']));

      // Update app visibility
      AnalysisResult::where('isVisible', false)
      ->update(['isVisible' => true]);

      if($request->max_cvss != -1) {
        AnalysisResult::where('mitm_cvss', '>', $request->max_cvss)
        ->orWhere('cert_pinning_mitm_cvss', '>', $request->max_cvss)
        ->orWhere('privilege_escalation_cvss', '>', $request->max_cvss)
        ->update(['isVisible' => false]);
      }

      if($request->allow_mitm == 0) {
        AnalysisResult::where('mitm', 1)
        ->update(['isVisible' => false]);
      }

      if($request->allow_hide_icon == 0) {
        AnalysisResult::where('hide_icon', 1)
        ->update(['isVisible' => false]);
      }

      if($request->allow_weak_cryptographic_api == 0) {
        AnalysisResult::where('weak_crypto', 1)
        ->update(['isVisible' => false]);
      }

      if($request->allow_vulnerable_leak == 0) {
        AnalysisResult::where('vulnerable_leak', 1)
        ->update(['isVisible' => false]);
      }

      if($request->allow_malicious_leak == 0) {
        AnalysisResult::where('malicious_leak', 1)
        ->update(['isVisible' => false]);
      }

      if($request->max_no_rules_broken != -1) {
        AnalysisResult::where('no_rules_broken', '>', $request->max_no_rules_broken)
        ->update(['isVisible' => false]);
      }

      if($request->max_no_flow != -1) {
        AnalysisResult::where('no_flow', '>', $request->max_no_flow)
        ->update(['isVisible' => false]);
      }

      if($request->cert_pinning_mitm == 0) {
        AnalysisResult::where('allow_cert_pinning_mitm', 1)
        ->update(['isVisible' => false]);
      }

      if($request->allow_api_key == 0) {
        AnalysisResult::where('api_key', 1)
        ->update(['isVisible' => false]);
      }

      if($request->allow_password == 0) {
        AnalysisResult::where('password', 1)
        ->update(['isVisible' => false]);
      }

      if($request->allow_privilege_escalation == 0) {
        AnalysisResult::where('privilege_escalation', 1)
        ->update(['isVisible' => false]);
      }

      if($request->max_vulnerability_count != -1) {
        AnalysisResult::where('vulnerability_count', '>', $request->max_vulnerability_count)
        ->update(['isVisible' => false]);
      }

    }

    return redirect('/admin/config');

  }

  /**
  * Display the specified resource.
  *
  * @param  \App\siteConfig  $siteConfig
  * @return \Illuminate\Http\Response
  */
  public function show(siteConfig $siteConfig)
  {
    //

    return redirect('/admin/config');
  }

  /**
  * Show the form for editing the specified resource.
  *
  * @param  \App\siteConfig  $siteConfig
  * @return \Illuminate\Http\Response
  */
  public function edit(siteConfig $siteConfig)
  {
    //

    return redirect('/admin/config');
  }

  /**
  * Update the specified resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @param  \App\siteConfig  $siteConfig
  * @return \Illuminate\Http\Response
  */
  public function update(Request $request, siteConfig $siteConfig)
  {
    //

    return redirect('/admin/config');
  }

  /**
  * Remove the specified resource from storage.
  *
  * @param  \App\siteConfig  $siteConfig
  * @return \Illuminate\Http\Response
  */
  public function destroy(siteConfig $siteConfig)
  {
    //

    return redirect('/admin/config');
  }
}
