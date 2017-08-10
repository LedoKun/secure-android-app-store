<legend>General Information</legend>

<div class="form-group">

  <div class="form-group">
    {{ Form::label('rule_name', 'Rule name') }}
    {{ Form::text('rule_name', null, array('class' => 'form-control', 'required' => 'required', 'autofocus' => 'autofocus')) }}
  </div>

  <div class="form-group">
    {{ Form::label('comments', 'Comments') }}
    {{ Form::textarea('comments', null, array('class' => 'form-control')) }}
  </div>

</div>
