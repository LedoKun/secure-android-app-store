@extends('admin.layout.master')

@section('content')

  @include('admin.layout.messages')
  @yield('form_open')

  @include('admin.tools_config_partials.general_information')
  @include('admin.tools_config_partials.general_properties')
  @include('admin.tools_config_partials.unified_analysis_conf')
  @include('admin.tools_config_partials.footnote')

  @yield('form_close')

  @section('additional_js')
    @include('admin.tools_config_partials.configJS')
  @endsection

  @section('additional_css')
    <style>
    </style>
  @endsection

@endsection
