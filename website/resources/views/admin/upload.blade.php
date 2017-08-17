@extends('admin.layout.master')

@section('title', 'Upload Apps')

@section('content')

@include('admin.layout.messages')

<!-- <form action="/admin/upload" method="post" id="x" enctype="multipart/form-data">
  {{ csrf_field() }}
  <input type="file" name="file[]" multiple><br />
  <input type="submit">
</form> -->

<div class="panel panel-default">
  <div class="panel-heading">
    <strong>Upload files</strong>
    <small> Supports multiple APK files upload.</small>
  </div>
  <div class="panel-body">
    <form id="upload" enctype="multipart/form-data" action="/admin/upload" method="post">
      {{ csrf_field() }}
      <div class="form-group">
        <input type="file" name="file[]" id="fileupload_fild" multiple="multiple">
      </div>
      <div class="form-group">
        <input type="submit" name="submit" class="btn btn-success pull-right" id="submit">
      </div>
    </form>
    <div class="clearfix">
      <p id="message"></p>
    </div>
  </div>
</div>
@endsection

@section('additional_js')

<script src="/js/bootstrap-filestyle.min.js"></script>

<script>
$('#fileupload_fild').filestyle({
  input : true,
  buttonName : 'btn-danger',
  iconName : 'glyphicon glyphicon-folder-close'
});
</script>

@endsection
