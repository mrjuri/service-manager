<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('node_modules/bootstrap/dist/css/bootstrap.min.css') }}" crossorigin="anonymous" />
    <link rel="stylesheet" href="{{ asset('node_modules/@fortawesome/fontawesome-free/css/all.min.css') }}" crossorigin="anonymous" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

</head>
<body>
<div id="app">

    @yield('content')

</div>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="{{ asset('node_modules/jquery/dist/jquery.min.js') }}" crossorigin="anonymous"></script>
<script src="{{ asset('node_modules/popper.js/dist/popper.min.js') }}" crossorigin="anonymous"></script>
<script src="{{ asset('node_modules/bootstrap/dist/js/bootstrap.min.js') }}" crossorigin="anonymous"></script>
</body>
</html>
