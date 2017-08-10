@extends('store.layout.master')

@section('title')

{{ \App\SiteConfig::getSiteName() }}

@endsection

@section('content')
<!-- Display apps -->
<div class="container">

  <div class="row">

    <div class="top">
      <h1>404 Oops!</h1>
      <p class="lead">Where am I? Where are you?</p>
    </div>

</div> <!-- /container -->

@endsection
