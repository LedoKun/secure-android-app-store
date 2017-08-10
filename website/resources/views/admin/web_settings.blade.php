@extends('admin.layout.master')

@section('title', 'Web Store Settings')

@section('content')

@include('admin.layout.messages')

{{ Form::open(array('url' => '/admin/config', 'method' => 'POST')) }}

<legend>Site Information</legend>

<div class="form-group">

  <div class="form-group">
    {{ Form::label('site_name', 'Site name') }}
    {{ Form::text('site_name', null, array('class' => 'form-control', 'required' => 'required', 'autofocus' => 'autofocus')) }}
  </div>

</div>

<legend>App Publishing Criteria</legend>

<div class="form-group">

  <div class="form-group">
    {{ Form::label('max_vulnerability_count', 'Maximum number of vulnerabilities found (Enter -1 to disable this rule)') }}
    {{ Form::text('max_vulnerability_count', null, array('class' => 'form-control', 'required' => 'required')) }}
  </div>

  <div class="form-group">
    {{ Form::label('allow_hide_icon', 'Allow app that hides its icon') }}
    <div class="radio">
      <label>{{ Form::radio('allow_hide_icon', '1', false, array('id' => 'allow_hide_icon_enabled')) }} Enable</label>
    </div>
    <div class="radio">
      <label>{{ Form::radio('allow_hide_icon', '0', true, array('id' => 'allow_hide_icon_disabled')) }} Disable</label>
    </div>
  </div>

  <div class="form-group">
    {{ Form::label('allow_mitm', 'Allow app that might be vulnerable to the man-in-the-middle attack') }}
    <div class="radio">
      <label>{{ Form::radio('allow_mitm', '1', false, array('id' => 'allow_mitm_enabled')) }} Enable</label>
    </div>
    <div class="radio">
      <label>{{ Form::radio('allow_mitm', '0', true, array('id' => 'allow_mitm_disabled')) }} Disable</label>
    </div>
  </div>

  <div class="form-group">
    {{ Form::label('allow_cert_pinning_mitm', 'Allow app that might be implement certificate pinning') }}
    <div class="radio">
      <label>{{ Form::radio('allow_cert_pinning_mitm', '1', false, array('id' => 'allow_cert_pinning_mitm_enabled')) }} Enable</label>
    </div>
    <div class="radio">
      <label>{{ Form::radio('allow_cert_pinning_mitm', '0', true, array('id' => 'allow_cert_pinning_mitm_disabled')) }} Disable</label>
    </div>
  </div>

  <div class="form-group">
    {{ Form::label('allow_weak_cryptographic_api', 'Allow app with weak implementation of cryptography APIs') }}
    <div class="radio">
      <label>{{ Form::radio('allow_weak_cryptographic_api', '1', false, array('id' => 'allow_weak_cryptographic_api_enabled')) }} Enable</label>
    </div>
    <div class="radio">
      <label>{{ Form::radio('allow_weak_cryptographic_api', '0', true, array('id' => 'allow_weak_cryptographic_api_disabled')) }} Disable</label>
    </div>
  </div>

  <div class="form-group">
    {{ Form::label('allow_vulnerable_leak', 'Allow app that might vulnerable to data leakage') }}
    <div class="radio">
      <label>{{ Form::radio('allow_vulnerable_leak', '1', false, array('id' => 'allow_vulnerable_leak_enabled')) }} Enable</label>
    </div>
    <div class="radio">
      <label>{{ Form::radio('allow_vulnerable_leak', '0', true, array('id' => 'allow_vulnerable_leak_disabled')) }} Disable</label>
    </div>
  </div>

  <div class="form-group">
    {{ Form::label('max_no_flow', 'Maximum number of possible information leakage count (Enter -1 to disable this rule)') }}
    {{ Form::text('max_no_flow', null, array('class' => 'form-control', 'required' => 'required')) }}
  </div>

  <div class="form-group">
    {{ Form::label('allow_malicious_leak', 'Allow app that might steal user information') }}
    <div class="radio">
      <label>{{ Form::radio('allow_malicious_leak', '1', false, array('id' => 'allow_malicious_leak_enabled')) }} Enable</label>
    </div>
    <div class="radio">
      <label>{{ Form::radio('allow_malicious_leak', '0', true, array('id' => 'allow_malicious_leak_disabled')) }} Disable</label>
    </div>
  </div>

  <div class="form-group">
    {{ Form::label('max_no_rules_broken', 'Maximum number of policy rules violated by an app (Enter -1 to disable this rule)') }}
    {{ Form::text('max_no_rules_broken', null, array('class' => 'form-control', 'required' => 'required')) }}
  </div>

  <div class="form-group">
    {{ Form::label('allow_api_key', 'Allow app that might hardcoded an API key') }}
    <div class="radio">
      <label>{{ Form::radio('allow_api_key', '1', false, array('id' => 'allow_api_key_enabled')) }} Enable</label>
    </div>
    <div class="radio">
      <label>{{ Form::radio('allow_api_key', '0', true, array('id' => 'allow_api_key_disabled')) }} Disable</label>
    </div>
  </div>

  <div class="form-group">
    {{ Form::label('allow_password', 'Allow app that might hardcoded an password') }}
    <div class="radio">
      <label>{{ Form::radio('allow_password', '1', false, array('id' => 'allow_password_enabled')) }} Enable</label>
    </div>
    <div class="radio">
      <label>{{ Form::radio('allow_password', '0', true, array('id' => 'allow_password_disabled')) }} Disable</label>
    </div>
  </div>

  <div class="form-group">
    {{ Form::label('allow_privilege_escalation', 'Allow app that might be vulnerable to privilege escalation attack') }}
    <div class="radio">
      <label>{{ Form::radio('allow_privilege_escalation', '1', false, array('id' => 'allow_privilege_escalation_enabled')) }} Enable</label>
    </div>
    <div class="radio">
      <label>{{ Form::radio('allow_privilege_escalation', '0', true, array('id' => 'allow_privilege_escalation_disabled')) }} Disable</label>
    </div>
  </div>

  <div class="form-group">
    {{ Form::submit('Create', array('class' => 'btn btn-success')) }}
  </div>

</div>
{{ Form::close() }}

@endsection

@section('additional_js')
<script>

@if (isset($oldValues))

var old_value = {};
@foreach ( $oldValues->toArray() as $key => $value )
old_value['{{ $key }}'] = '{{ $value }}';
@endforeach

$( document ).ready(function() {

  for(var key in old_value) {

    $element = $('[name=' + key + ']');

    if ($element.is(':radio')) {
      $("input[name=" + key + "][value='" + old_value[key] + "']").prop("checked",true);
    } else {
      $element.val(old_value[key]);
    }

  }

});
@endif

</script>
@endsection
