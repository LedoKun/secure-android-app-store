@extends('admin.layout.master')

@section('title', 'Dashboard')

@section('content')

<div class="row">
  <div class="col-xs-6 col-sm-3">

    <div class="widget well well-sm">
      <div class="icon">
        <i class="glyphicon glyphicon-file"></i>
      </div>
      <div class="text">
        <var>{{ $no_of_apps }}</var>
        <label class="text-muted"># of uploaded apps</label>
      </div>
    </div>

  </div>
  <div class="col-xs-6 col-sm-3">

    <div class="widget well well-sm">
      <div class="icon">
        <i class="glyphicon glyphicon-transfer"></i>
      </div>
      <div class="text">
        <var>{{ $no_of_analysis_done }}</var>
        <label class="text-muted"># of analysis done</label>
      </div>
    </div>

  </div>
  <div class="col-xs-6 col-sm-3">

    <div class="widget well well-sm">
      <div class="icon">
        <i class="glyphicon glyphicon-ok"></i>
      </div>
      <div class="text">
        <var>{{ $no_of_visible_apps }}</var>
        <label class="text-muted"># of okay apps</label>
      </div>
    </div>

  </div>
  <div class="col-xs-6 col-sm-3">

    <div class="widget well well-sm">
      <div class="icon">
        <i class="glyphicon glyphicon-remove"></i>
      </div>
      <div class="text">
        <var>{{ $no_of_analysis_done - $no_of_visible_apps }}</var>
        <label class="text-muted"># of malicious apps</label>
      </div>
    </div>

  </div>
</div>

@endsection

@section('additional_css')
<style>
/*Adapted from https://bootsnipp.com/snippets/featured/hero-widgets
by travislaynewilson*/

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
