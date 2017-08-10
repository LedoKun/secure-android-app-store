@extends('admin.tools_config_partials.tools_settings_master')

@section('title', 'New Analysis Configuration')

@section('form_open')

  {{ Form::open(array('url' => '/admin/rules', 'method' => 'POST')) }}

@endsection

@section('form_close')

  <div class="form-group">
  {{ Form::submit('Create', array('class' => 'btn btn-success')) }}
  </div>

  {{ Form::close() }}

@endsection

@section('additional_js')
<script>
</script>
@endsection

@section('additional_css')
<style>
</style>
@endsection
