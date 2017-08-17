@if (isset($errors) && $errors->any())
<div class="alert alert-danger alert-dismissibler" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <span class="sr-only">Error:</span>
  @foreach ($errors->all() as $error)
  <p>{{ $error }}<br/></p>
  @endforeach
</div>
@endif

@if (isset($info))
<div class="alert alert-info alert-dismissibler" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <span class="sr-only">Error:</span>
  @foreach ($info as $msg)
  <p>{{ $msg }}<br/></p>
  @endforeach
</div>
@endif
