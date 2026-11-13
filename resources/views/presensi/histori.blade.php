@extends('layouts.presensi')
@section('header')

    <!-- App Header -->
    <div class="appHeader bg-primary text-light attendify-subheader">
        <div class="left">
            <a href="javascript:;" class="headerButton goBack">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">Histori Presensi</div>
        <div class="right"></div>
    </div>
    <!-- * App Header -->
@endsection

@section('content')
<div class="attendify-subpage" style="margin-top: 68px">
    <div class="card attendify-card mb-2">
        <div class="card-body">
            <div class="attendify-section-head">
                <h4 class="attendify-section-title">Filter Riwayat Kehadiran</h4>
                <p class="attendify-section-sub">Pilih periode bulan dan tahun untuk melihat rincian presensi Anda.</p>
            </div>
            <div class="row">
                <div class="col-7 pr-1">
                    <div class="form-group mb-2">
                        <label class="attendify-field-label" for="bulan">Bulan</label>
                        <select name="bulan" id="bulan" class="form-control attendify-input">
                            <option value="">Bulan</option>
                            @for ($i=1; $i<=12; $i++ ) 
                                <option value="{{ $i }}" {{ date("m") == $i ? 'selected' : '' }}>{{ $namabulan[$i] }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="col-5 pl-1">
                    <div class="form-group mb-2">
                        <label class="attendify-field-label" for="tahun">Tahun</label>
                        <select name="tahun" id="tahun" class="form-control attendify-input">
                            <option value="">Tahun</option>
                            @php
                                $tahunmulai = 2024;
                                $tahunskrng = date("Y")
                            @endphp
                            @for ($tahun=$tahunmulai; $tahun<= $tahunskrng; $tahun++)
                                <option value="{{ $tahun }}" {{ date("Y") == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form-group mb-0">
                        <button class="btn btn-primary btn-block attendify-btn-action" id="getdata">
                            <ion-icon name="search-outline"></ion-icon> Tampilkan Histori
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col" id="showhistori">
            <div class="attendify-empty-state attendify-empty-state--tab">
                <svg class="attendify-empty-state__svg" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect x="2" y="2" width="44" height="44" rx="14" fill="#2E3AC7" fill-opacity="0.08" />
                    <path d="M24 11L26.1 18.9L34 21L26.1 23.1L24 31L21.9 23.1L14 21L21.9 18.9L24 11Z" fill="#2E3AC7" />
                    <circle cx="34" cy="14" r="2.5" fill="#5AD8E6" />
                </svg>
                <div class="attendify-empty-state__text">
                    <p class="attendify-empty-state__title">Siap menampilkan histori</p>
                    <p class="attendify-empty-state__desc">Ketuk tombol <strong>Tampilkan Histori</strong> di atas untuk memuat data presensi.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<script>
    $(function() {
        $("#getdata").click(function(e) {
            var bulan = $("#bulan").val();
            var tahun = $("#tahun").val();
            $.ajax({
               type:'POST',
               url:'/gethistori', 
               data: {
                    _token: "{{ csrf_token() }}",
                    bulan: bulan,
                    tahun: tahun
               },
               cache: false,
               success: function(respond) {
                    $("#showhistori").html(respond);
               }
            });
        });
    });
</script>
@endpush
