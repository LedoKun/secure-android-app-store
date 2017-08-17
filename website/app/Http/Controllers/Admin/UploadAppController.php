<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Queue;
use Session;

# Models
use App\UploadApp;
use App\AnalysisResult;
use App\SiteConfig;
use App\AnalysisToolSetting;
use App\AnalysisToolDefaultRule;

# Queue
use App\Jobs\AnalyseApps;


# Other Plugins
use ZipArchive;

# Secure App Store class
use App\SecureAppStore\APK\APK;

class UploadAppController extends Controller
{

  /*
  |--------------------------------------------------------------------------
  | Upload App Controller
  |--------------------------------------------------------------------------
  |
  | This controller is responsible for handling Android application upload.
  |
  */

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
  * @return \Illuminate\Http\View
  */
  public function index()
  {
    //

    if(Session::has('response')) {
      $response = Session::get('response');
      $info = "Successfully uploaded " . $response['success'] . " files" .
      " (duplication " . $response['duplicate'] .
      ", failed " . $response['fail'] . ")";
    } else {
      $info = null;
    }

    $results = UploadApp::selectAllPendingApps()
    ->paginate(15, ['id', 'package_name', 'size', 'sha256', 'isBeingAnalyzed',
    'isFailedAnalysis', 'attempts', 'updated_at', 'created_at']);

    return view('admin.pending', compact('results', 'info'));
  }

  /**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\View
  */
  public function create()
  {
    //
    return view('admin.upload');
  }

  /**
  * Store a newly created resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return \Illuminate\Http\Response
  */
  public function store(Request $request)
  {

    $validator = Validator::make($request->all(), [

      'file' => 'max:4194304',                         // limit at 4GB

    ]);

    // Modified from https://devdojo.com/episode/laravel-multiple-file-upload
    $files = $validator->valid();

    $response = array(
      'fail'       => 0,
      'success'    => 0,
      'duplicate'  => 0,
    );

    // Failed the validation
    if(isset($validator->invalid()['file'])) {
      $response['fail'] = count($validator->invalid()['file']);
    }

    // No valid files uploaded
    if(!isset($files['file'])) {
      return \Response::json($response);
    } else {
      $files = $validator->valid()['file'];
    }

    // Save files
    foreach($files as $file):

      // get the original extension without the dot
      $extension = $file->getClientOriginalExtension();

      // Test APK file if it is broken/incomplete
      $test_zip = new \ZipArchive();
      $res = $test_zip->open($file->getRealPath(), ZipArchive::CHECKCONS);

      if($res !== true) {
        $response['fail']++;
        continue;
      }

      // Calculate hash and generate new filename
      $hash = hash_file('sha256', $file->getRealPath());
      $isAppExits = UploadApp::selectAppHash($hash)->exists();

      if($isAppExits) {
        $response['duplicate']++;
        continue;
      }

      $randomCount = 1;

      do {
        if($randomCount > 5) {
          // Failed filename generation
          abort(500);
        }

        $filename_no_ext = Str::quickRandom();
        $filename = $filename_no_ext.'.apk';

        $randomCount++;
      } while(Storage::disk('apk')->exists($filename));


      try {
        $apk = new APK($file->getRealPath());
      } catch (Exception $e) {
        // APK testing Failed
        $response['fail']++;
        continue;
      }

      // Unable to extract information from the APK file
      if(!$apk->isValid()) {
        $response['fail']++;
        continue;
      }

      $manifest = $apk->getManifest();
      $permissions = $apk->getPermissions();
      $resources = $apk->getResources();
      $appLabel = $apk->getAppLabel();

      // Add record in the database
      $fileRecord = new UploadApp();
      $fileRecord->package_name = $manifest->getPackageName();
      $fileRecord->apk_label = $appLabel[0];
      $fileRecord->version = $manifest->getVersionName();
      $fileRecord->min_sdk_level = $manifest->getMinSdkLevel();
      $fileRecord->min_sdk_platform = $manifest->getMinSdk()->platform;
      $fileRecord->originalFilename = $file->getClientOriginalName();
      $fileRecord->filename = $filename;
      $fileRecord->size = ceil($file->getSize()/1048576);
      $fileRecord->sha256 = $hash;
      $fileRecord->sha1 = hash_file('sha1', $file->getRealPath());
      $fileRecord->md5 = hash_file('md5', $file->getRealPath());
      $rowSaveStatus = $fileRecord->save();

      // Get the ID of the new record
      $app_id = $fileRecord->id;

      // Save the uploaded apk in the filesystem
      $fileSaveStatus = Storage::disk('apk')->put($filename, fopen($file, 'r+'));

      // Save extracted APK resources
      $apkPermission = Storage::disk('apk_permission')->put($filename_no_ext.'_perm.json', json_encode($permissions));
      if (isset($resources[0]) && (strlen($resources[0]) > 0) ) {
        Storage::disk('apk_png')->put($filename_no_ext.'_icon.png', $apk->getStreamFirstResource());
      }

      // Either unable to save DB record, or save files
      if(!$rowSaveStatus || !$fileSaveStatus || !$apkPermission) {
        Storage::disk('apk')->delete($filename);
        Storage::disk('apk_png')->delete($filename_no_ext.'_icon.png');
        Storage::disk('apk_permission')->delete($filename_no_ext.'_perm.png');
        UploadApp::where('id', $app_id)->delete();

        $response['fail']++;
        continue;
      }

      //  Dispatch a new background analysis task
      $job = new AnalyseApps(['id' => $app_id, 'filename' => $filename]);
      $job->tries = env('ANALYSIS_RETRIES', 3);
      $currentDefault = AnalysisToolDefaultRule::getDefaultRuleID()->latest()->first();

      if ($currentDefault !== null) {
        $rule = AnalysisToolSetting::where('id', $currentDefault->rule_id)->first();
        $job->timeout = $rule->timeout + 180;		// Allow worker to handle cleanup process
      }

      dispatch($job);

      $response['success']++;

    endforeach;

    return \Redirect::route('upload.store')->with('response', $response);
  }

