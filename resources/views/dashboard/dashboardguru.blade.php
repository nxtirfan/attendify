@extends('layouts.guru.tabler')
@section('content')
    {{-- <style>
        .navbar-expand-lg.navbar-vertical~.page-wrapper {
            margin-left: 20rem !important;
        }
    </style> --}}
    <div class="page-header d-print-none" aria-label="Page header">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <!-- Page pre-title -->
                    <div class="page-pretitle">Overview</div>
                    <h2 class="page-title">Dashboard Guru</h2>
                </div>

                <!-- BEGIN MODAL -->
                <!-- END MODAL -->
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row">

                {{-- <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span
                                        class="bg-primary text-white avatar"><!-- Download SVG icon from http://tabler.io/icons/icon/currency-dollar -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-users">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                            <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                            <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
                                        </svg>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">132</div>
                                    <div class="text-secondary">Siswa</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}

                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm attendify-stat-card-admin attendify-stat-card-admin--hadir">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span
                                        class="avatar attendify-avatar--hadir"><!-- Download SVG icon from http://tabler.io/icons/icon/currency-dollar -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-fingerprint">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M18.9 7a8 8 0 0 1 1.1 5v1a6 6 0 0 0 .8 3" />
                                            <path d="M8 11a4 4 0 0 1 8 0v1a10 10 0 0 0 2 6" />
                                            <path d="M12 11v2a14 14 0 0 0 2.5 8" />
                                            <path d="M8 15a18 18 0 0 0 1.8 6" />
                                            <path d="M4.9 19a22 22 0 0 1 -.9 -7v-1a8 8 0 0 1 12 -6.95" />
                                        </svg>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">{{ $rekappresensi->jmlhadir }}</div>
                                    <div class="text-secondary">Siswa Hadir</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm attendify-stat-card-admin attendify-stat-card-admin--izin">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span
                                        class="avatar attendify-avatar--izin"><!-- Download SVG icon from http://tabler.io/icons/icon/currency-dollar -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-file-text">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                            <path
                                                d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                            <path d="M9 9l1 0" />
                                            <path d="M9 13l6 0" />
                                            <path d="M9 17l6 0" />
                                        </svg>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">{{ $rekapizin->jmlizin }}</div>
                                    <div class="text-secondary">Siswa Izin</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm attendify-stat-card-admin attendify-stat-card-admin--sakit">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span
                                        class="avatar attendify-avatar--sakit"><!-- Download SVG icon from http://tabler.io/icons/icon/currency-dollar -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-mood-sick">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M12 21a9 9 0 1 1 0 -18a9 9 0 0 1 0 18z" />
                                            <path d="M9 10h-.01" />
                                            <path d="M15 10h-.01" />
                                            <path d="M8 16l1 -1l1.5 1l1.5 -1l1.5 1l1.5 -1l1 1" />
                                        </svg>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">{{ $rekapizin->jmlsakit }}</div>
                                    <div class="text-secondary">Siswa Sakit</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm attendify-stat-card-admin attendify-stat-card-admin--terlambat">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span
                                        class="avatar attendify-avatar--terlambat"><!-- Download SVG icon from http://tabler.io/icons/icon/currency-dollar -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-alarm">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M12 13m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                                            <path d="M12 10l0 3l2 0" />
                                            <path d="M7 4l-2.75 2" />
                                            <path d="M17 4l2.75 2" />
                                        </svg>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">{{ $rekappresensi->jmlterlambat }}</div>
                                    <div class="text-secondary">Siswa Terlambat</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection