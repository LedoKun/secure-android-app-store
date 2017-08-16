@extends('admin.layout.master')

@section('title')
App #{{ $app->id }}
@endsection

@section('content')

<div class="container-fluid">

  <div class="row pull-right">
    <p><a href="javascript:history.go(-1)"><i class="fa fa-arrow-left fa-fw"></i> Back</a></p>
  </div>

  <div class="row">
    <dl class="dl-horizontal">
      <dt>App Icon</dt>
      <dd>
        @if ($app_icon !== null)
        <img src="{{ asset('storage/apk/png/'.$app_icon) }}" alt="App Icon" />
        @else
        -
        @endif
      </dd>
    </dl>

    <dl class="dl-horizontal">
      <dt></dt>
      <dd></dd>
    </dl>

    <dl class="dl-horizontal">
      <dt>App Name</dt>
      <dd>
        @if ( ($app->apk_label === null) || (strlen($app->apk_label == 0)) )
        -
        @else
        {{ $app->apk_label }}
        @endif
      </dd>
    </dl>
    <dl class="dl-horizontal">
      <dt>Package Name</dt>
      <dd>{{ $app->package_name }}</dd>
    </dl>
    <dl class="dl-horizontal">
      <dt>Version</dt>
      <dd>{{ $app->version }}</dd>
    </dl>
    <dl class="dl-horizontal">
      <dt>Min SDK Level</dt>
      <dd>{{ $app->min_sdk_level }}</dd>
    </dl>
    <dl class="dl-horizontal">
      <dt>Min SDK Platform</dt>
      <dd>{{ $app->min_sdk_platform }}</dd>
    </dl>
    <dl class="dl-horizontal">
      <dt>Permissions</dt>
      <dd>
        @foreach ($permissions as $perm => $detail)

        <p class="
        @if($detail['flags']['danger'])
        text-danger
        @elseif($detail['flags']['warning'])
        text-warning
        @elseif($detail['flags']['cost'])
        text-info
        @endif
        ">
        <b>{{ $perm }}</b>
        <br/>
        {{ $detail['description'] }}
      </p>

      @endforeach
    </dd>
  </dl>

  <dl class="dl-horizontal">
    <dt></dt>
    <dd></dd>
  </dl>

  <dl class="dl-horizontal">
    <dt>Original Filename</dt>
    <dd>{{ $app->originalFilename }}</dd>
  </dl>
  <dl class="dl-horizontal">
    <dt>Filename on system</dt>
    <dd>{{ $app->filename }}</dd>
  </dl>
  <dl class="dl-horizontal">
    <dt>Size (MB)</dt>
    <dd>{{ ($app->size) }}</dd>
  </dl>
  <dl class="dl-horizontal">
    <dt>MD5*</dt>
    <dd>{{ $app->md5 }}</dd>
  </dl>
  <dl class="dl-horizontal">
    <dt>SHA1*</dt>
    <dd>{{ $app->sha1 }}</dd>
  </dl>
  <dl class="dl-horizontal">
    <dt>SHA256</dt>
    <dd>{{ $app->sha256 }}</dd>
  </dl>

  <dl class="dl-horizontal">
    <dt></dt>
    <dd></dd>
  </dl>

  <dl class="dl-horizontal">
    <dt>Analysis Status</dt>
    <dd>
      @if($app->isAnalyzed)
      Done
      @elseif($app->isBeingAnalyzed)
      Analysing
      @else
      -
      @endif
      <br/>(# of attempts: {{ $app->attempts }})
    </dd>
  </dl>

  <dl class="dl-horizontal">
    <dt></dt>
    <dd></dd>
  </dl>

  <dl class="dl-horizontal">
    <dt>Last update</dt>
    <dd>{{ $app->updated_at }} ({{ $app->updated_at->diffForHumans() }})</dd>
  </dl>
  <dl class="dl-horizontal">
    <dt>Created on</dt>
    <dd>{{ $app->created_at }} ({{ $app->created_at->diffForHumans() }})</dd>
  </dl>
</div>

@if($app->isAnalyzed)

<div class="row result">
  <h2 class="page-header">Analysis Results</h2>

  <div class="col-xs-4 col-sm-4 text-center">
  </div>

  <div class="col-xs-4 col-sm-3 text-center">
    <div class="widget well well-sm">

      @if($analysis_results->isVisible)
      <div class="icon">
        <i class="glyphicon glyphicon-ok"></i>
      </div>
      <div class="text">
        <var>Passed</var>
        <label class="text-muted">The app passed the<br/>publishing criteria.</label>
      </div>
      @else
      <div class="icon">
        <i class="glyphicon glyphicon-remove"></i>
      </div>
      <div class="text">
        <var>Failed</var>
        <label class="text-muted">The app failed the<br/>publishing criteria.</label>
      </div>
      @endif

    </div>
  </div>

  <div class="col-xs-4 col-sm-4 text-center">
  </div>

</div>

@foreach ($file_results as $testname => $output)
<div class="row raw-div">

  <h3 class="page-header">Test - {{ $testname }}</h3>

  <?php
  if(!isset($output['raw'])) {
    $raw = null;
  } else {
    $raw = $output['raw'];
    unset($output['raw']);
  }
  ?>

  Found Issues:
  <textarea class="at_a_glance" disabled>{{ implode(PHP_EOL, $output) }}</textarea>

  Raw output:
  <textarea class="raw" disabled>{{ $raw }}</textarea>
</div>
@endforeach

@endif

<div class="row pull-right">
  <p><a href="javascript:history.go(-1)"><i class="fa fa-arrow-left fa-fw"></i> Back</a></p>
</div>

<div class="row">
  <p class="small text-warning"><b>*</b> - MD5 and SHA1 hashes are provided for information purposes only. Collisions were found in the compression functions of <a href="http://ai2-s2-pdfs.s3.amazonaws.com/1e48/6d1df47fa6cad646de0ab921c566aab9e9c8.pdf">MD5</a> and <a href="http://courses.csail.mit.edu/6.885/spring05/papers/wangyinyu.pdf">SHA1</a>.</p>
</div>


</div>
@endsection

@section('additional_css')
<style>
div.result {
  padding-top: 20px;
}

div.raw-div {
  padding-bottom: 20px;
}

div textarea.raw {
  background-color: #000;
  border: 1px solid #000;
  color: #00ff00;
  padding: 8px;
  font-family: Consolas, Lucida Console, monospace;
  width: 100%;
  height: 400px;
}

textarea.at_a_glance {
  width: 100%;
  height: 250px;
}

.widget {
  text-align: center;
  padding-top: 10px;
  padding-bottom: 10px;
}

.widget .icon {
  display: block;
  font-size: 86px;
  line-height: 86px;
  margin-bottom: 10px;
  text-align: center;
}

.widget var {
  display: block;
  height: 50px;
  font-size: 50px;
  line-height: 50px;
  font-style: normal;
}

.widget label {
  font-size: 17px;
}

</style>
@endsection
