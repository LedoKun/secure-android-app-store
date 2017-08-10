@extends('admin.tools_config_partials.tools_settings_master')

@section('title')

  Edit Configuration #{{ $id }}

@endsection

@section('form_open')

  {{ Form::model($oldValues, array('route' => array('rules.update', $id), 'method' => 'PATCH')) }}

@endsection

@section('form_close')

  @if (!$isDefault)
    <div class="form-group">
      {{ Form::submit('Save', array('class' => 'btn btn-success', 'id' => 'submit')) }}
    </div>
  @else
    <div class="form-group">
      {{ Form::submit('Save', array('class' => 'btn btn-success', 'id' => 'submit', 'disabled')) }}
    </div>
  @endif

  {{ Form::close() }}

@endsection
