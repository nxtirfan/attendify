@extends('layouts.guru.tabler')
@section('content')
    <div class="page-header d-print-none" aria-label="Page header">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Pemantauan Kehadiran</div>
                    <h2 class="page-title">Monitoring Presensi</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="input-icon mb-3">
                                        <span class="input-icon-addon">
                                            <!-- Download SVG icon from http://tabler.io/icons/icon/user -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-calendar">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                                <path d="M16 3v4" />
                                                <path d="M8 3v4" />
                                                <path d="M4 11h16" />
                                                <path d="M11 15h1" />
                                                <path d="M12 15v3" />
                                            </svg>
                                        </span>
                                        <input type="text" id="tanggal"  value="{{ date('Y-m-d') }}" name="tanggal" class="form-control"
                                            placeholder="Tanggal Presensi" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                    <table class="table table-striped table-hover table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>NIS</th>
                                                <th>Nama</th>
                                                <th>Angkatan</th>
                                                <th>Jam Masuk</th>
                                                <th>Foto</th>
                                                <th>Jam Pulang</th>
                                                <th>Foto</th>
                                                <th>Keterangan</th>
                                                <th>Peta</th>
                                            </tr>
                                        </thead>
                                        <tbody id="loadpresensi">

                                        </tbody>
                                    </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BEGIN MODAL TAMPILKAN PETA -->
    <div class="modal modal-blur fade" id="modal-tampilkanpeta" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lokasi Presensi Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="loadmap">

                </div>
            </div>
        </div>
    </div>
    <!-- END MODAL TAMPILKAN PETA -->
@endsection
@push('myscript')
    <script>
        $(function () {
            $("#tanggal").datepicker({
                autoclose: true,
                todayHighlight: true,
                format: "yyyy-mm-dd"
            });

            function loadpresensi() {
                var tanggal = $("#tanggal").val();
                $.ajax({
                    type: "POST",
                    url: "/getpresensi",
                    data: {
                        _token: "{{ csrf_token() }}",
                        tanggal: tanggal
                    
                    },
                    cache: false,
                    success: function (respond) {
                        $("#loadpresensi").html(respond);
                    }
                });
            }

            $("#tanggal").on("change", function (e) {
                loadpresensi();
            });

            loadpresensi();
        });

    </script>
@endpush