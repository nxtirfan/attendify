@extends('layouts.presensi')
@section('header')
    <!-- App Header -->
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="javascript:;" class="headerButton goBack">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">Attendify</div>
        <div class="right"></div>
    </div>
    <!-- * App Header -->
    <style>
        .webcam-capture {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            width: 100% !important;
            margin-bottom: 10px !important;
        }
        
        .webcam-capture video {
            display: inline-block;
            max-width: 100% !important;
            margin: auto;
            height: auto !important;
            border-radius: 15px;
        }

        #map { 
            height: 180px; 
            margin-bottom: 180px !important;
        }

        /* .btn-block {
            width: 100%;
            display: block;
        } */

        .row {
            margin-left: 0;
            margin-right: 0;
        }

        .col {
            padding-left: 0;
            padding-right: 0;
        }
    </style>
     
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
     <!-- Make sure you put this AFTER Leaflet's CSS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection

@section('content')
    <div class="row" style="margin-top: 70px">
        <div class="col">
            <input type="hidden" id="lokasi">
            <div class="webcam-capture">
                <video></video>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            @if ($cek > 0)
            <button id="takeabsen" class="btn btn-danger btn-block">
                <ion-icon name="camera-outline"></ion-icon>   
                Presensi Pulang
            </button>
            @else
            <button id="takeabsen" class="btn btn-primary btn-block">
                <ion-icon name="camera-outline"></ion-icon>   
                Presensi Masuk
            </button>
            @endif

        </div>
    </div>
    <div class="row mt-2">
        <div class="col">
            <div id="map"></div>
        </div>
    </div>

<audio id="notifikasi_in" src="{{ asset('assets/sounds/success.mp3') }}" preload="auto"></audio>    
<audio id="notifikasi_out" src="{{ asset('assets/sounds/success.mp3') }}" preload="auto"></audio>  
<audio id="radius_sound" src="{{ asset('assets/sounds/error2.mp3') }}" preload="auto"></audio>    

@endsection

@push('myscript')
<script>

    var notifikasi_in = document.getElementById('notifikasi_in');
    var notifikasi_out = document.getElementById('notifikasi_out');
    var radius_sound = document.getElementById('radius_sound');


    Webcam.set({
        height: 480,
        width: 640,
        image_format: 'jpeg',
        jpeg_quality: 80
    })

    Webcam.attach('.webcam-capture');

    var lokasi = document.getElementById('lokasi');
    if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
    }

    function successCallback(position) {
        lokasi.value = position.coords.latitude + ',' + position.coords.longitude;
        var map = L.map('map').setView([position.coords.latitude, position.coords.longitude], 18);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        var marker = L.marker([position.coords.latitude, position.coords.longitude]).addTo(map);
        // var circle = L.circle([-7.015284, 110.380915], {-6.996044, 110.392116
        var circle = L.circle([{{ $sekolah_lat }}, {{ $sekolah_lng }}], {

            color: 'red',
            fillColor: '#f03',
            fillOpacity: 0.5,
            radius: {{ $radius_m }}
        }).addTo(map);
    }

    function errorCallback(error) {
        var pesan = 'Gagal mendapatkan lokasi. Izinkan akses lokasi pada browser, lalu muat ulang halaman.';
        if (error.code === error.PERMISSION_DENIED) {
            pesan = 'Akses lokasi ditolak. Aktifkan izin lokasi untuk situs ini, lalu muat ulang halaman.';
        }
        lokasi.value = '';
        Swal.fire({
            title: 'Lokasi tidak tersedia',
            text: pesan,
            icon: 'warning',
            confirmButtonText: 'OK'
        });
    }

    $("#takeabsen").click(function() {
        if (!$("#lokasi").val()) {
            Swal.fire({
                title: 'Lokasi belum siap',
                text: 'Mohon izinkan akses lokasi dan tunggu hingga lokasi terdeteksi sebelum presensi.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }
        Webcam.snap(function(uri) {
            image = uri;
        });
        var lokasi = $("#lokasi").val();
        $.ajax({
            url: "/presensi/store",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                image: image,
                lokasi: lokasi,
            },
            cache: false,
            success: function(respond) {
                var status = respond.split("|");
                if(status[0] == "success") {   
                    if(status[2] == "in") {
                        notifikasi_in.play();
                    } else {
                        notifikasi_out.play();
                    }
                    Swal.fire({
                        title: 'Berhasil',
                        text: status[1],
                        icon: 'success',
                        confirmButtonText: 'OK'
                    })
                    setTimeout("location.href = '/dashboard';", 1000);
                } else {
                    if (status[2] == "radius") {
                        radius_sound.play();
                    }
                    Swal.fire({
                        title: 'Gagal !',
                        text: status[1],
                        icon: 'error',
                        confirmButtonText: 'OK'
                    })
                }
            }
        });
    });
</script>
@endpush