<legend>Analysis Parameters</legend>

<div class="form-group">

  <div class="panel panel-default">
    <!-- Default panel contents -->
    <div class="panel-heading">APIs and Permissions Misuse Detection</div>

    <ul class="list-group">

      <li class="list-group-item">
        <div class="form-group">
          {{ Form::label('api_misuse', 'Cryptographic API misuse detection') }}
          <div class="radio">
            <label>{{ Form::radio('api_misuse', '1', true, array('id' => 'api_misuse_enabled')) }} Enable</label>
          </div>
          <div class="radio">
            <label>{{ Form::radio('api_misuse', '0', false, array('id' => 'api_misuse_disabled')) }} Disable</label>
          </div>
        </div>
      </li>

      <li class="list-group-item">
        <div class="form-group">
          {{ Form::label('vulnerability_scan', 'Scan for app vulnerability') }}
          <div class="radio">
            <label>{{ Form::radio('vulnerability_scan', '1', true, array('id' => 'vulnerability_scan_enabled')) }} Enable</label>
          </div>
          <div class="radio">
            <label>{{ Form::radio('vulnerability_scan', '0', false, array('id' => 'vulnerability_scan_disabled')) }} Disable</label>
          </div>
        </div>
      </li>

      <li class="list-group-item">
        <div class="form-group">
          {{ Form::label('custom_policy', 'Enter a filename of a custom policy to be used with EviCheck (Leave empty to disable)') }}
          {{ Form::text('custom_policy', '', array('class' => 'form-control')) }}
        </div>
      </li>
    </ul>
  </div>
</div>

<div class="panel panel-default">
  <!-- Default panel contents -->
  <div class="panel-heading">Taint Analysis</div>

  <ul class="list-group">

    <li class="list-group-item">
      <div class="form-group">
        {{ Form::label('taint_analysis', 'Perform taint analysis') }}
        <div class="radio">
          <label>{{ Form::radio('taint_analysis', '1', true, array('id' => 'taint_analysis_enabled')) }} Enable</label>
        </div>
        <div class="radio">
          <label>{{ Form::radio('taint_analysis', '0', false, array('id' => 'taint_analysis_disabled')) }} Disable</label>
        </div>
      </div>
    </li>

    <li class="list-group-item">
      <div class="form-group">
        {{ Form::label('taint_aplength', 'Set access path length') }}
        {{ Form::text('taint_aplength', '5', array('class' => 'form-control', 'required' => 'required')) }}
      </div>
    </li>

    <li class="list-group-item">
      <div class="form-group">
        {{ Form::label('taint_nocallbacks', 'Callback analysis') }}
        <div class="radio">
          <label>{{ Form::radio('taint_nocallbacks', '1', false, array('id' => 'taint_nocallbacks_enabled')) }} Enable</label>
        </div>
        <div class="radio">
          <label>{{ Form::radio('taint_nocallbacks', '0', true, array('id' => 'taint_nocallbacks_disabled')) }} Disable</label>
        </div>
      </div>
    </li>

    <li class="list-group-item">
      <div class="form-group">
        {{ Form::label('taint_sysflows', 'Also analyze classes in system packages') }}
        <div class="radio">
          <label>{{ Form::radio('taint_sysflows', '1', false, array('id' => 'taint_sysflows_enabled')) }} Enable</label>
        </div>
        <div class="radio">
          <label>{{ Form::radio('taint_sysflows', '0', true, array('id' => 'taint_sysflows_disabled')) }} Disable</label>
        </div>
      </div>
    </li>

    <li class="list-group-item">
      <div class="form-group">
        {{ Form::label('taint_implicit', 'Implicit flows') }}
        <div class="radio">
          <label>{{ Form::radio('taint_implicit', '1', false, array('id' => 'taint_implicit_enabled')) }} Enable</label>
        </div>
        <div class="radio">
          <label>{{ Form::radio('taint_implicit', '0', true, array('id' => 'taint_implicit_disabled')) }} Disable</label>
        </div>
      </div>
    </li>

    <li class="list-group-item">
      <div class="form-group">
        {{ Form::label('taint_static', 'Static field tracking') }}
        <div class="radio">
          <label>{{ Form::radio('taint_static', '1', false, array('id' => 'taint_static_enabled')) }} Enable</label>
        </div>
        <div class="radio">
          <label>{{ Form::radio('taint_static', '0', true, array('id' => 'taint_static_disabled')) }} Disable</label>
        </div>
      </div>
    </li>
  </ul>
</div>