  /**
  * Display the specified resource.
  *
  * @param  \App\Admin\UploadApp  $uploadApp
  * @return \Illuminate\Http\View
  */
  public function show($id)
  {

    $app = UploadApp::where('id', $id)->first();

    if($app === null) {
      // No app with that ID
      abort(404);
    }

    $filename_without_ext = basename($app->filename, '.apk');

    // Get the app icon
    $app_icon_filename = $filename_without_ext.'_icon.png';

    if (!Storage::disk('apk_png')->exists($app_icon_filename)) {
      $app_icon = null;
    } else {
      $app_icon = $app_icon_filename;
    }

    // Read the app permissions
    $permission_filename = $filename_without_ext.'_perm.json';

    if (!Storage::disk('apk_permission')->exists($permission_filename)) {
      $permissions = null;
    } else {
      $permissions = json_decode(Storage::disk('apk_permission')
      ->get($permission_filename), true);
    }

    if($app->isAnalyzed) {
      // Read parsed results
      $parsed_result_filename = $filename_without_ext.'_parsed.json';

      if (!Storage::disk('analysis')->exists($parsed_result_filename)) {
        // File missing
        abort(500);
      }

      $file_results = json_decode(Storage::disk('analysis')
      ->get($parsed_result_filename), true);
      $analysis_results = AnalysisResult::getAnalysisResult($id)->first();

    } else {
      $analysis_results = null;
      $file_results = null;
    }

    return view('admin.showApp', compact('app', 'analysis_results',
    'file_results', 'app_icon', 'permissions'));

  }

  /**
  * Show the form for editing the specified resource.
  *
  * @param  \App\Admin\UploadApp  $uploadApp
  * @return \Illuminate\Http\Response
  */
  public function edit(UploadApp $uploadApp)
  {
    //
  }

  /**
  * Update the specified resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @param  \App\Admin\UploadApp  $uploadApp
  * @return \Illuminate\Http\Response
  */
  public function update(Request $request, UploadApp $uploadApp)
  {
    //
  }

  /**
  * Remove the specified resource from storage.
  *
  * @param  \App\Admin\UploadApp  $uploadApp
  * @return \Illuminate\Http\Response
  */
  public function destroy(Request $request, $id)
  {
    //

    $file = UploadApp::selectPendingApp($id, $request->hash)->get(['filename',
    'isBeingAnalyzed', 'isAnalyzed'])->first();

    $response['success'] = false;

    if(($file !== null) && !$file->isBeingAnalyzed) {

      // Delete APK files
      $based_filename = basename($file->filename, '.apk');
      $parsed_result_filename = $based_filename.'_parsed.json';
      $app_icon = $based_filename.'_icon.png';
      $app_perm = $based_filename.'_perm.json';

      $deletedFile = Storage::disk('apk')->delete($file->filename);
      Storage::disk('analysis')->delete($parsed_result_filename);
      Storage::disk('apk_png')->delete($app_icon);
      Storage::disk('apk_permission')->delete($app_perm);

      // Delete DB record
      UploadApp::where('id', $id)->delete();
      AnalysisResult::where('app_id', $id)->delete();

      if($deletedFile) {
        $response['success'] = true;
      }

    }

    return \Response::json($response);

  }
}
