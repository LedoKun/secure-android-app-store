@extends('admin.layout.master')

@section('title', 'Published Apps')

@section('content')

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>#</th>
        <th>Package Name</th>
        <th>Size (MB)</th>
        <th>SHA256</th>
        <th>Visible On Store</th>
        <th>Status Updated</th>
        <th>Created</th>
        <th></th>
      </tr>
    </thead>
    <tbody class="element">
      @if($results->isNotEmpty())
        @foreach ($results as $result)
        <tr>
          <td>{{ $result->id }}</td>
          <td>
            @if (strlen($result->package_name) > 20)
              {{ substr($result->package_name, 0, 9) }} ... {{ substr($result->package_name, -6) }}
            @else
              {{ $result->package_name }}
            @endif
          </td>
          <td>{{ ceil($result->size) }}</td>
          <td>{{ substr($result->sha256, 0, 10)."..." }}</td>
          <td>
            @if ($result->isVisible)
              <span class="label label-success">Visible</span>
            @else
              <span class="label label-danger">Not Visible</span>
            @endif
          </td>
          <td>
            {{ $result->updated_at }}
          </td>
          <td>
            {{ $result->created_at }}
          </td>
          <td>
            <form>
              <input type="hidden" value="{{ $result->id }}" id="id">
              <input type="hidden" value="{{ $result->sha256 }}" id="hash">
              <a href="{{ URL::to('/admin/upload/'.$result->id) }}" id="details" title="More information"><i class="fa fa-window-maximize fa-fw" aria-hidden="true"></i></a>
              <a href="#" id="cancel" title="Delete"><i class="fa fa-window-close fa-fw text-danger" aria-hidden="true"></i></a>
            </form>
          </td>
        </tr>
        @endforeach
      @else
        <tr>
          <td colspan="8" class="text-center">No published app...</td>
        </tr>
      @endif
    </tbody>
  </table>

  <div class="text-center">
    {{ $results->render() }}
  </div>

</div>
@endsection

@section('additional_js')

<script>
//Remove button
$(document).on('click', '#cancel', function(e) {

  var button = this;

  $.ajax({

    url       : '{{ URL::to('admin/upload') }}/' + $(button).closest('form').find('#id').val(),
    type      : 'post',
    dataType  : 'json',
    data      : {
      hash    : $(button).closest('form').find('#hash').val(),
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
</script>
@endsection
