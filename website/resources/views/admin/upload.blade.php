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
    <form id="upload" enctype="multipart/form-data">
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

{{-- https://devdojo.com/episode/laravel-multiple-file-upload --}}

<script>
var form = document.getElementById('upload');
var request = new XMLHttpRequest();

form.addEventListener('submit', function(e){
  e.preventDefault();
  var formdata = new FormData(form);

  request.open('post', '{{ URL::to('admin/upload') }}');
  request.addEventListener("load", transferComplete);
  request.send(formdata);

  document.getElementById('message').innerHTML = "Uploading files...";
  document.getElementById("submit").disabled = true;
});

function transferComplete(data){
  response = JSON.parse(data.currentTarget.response);
  form.reset();
  if(response.success > 0 || response.duplicate > 0 || response.fail > 0){
    document.getElementById('message').innerHTML = "Successfully uploaded " + response.success + " files"
    + " (duplication " + response.duplicate
    + ", failed " + response.fail + ")";
  } else {
    document.getElementById('message').innerHTML = "Files upload failed!";
  }

  document.getElementById("submit").disabled = false;
}
</script>
@endsection
