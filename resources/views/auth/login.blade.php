<!doctype html>
<html lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#0D1442">
    <title>Attendify — Login</title>
    <meta name="description" content="Attendify: Sistem Presensi Berbasis GPS dan Selfie">
    <meta name="keywords" content="attendify, presensi gps, selfie, sekolah, pendidikan" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/icon/192x192.png') }}">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/attendify.css') }}">
    <link rel="manifest" href="__manifest.json">
    {{-- <script src="assets/js/tailwind.js"></script> --}}
</head>

<body class="attendify-login-body">

    <!-- loader -->
    <div id="loader">
        <div class="spinner-border text-primary" role="status"></div>
    </div>
    <!-- * loader -->


    <!-- App Capsule -->
    <div id="appCapsule" class="pt-0">

        <div class="login-form mt-1 attendify-login-shell">
            <div class="section attendify-login-brand">
                <img src="{{ asset('assets/img/logo-attendify-white.svg') }}" alt="Attendify" class="form-image attendify-login-logo">
                <div class="attendify-sparkle-row" aria-hidden="true">
                    <span class="attendify-sparkle-dot attendify-sparkle-dot--amber"></span>
                    <span>GPS &bull; Selfie &bull; Presensi</span>
                    <span class="attendify-sparkle-dot attendify-sparkle-dot--cyan"></span>
                </div>
            </div>

            <div class="attendify-login-card">
                <div class="section mt-1 attendify-login-header">
                    <h1>Attendify</h1>
                    <h4>Sistem Presensi Berbasis GPS dan Selfie</h4>

                    <!-- ✅ Error Alert -->
                    @if($errors->any())
                        <div class="alert alert-danger mt-3 attendify-alert-error" role="alert">
                            <ion-icon name="alert-circle-outline"></ion-icon>
                            <div>
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <!-- ✅ End Error Alert -->

                </div>
                <div class="section mt-2 mb-1">
                    <form action="/proseslogin" method="POST">
                        @csrf
                        <div class="form-group boxed">
                            <label class="form-label-text" for="nis">NIS / Username</label>
                            <div class="input-wrapper">
                                <input type="text" name="nis" class="form-control" id="nis" placeholder="Masukkan NIS atau Username">
                                <i class="clear-input">
                                    <ion-icon name="close-circle"></ion-icon>
                                </i>
                            </div>
                        </div>

                        <div class="form-group boxed">
                            <label class="form-label-text" for="password">Kata Sandi</label>
                            <div class="input-wrapper">
                                <input type="password" name="password" class="form-control" id="password" placeholder="Masukkan Kata Sandi">
                                <i class="clear-input">
                                    <ion-icon name="close-circle"></ion-icon>
                                </i>
                            </div>
                        </div>

                        {{-- <div class="form-links mt-2">
                            <div>
                                <a href="page-register.html">Register Now</a>
                            </div>
                            <div><a href="page-forgot-password.html" class="text-muted">Forgot Password?</a></div>
                        </div> --}}

                        <div class="form-button-group">
                            <button type="submit" class="btn btn-primary btn-block btn-lg attendify-login-submit">Masuk ke Attendify</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>


    </div>
    <!-- * App Capsule -->



    <!-- ///////////// Js Files ////////////////////  -->
    <!-- Jquery -->
    <script src="{{ asset ('assets/js/lib/jquery-3.4.1.min.js') }}"></script>
    <!-- Bootstrap-->
    <script src="{{ asset ('assets/js/lib/popper.min.js') }}"></script>
    <script src="{{ asset ('assets/js/lib/bootstrap.min.js') }}"></script>
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@5.0.0/dist/ionicons/ionicons.js"></script>
    <!-- Owl Carousel -->
    <script src="{{ asset ('assets/js/plugins/owl-carousel/owl.carousel.min.js') }}"></script>
    <!-- jQuery Circle Progress -->
    <script src="{{ asset ('assets/js/plugins/jquery-circle-progress/circle-progress.min.js') }}"></script>
    <!-- Base Js File -->
    <script src="{{ asset ('assets/js/base.js') }}"></script>


</body>

</html>
