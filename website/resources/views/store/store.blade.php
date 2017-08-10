@extends('store.layout.master')

@section('title')

{{ \App\SiteConfig::getSiteName() }}

@endsection

@section('content')
<!-- Display apps -->
<div class="container">

  <div class="row">
    @if ($apps->count() == 0)

    <div class="top">
      <h1>Nothing Here At The Moment</h1>
      <p class="lead">We will know how to accommodate ourselves in a short a while</p>
    </div>

    @else

    <div class="top">
      <h1>Check Out These Cool Apps</h1>
      <p class="lead">They are safe, we checked these apps for you.</p>
    </div>

  </div>

  <?php $i = 0; ?>

  @foreach ($apps as $app)

  @if ($i % 4 == 0)
  <div class="row">
    @endif

    <?php

    $filename_without_ext = basename($app->filename, '.apk');
    $icon_filename = $filename_without_ext.'_icon.png';

    if(\Storage::disk('apk_png')->exists($icon_filename)){
      $icon_url = asset('storage/apk/png/'.$icon_filename);
    } else {
      $icon_url = asset('img/no_icon.png');
    }

    if(strlen($app->apk_label) != 0) {
      $icon_alt = $app->apk_label;
    } else {
      $icon_alt = $app->package_name;
    }

    $icon_alt .= " app icon";

    if(strlen($app->apk_label) != 0) {
      $app_label = $app->apk_label;
    } else {
      $app_label = $app->package_name;
    }

    if(strlen($app_label) > 25) {
      $app_label = substr($app_label, 0, 17) . '...' . substr($app_label, -5);
    }

    // Read the app permissions
    $permission_filename = $filename_without_ext.'_perm.json';

    if (!Storage::disk('apk_permission')->exists($permission_filename)) {
      $permissions = null;
    } else {
      $permissions = json_decode(Storage::disk('apk_permission')->get($permission_filename), true);
    }

    ?>

    <div class="col-xs-6 col-sm-3">
      <div class="thumbnail">
        <img class="app-icon text-center" src="{{ $icon_url }}" alt="{{ $icon_alt }}">
        <div class="caption">

          <h4 class="text-center">{{ $app_label }}</h4>

          <dl>
            <dt>
              Version
            </dt>
            <dd>
              {{ $app->version }}
            </dd>
            <dt>
              System Requirements
            </dt>
            <dd>
              {{ $app->min_sdk_platform }}
            </dd>
            <dt>
              Size
            </dt>
            <dd>
              {{ $app->size }} MB
            </dd>
            <dt>
              <a class="permission_toggle" href="javascript:togglePermission({{ $app->id }});">
                Permissions »
              </a>
            </dt>
            <dd>
              <div id="permission_{{ $app->id }}" class="permission_list">
                <ul>
                  @foreach ($permissions as $detail)

                  <?php

                  if (strlen($detail['description']) == 0) {
                    continue;
                  }

                  ?>

                  <li class="small 
                  @if($detail['flags']['danger'])
                  text-danger
                  @elseif($detail['flags']['warning'])
                  text-warning
                  @elseif($detail['flags']['cost'])
                  text-info
                  @endif
                  ">
                  {{ $detail['description'] }}
                </li>

                @endforeach
              </ul>
            </div>
          </dd>
          <dd class="detail-btn text-center">
            <a class="btn btn-success" href="{{ asset('storage/apk/' . $app->filename) }}" role="button">Download »</a>
          </dd>
        </dl>

      </div>
    </div>
  </div>

  <?php $i++; ?>

  @if ($i % 2 == 0)
  <!-- Add the extra clearfix for only the required viewport -->
  <div class="clearfix visible-xs-block"></div>

  @endif

  @if ( ($i % 4 == 0) || ($apps->count() == $i-1) )
</div> <!-- /row -->
@endif

@endforeach

@endif

<div class="row text-center">
  {!! $apps->render() !!}
</div>

</div> <!-- /container -->

@endsection

@section('additional_js')
<script>
function togglePermission(id) {
  $('#permission_'+id).toggle();
}
</script>
@endsection
