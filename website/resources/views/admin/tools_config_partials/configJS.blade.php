<script>
var taint = [
  @foreach ( $disableableFields['taint'] as $element )
  '{{ $element }}',
  @endforeach
];

function disable(group) {
  for(var i = 0; i < group.length; i++) {
    $('input[name=' + group[i] + ']').prop('disabled', true);
    $('input[name=' + group[i] + ']').addClass("disabled");
  }
}

function enable(group) {
  for(var i = 0; i < group.length; i++) {
    $('input[name=' + group[i] + ']').prop("disabled", false);
    $('input[name=' + group[i] + ']').removeClass("disabled");
  }
}

$(function() {

  $('#taint_analysis_disabled').change(function() {
    if($(this).is(':checked')) {
      disable(taint);
    };
  });

  $('#taint_analysis_enabled').change(function() {
    if($(this).is(':checked')) {
      enable(taint);
    };
  });

});

@if (isset($errors) && $errors->any())

var error_fields = [
  @foreach ( array_keys($errors->toArray()) as $element )
  '{{ $element }}',
  @endforeach
];

$( document ).ready(function() {

  for(var i = 0; i < error_fields.length; i++) {

    if(error_fields[i] == 'submit') {
      $('#submit').prop('disabled', true);
      $('#submit').addClass("disabled");
    } else {
      $('input[name=' + error_fields[i] + ']').closest( "div" ).addClass('has-error has-feedback');
      $('input[name=' + error_fields[i] + ']').closest( "div" ).append('<span class="glyphicon glyphicon-remove form-control-feedback"></span>');
    }
  }

});

@endif

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

$( document ).ready(function() {
  if($('#taint_analysis_disabled').is(':checked')) {
    disable(taint);
  }
});

</script>
