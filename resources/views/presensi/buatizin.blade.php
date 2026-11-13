@extends('layouts.presensi')
@section('header')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/css/materialize.min.css">
    <style>
    .datepicker-modal {
        max-height: 430px !important;
        border-radius: 16px !important;
    }
    .datepicker-date-display {
        background: linear-gradient(135deg, #0D1442 0%, #2E3AC7 100%) !important;
    }
    </style>
    <!-- App Header -->
    <div class="appHeader bg-primary text-light attendify-subheader">
        <div class="left">
            <a href="javascript:;" class="headerButton goBack">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">Form Izin</div>
        <div class="right"></div>
    </div>
    <!-- * App Header -->
@endsection

@section('content')
    <div class="attendify-subpage" style="margin-top: 68px">
        <div class="card attendify-card">
            <div class="card-body">
                <div class="attendify-section-head">
                    <h4 class="attendify-section-title">Pengajuan Izin / Sakit</h4>
                    <p class="attendify-section-sub">Lengkapi tanggal, status ketidakhadiran, dan alasan pengajuan dengan jelas.</p>
                </div>

                <form method="POST" action="/presensi/storeizin" id="frmizin" class="attendify-form-clean">
                    @csrf
                    <div class="form-group mb-2">
                        <label class="attendify-field-label" for="tgl_izin">Tanggal Izin / Sakit</label>
                        <input type="text" id="tgl_izin" name="tgl_izin" class="form-control datepicker attendify-input" placeholder="Pilih Tanggal (TTTT-BB-HH)">
                    </div>

                    <div class="form-group mb-2">
                        <label class="attendify-field-label" for="status">Jenis Pengajuan</label>
                        <select name="status" id="status" class="form-control browser-default attendify-input">
                            <option value="" disabled selected>Pilih Status (Izin / Sakit)</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="attendify-field-label" for="keterangan">Keterangan / Alasan</label>
                        <textarea name="keterangan" id="keterangan" class="form-control attendify-textarea" rows="5"
                            placeholder="Tuliskan alasan izin atau sakit secara singkat..."></textarea>
                    </div>

                    <div class="form-group mb-0">
                        <button class="btn btn-primary w-100 attendify-btn-action">
                            <ion-icon name="paper-plane-outline"></ion-icon> Kirim Pengajuan
                        </button>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
@endsection
@push('myscript')
    <script>
        var currYear = (new Date()).getFullYear();

        $(document).ready(function () {
            $(".datepicker").datepicker({
                format: "yyyy-mm-dd"
            });

            $("#tgl_izin").change(function (e) {
                var tgl_izin = $(this).val();
                $.ajax({
                    type: 'POST',
                    url: '/presensi/cekizin',
                    data: {
                        _token: "{{ csrf_token() }}",
                        tgl_izin: tgl_izin
                    },
                    cache: false,
                    success: function (respond) {
                        if(respond == 1) {
                            Swal.fire({
                                title: 'Oops!',
                                text: 'Anda Sudah Melakukan Izin Pada Tanggal ini!',
                                icon: 'warning'
                            }).then((result) => {
                                $("#tgl_izin").val("");
                            });

                        }
                    }

                });
            });

            $("#frmizin").submit(function () {
                var tgl_izin = $("#tgl_izin").val();
                var status = $("#status").val();
                var keterangan = $("#keterangan").val();

                if (tgl_izin === "") {
                    Swal.fire({
                        title: 'Oops!',
                        text: 'Tanggal Harus diisi!',
                        icon: 'warning',
                    });
                    return false;
                } else if (status == "") {
                    Swal.fire({
                        title: 'Oops!',
                        text: 'Status Harus diisi!',
                        icon: 'warning',
                    });
                    return false;
                } else if (keterangan == "") {
                    Swal.fire({
                        title: 'Oops!',
                        text: 'Keterangan Harus diisi!',
                        icon: 'warning',
                    });
                    return false;   
                }

                // if (tgl_izin === "" || status === null || keterangan === "") {
                //     // alert('Tanggal harus diisi!');
                //     // return false;
                //     M.toast({
                //         html: 'Semua field harus diisi!',
                //         classes: 'red'
                //     });
                //     return;
                // }

                // // Simulasi pengiriman data
                // M.toast({
                //     html: 'Izin berhasil dikirim!',
                //     classes: 'green'
                // });

                // // Reset form
                // $(this).trigger("reset");
            });
        });
    </script>
@endpush
