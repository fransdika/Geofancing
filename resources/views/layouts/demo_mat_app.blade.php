<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'Dashboard' }} | {{ config('app.name') }}</title>
  <link rel="icon" href="{{ asset('matoxi/assets/images/favicon-32x32.png') }}" type="image/png">
  <link rel="stylesheet" href="{{ asset('matoxi/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}">
  <link rel="stylesheet" href="{{ asset('matoxi/assets/plugins/metismenu/metisMenu.min.css') }}">
  <link rel="stylesheet" href="{{ asset('matoxi/assets/plugins/metismenu/mm-vertical.css') }}">
  <link rel="stylesheet" href="{{ asset('matoxi/assets/plugins/simplebar/css/simplebar.css') }}">
  <link rel="stylesheet" href="{{ asset('matoxi/assets/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600&display=swap">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined">
  <link rel="stylesheet" href="{{ asset('matoxi/assets/css/bootstrap-extended.css') }}">
  <link rel="stylesheet" href="{{ asset('matoxi/sass/main.css') }}">
  <link rel="stylesheet" href="{{ asset('matoxi/sass/dark-theme.css') }}">
  <link rel="stylesheet" href="{{ asset('matoxi/sass/semi-dark.css') }}">
  <link rel="stylesheet" href="{{ asset('matoxi/sass/bordered-theme.css') }}">
  <link rel="stylesheet" href="{{ asset('matoxi/sass/responsive.css') }}">
  @stack('styles')
</head>
<body>
  @include('layouts.partials.mat_navbar')
  @include('layouts.partials.mat_sidebar')

  <!-- <main class="main-wrapper"> -->
    <!-- <div class="main-content"> -->
      @yield('content')
    <!-- </div> -->
  <!-- </main> -->

  @include('layouts.partials.footer')

  <div class="overlay btn-toggle"></div>

  <script src="{{ asset('matoxi/assets/js/bootstrap.bundle.min.js') }}" defer></script>
  <script src="{{ asset('matoxi/assets/js/jquery.min.js') }}" defer></script>
  <script src="{{ asset('matoxi/assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}" defer></script>
  <script src="{{ asset('matoxi/assets/plugins/metismenu/metisMenu.min.js') }}" defer></script>
  <script src="{{ asset('matoxi/assets/plugins/apexchart/apexcharts.min.js') }}" defer></script>
  <script src="{{ asset('matoxi/assets/js/index.js') }}" defer></script>
  <script src="{{ asset('matoxi/assets/plugins/peity/jquery.peity.min.js') }}" defer></script>
  <script>
    document.addEventListener('DOMContentLoaded',function(){
      if (window.jQuery) $(".data-attributes span").peity("donut")
    })
  </script>
  <script src="{{ asset('matoxi/assets/plugins/simplebar/js/simplebar.min.js') }}" defer></script>
  <script src="{{ asset('matoxi/assets/js/main.js') }}" defer></script>
  @stack('scripts')
</body>
</html>
