<?php

namespace Tests\Feature;

use Laravel\BrowserKitTesting\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\CreatesApplication;
use Mockery;
use App\UploadApp;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

# Queue
use App\Jobs\AnalyseApps;

class APKUploadTest extends BaseTestCase
{

  use WithoutMiddleware, DatabaseMigrations, CreatesApplication;

  public $baseUrl = 'http://localhost:8080';

  public function setUp() {
    parent::setUp();

    // Test without actually creating a job
    Queue::fake();

    // Test without actually creating a file
    Storage::fake('apk');
    Storage::fake('apk_permission');
    Storage::fake('apk_png');
  }

  public function tearDown(){
    parent::tearDown();
    Mockery::close();
  }

  /**
  * Test upload a fake APK, should fail
  *
  * @return void
  */
  public function testFakeApkUpload()
  {
    $full_path = dirname(__FILE__) . '/fake_apk_1.apk';
    $file = new UploadedFile($full_path, null, null, null, true);

    $response = $this->call('POST', 'admin/upload', [
      'file' => [$file],
      '_token' => csrf_token()
    ]);

    $this->assertEquals(200, $response->status());
    $this->assertEquals('{"fail":1,"success":0,"duplicate":0}', $response->content());

    // No qeuue must be pushed
    Queue::assertNotPushed(AnalyseApps::class);
  }

  /**
  * Test upload multiple fake APKs, should failed
  *
  * @return void
  */
  public function testMultipleFakeApkUpload()
  {
    $full_path = dirname(__FILE__) . '/fake_apk_1.apk';
    $full_path2 = dirname(__FILE__) . '/fake_apk_2.apk';
    $full_path3 = dirname(__FILE__) . '/fake_apk_3.apk';
    $file = new UploadedFile($full_path, null, null, null, true);
    $file2 = new UploadedFile($full_path, null, null, null, true);
    $file3 = new UploadedFile($full_path, null, null, null, true);

    $response = $this->call('POST', 'admin/upload', [
      'file' => [$file, $file2, $file3],
      '_token' => csrf_token()
    ]);

    $this->assertEquals(200, $response->status());
    $this->assertEquals('{"fail":3,"success":0,"duplicate":0}', $response->content());

    // No qeuue must be pushed
    Queue::assertNotPushed(AnalyseApps::class);
  }

  /**
  * Test upload a valid APKs, should passed
  *
  * @return void
  */
  public function testApkUploadNoJob()
  {
    $full_path = dirname(__FILE__) . '/dummy.apk';

    $file = new UploadedFile($full_path, null, null, null, true);

    $response = $this->call('POST', 'admin/upload', [
      'file' => [$file],
      '_token' => csrf_token()
    ]);

    $this->assertEquals(200, $response->status());
    $this->assertEquals('{"fail":0,"success":1,"duplicate":0}', $response->content());

    // The upload page display a fraction of sha1
    $this->visit('admin/upload')
    ->see('d6ac4d091c');

    // check the database
    $this->seeInDatabase('upload_apps',['md5' => '98ef4a3f01e09d366afd8ab11db374ac']);

    $app = UploadApp::where('md5', '98ef4a3f01e09d366afd8ab11db374ac')
    ->get()->first();
    $filename_without_ext = basename($app->filename, '.apk');

    // Test APK information extraction
    Storage::disk('apk')->assertExists($app->filename);
    Storage::disk('apk_permission')->assertExists($filename_without_ext.'_perm.json');
    Storage::disk('apk_png')->assertExists($filename_without_ext.'_icon.png');
  }

}
