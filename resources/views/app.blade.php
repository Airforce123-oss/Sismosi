<!DOCTYPE html>
<script src="{{ URL::to('assets/plugins/apexchart/apexcharts.min.js') }}"></script>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Sistem Monitoring Siswa') }}</title>

        <!-- Fonts and asset -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="shortcut icon" href="{{ URL::to('assets/img/favicon.png') }}">
        <link rel="stylesheet" href="{{ URL::to('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ URL::to('assets/plugins/feather/feather.css') }}">
        <link rel="stylesheet" href="{{ URL::to('assets/plugins/icons/flags/flags.css') }}">
        <link rel="stylesheet" href="{{ URL::to('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/1.9.2/tailwind.min.css">
        <link rel="stylesheet" href="{{ URL::to('assets/plugins/fontawesome/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ URL::to('assets/plugins/select2/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ URL::to('assets/css/style.css') }}">
        <link rel="stylesheet" href="{{ URL::to('assets/css/toastr.min.css') }}">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
        <script src="{{ URL::to('assets/js/toastr_jquery.min.js') }}"></script>
        <script src="{{ URL::to('assets/js/toastr.min.js') }}"></script>
        <script src="{{ URL::to('assets/js/jquery-3.6.0.min.js') }}"></script>
        <script src="{{ URL::to('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ URL::to('assets/js/feather.min.js') }}"></script>
        <script src="{{ URL::to('assets/plugins/select2/js/select2.min.js') }}"></script>
        <script src="{{ URL::to('assets/js/script.js') }}"></script>
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>





