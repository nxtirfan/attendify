<!doctype html>
<html lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#2E3AC7">
    <title>Attendify</title>
    <meta name="description" content="Attendify: Sistem Presensi Berbasis GPS dan Selfie">
    <meta name="keywords" content="bootstrap 4, mobile template, cordova, phonegap, mobile, html" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/icon/192x192.png') }}">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/attendify.css') }}">
    <link rel="manifest" href="__manifest.json">
</head>

<body class="attendify-student-body" style="background-color:#F4F6FE;">

    <!-- loader -->
    <div id="loader">
        <div class="spinner-border text-primary" role="status"></div>
    </div>
    <!-- * loader -->

    <!-- Attendify Branded Topbar -->
    <header class="attendify-topbar">
        <a href="/dashboard" class="attendify-topbar__brand">
            <img src="{{ asset('assets/img/logo-attendify-indigo.svg') }}" alt="Attendify" class="attendify-topbar__logo">
        </a>
        <span class="attendify-topbar__pill">
            <span class="attendify-topbar__pill-dot"></span>
            Presensi GPS
        </span>
    </header>
    <!-- * Attendify Branded Topbar -->

    @yield('header')

    <!-- App Capsule -->
    <div id="appCapsule">
        @yield('content')
    </div>
    <!-- * App Capsule -->


@include('layouts.bottom-nav')


@include('layouts.script')



</body>

</html>
