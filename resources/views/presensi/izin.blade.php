@extends('layouts.presensi')
@section('header')

    <!-- App Header -->
    <div class="appHeader bg-primary text-light attendify-subheader">
        <div class="left">
            <a href="javascript:;" class="headerButton goBack">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">Data Izin / Sakit</div>
        <div class="right"></div>
    </div>
    <!-- * App Header -->
@endsection

@section('content')
    <div class="attendify-subpage" style="margin-top: 68px">
        <div class="row">
            <div class="col">
                @php
                    $messagesuccess = Session::get('success');
                    $messageerror = Session::get('error');
                @endphp
                @if (Session::get('success'))
                    <div class="alert alert-info attendify-alert-info mb-2">
                        <ion-icon name="checkmark-circle-outline"></ion-icon>
                        <div>{{ $messagesuccess }}</div>
                    </div>
                @endif
                @if (Session::get('error'))
                    <div class="alert alert-danger attendify-alert-error mb-2">
                        <ion-icon name="alert-circle-outline"></ion-icon>
                        <div>{{ $messageerror }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col">
                @if ($dataizin->isEmpty())
                    <div class="attendify-empty-state attendify-empty-state--tab">
                        <svg class="attendify-empty-state__svg" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="2" y="2" width="44" height="44" rx="14" fill="#5AD8E6" fill-opacity="0.16" />
                            <path d="M24 11L26.1 18.9L34 21L26.1 23.1L24 31L21.9 23.1L14 21L21.9 18.9L24 11Z" fill="#2E3AC7" />
                            <circle cx="34" cy="14" r="2.5" fill="#F7A8D8" />
                        </svg>
                        <div class="attendify-empty-state__text">
                            <p class="attendify-empty-state__title">Belum ada pengajuan izin / sakit</p>
                            <p class="attendify-empty-state__desc">Ketuk tombol <strong>+</strong> di kanan bawah untuk mengajukan izin atau keterangan sakit.</p>
                        </div>
                    </div>
                @else
                    <ul class="listview image-listview attendify-listview">
                        @foreach ($dataizin as $d)
                            <li>
                                <div class="item">
                                    <div class="attendify-stat-badge {{ $d->status == 'izin' ? 'attendify-stat-badge--izin' : 'attendify-stat-badge--pink' }} mr-2">
                                        <ion-icon name="{{ $d->status == 'izin' ? 'calendar-outline' : 'medkit-outline' }}"></ion-icon>
                                    </div>
                                    <div class="in">
                                        <div>
                                            <div class="attendify-history-date">
                                                {{ \Carbon\Carbon::parse($d->tgl_izin)->translatedFormat('l, d F Y') }}
                                                <span class="attendify-badge {{ $d->status == 'izin' ? 'attendify-badge--izin' : 'attendify-badge--sakit' }} ml-1">
                                                    {{ $d->status == 'izin' ? 'Izin' : 'Sakit' }}
                                                </span>
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                {{ $d->keterangan }}
                                            </small>
                                        </div>
                                        @if ($d->status_approved == 'pending')
                                            <span class="badge badge-warning attendify-badge attendify-badge--terlambat">Pending</span>
                                        @elseif ($d->status_approved == 'disetujui')
                                            <span class="badge badge-primary attendify-badge attendify-badge--hadir">Disetujui</span>
                                        @elseif ($d->status_approved == 'ditolak')
                                            <span class="badge badge-danger attendify-badge attendify-badge--alpha">Ditolak</span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="fab-button bottom-right" style="margin-bottom: 70px">
        <a href="/presensi/buatizin" class="fab" title="Buat Pengajuan Izin / Sakit"><ion-icon name="add-outline"></ion-icon>
        </a>
    </div>
@endsection
