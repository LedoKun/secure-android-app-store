@extends('admin.layout.master')

@section('title', 'Analysis Options')

@section('content')

<legend>Active Configurations</legend>

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Rules Name</th>
        <th>Detect Cryptographic<br />APIs Misuse</th>
        <th>Perform Vulnerability<br />Scan</th>
        <th>Use Custom Policy</th>
        <th>Perform Taint Analysis</th>
        <th>Last Update</th>
        <th></th>
      </tr>
    </thead>

    <tbody class="element">
      @if($currentDefault)
      <tr>

        <td>
          @if (strlen($currentDefault->rule_name) > 20)
          {{ substr($currentDefault->rule_name, 0, 9) }} ... {{ substr($currentDefault->rule_name, -6) }}
          @else
          {{ $currentDefault->rule_name }}
          @endif
        </td>

        <td>{{ ($currentDefault->api_misuse ? "Enabled" : "Disabled") }}</td>
        <td>{{ ($currentDefault->vulnerability_scan ? "Enabled" : "Disabled") }}</td>
        <td>{{ (($currentDefault->custom_policy == '') ? '-' : $currentDefault->custom_policy) }}</td>
        <td>{{ ($currentDefault->taint_analysis ? "Enabled" : "Disabled") }}</td>
        <td>{{ $currentDefault->created_at }}</td>
        <td>
          <a href="{{ URL::to('/admin/rules/'.$currentDefault->rule_id.'/edit') }}" id="details" title="More information"><i class="fa fa-window-maximize fa-fw" aria-hidden="true"></i></a>
        </td>
      </tr>
      @else
      <td colspan="9" class="text-center">No active rules</td>
      @endif
    </tbody>
  </table>
</div>

<legend>Existing Configurations</legend>

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>#</th>
        <th>Rules Name</th>
        <th>Detect Cryptographic<br />APIs Misuse</th>
        <th>Perform Vulnerability<br />Scan</th>
        <th>Use Custom Policy</th>
        <th>Perform Taint Analysis</th>
        <th>Last Update</th>
        <th></th>
      </tr>
    </thead>

    <tbody class="element">
      @if($existingRules->isNotEmpty())
      @foreach ($existingRules as $rule)
      <tr>
        <td>{{ $rule->id }}</td>

        <td>
          @if (strlen($rule->rule_name) > 20)
          {{ substr($rule->rule_name, 0, 9) }} ... {{ substr($rule->rule_name, -6) }}
          @else
          {{ $rule->rule_name }}
          @endif
        </td>

        <td>{{ ($rule->api_misuse ? "Enabled" : "Disabled") }}</td>
        <td>{{ ($rule->vulnerability_scan ? "Enabled" : "Disabled") }}</td>
        <td>{{ (($rule->custom_policy == '') ? '-' : $rule->custom_policy) }}</td>
        <td>{{ ($rule->taint_analysis ? "Enabled" : "Disabled") }}</td>
        <td>{{ $rule->updated_at }}</td>
        <td>
          <form>
            <input type="hidden" value="{{ $rule->id }}" id="id">
            <a href="{{ URL::to('/admin/rules/'.$rule->id.'/edit') }}" id="details" title="More information"><i class="fa fa-window-maximize fa-fw" aria-hidden="true"></i></a>
            | <a href="#" id="default" title="Set as default rule"><i class="fa fa-cogs text-success" aria-hidden="true"></i></a>
            | <a href="#" id="cancel" title="Delete"><i class="fa fa-window-close fa-fw text-danger" aria-hidden="true"></i></a>
          </form>
        </td>
      </tr>
      @endforeach
      @else
      <td colspan="9" class="text-center">No existing rules</td>
      @endif
    </tbody>
  </table>

  <div class="text-center">
    {{ $existingRules->render() }}
  </div>
</div>

<legend>Active Configurations History</legend>

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>#</th>
        <th>Rules #</th>
        <th>Rules Name</th>
        <th>Detect Cryptographic<br />APIs Misuse</th>
        <th>Perform Vulnerability<br />Scan</th>
        <th>Use Custom Policy</th>
        <th>Perform Taint Analysis</th>
        <th>Last Used</th>
      </tr>
    </thead>

    <tbody class="element">
      @if($existingRules->isNotEmpty())
      @foreach ($historyDefault as $element)
      <tr>
        <td>{{ $element->id }}</td>
        <td>{{ $element->rule_id }}</td>

        <td>
          @if (strlen($element->rule_name) > 20)
          {{ substr($element->rule_name, 0, 9) }} ... {{ substr($element->rule_name, -6) }}
          @else
          {{ $element->rule_name }}
          @endif
        </td>

        <td>{{ ($element->api_misuse ? "Enabled" : "Disabled") }}</td>
        <td>{{ ($element->vulnerability_scan ? "Enabled" : "Disabled") }}</td>
        <td>{{ (($element->custom_policy == '') ? '-' : $element->custom_policy) }}</td>
        <td>{{ ($element->taint_analysis ? "Enabled" : "Disabled") }}</td>
        <td>{{ $element->created_at }}</td>
      </tr>
      @endforeach
      @else
      <td colspan="9" class="text-center">No history of default rules</td>
      @endif
    </tbody>

  </table>

  <div class="text-center">
    {{ $historyDefault->render() }}
  </div>

</div>

@endsection

@section('additional_js')

<script>
//Remove button
$(document).on('click', '#cancel', function(e) {

  var button = this;

  $.ajax({

    url       : '{{ URL::to('admin/rules') }}/' + $(button).closest('form').find('#id').val(),
    type      : 'post',
    dataType  : 'json',
    data      : {
      _token  : '{{ csrf_token() }}',
      _method : 'DELETE',
    },

    success   : function( data ) {

      if(data.hasOwnProperty('success') && data.success == false) {

        alert('The server cannot fulfill your request with the result\nPlease try again later.');

      } else if(data.hasOwnProperty('success') && data.success == true) {

        $(button).closest('tr').remove();

      }

    },

    error     : function( xhr,status,error ) {

      alert('The server cannot fulfill your request with the result:\n\n'+ error +'\n\nPlease try again later.');

    },

  });

  return false;
});

//Set default button
$(document).on('click', '#default', function(e) {

  var button = this;

  $.ajax({

    url       : '{{ URL::to('admin/rules/default') }}/' + $(button).closest('form').find('#id').val(),
    type      : 'post',
    dataType  : 'json',
    data      : {
      _token  : '{{ csrf_token() }}',
      _method : 'PATCH',
    },

    success   : function( data ) {

      if(data.hasOwnProperty('success') && data.success == false) {

        alert(data.msg);

      } else if(data.hasOwnProperty('success') && data.success == true) {

        if(confirm(data.msg)){
          window.location.reload();
        }

      }

    },

    error     : function( xhr,status,error ) {

      alert('The server cannot fulfill your request with the result:\n\n'+ error +'\n\nPlease try again later.');

    },

  });

  return false;
});

</script>
@endsection

@section('additional_css')
<style>
</style>
@endsection
