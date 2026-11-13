@extends('layouts.presensi')
@section('content')

    <!-- App Capsule -->
    <div id="appCapsule">
        <div class="section attendify-hero-section" id="user-section">
            <div id="user-detail">
                <div class="avatar">
                    @if(!empty(Auth::user()->siswa->foto))
                    <!-- If user has a photo, display it -->
                    @php
                        $path = Storage::url('public/uploads/siswa/' . Auth::user()->siswa->foto);
                    @endphp
                    <img src="{{ $path }}" alt="avatar" class="imaged w64 rounded" style="height: 64px; width: 64px;">
                    @else
                    <img src="assets/img/sample/avatar/avatar1.jpg" alt="avatar" class="imaged w64 rounded">
                    @endif
                </div>
                <div id="user-info">
                    <h2 id="user-name">
                        {{  Auth::user()->siswa->nama_lengkap }}
                    </h2>
                    <span id="user-role">{{  Auth::user()->role }}</span>
                </div>
            </div>
        </div>

        <div class="section" id="menu-section">
            <div class="card attendify-card">
                <div class="card-body text-center">
                    <a href="/presensi/create" class="attendify-cta-presensi">
                        <div class="attendify-cta-presensi__left">
                            <span class="attendify-cta-presensi__icon">
                                <ion-icon name="camera-outline"></ion-icon>
                            </span>
                            <div>
                                <p class="attendify-cta-presensi__title">Ambil Presensi Hari Ini</p>
                                <p class="attendify-cta-presensi__sub">Verifikasi koordinat GPS &amp; foto selfie</p>
                            </div>
                        </div>
                        <ion-icon name="chevron-forward-outline" class="attendify-cta-presensi__arrow"></ion-icon>
                    </a>

                    <div class="list-menu">
                        <div class="item-menu text-center">
                            <div class="menu-icon">
                                <a href="/editprofile" class="attendify-menu-indigo" style="font-size: 40px;">
                                    <ion-icon name="person-sharp"></ion-icon>
                                </a>
                            </div>
                            <div class="menu-name">
                                <span class="text-center">Profil</span>
                            </div>
                        </div>
                        <div class="item-menu text-center">
                            <div class="menu-icon">
                                <a href="/presensi/izin" class="attendify-menu-cyan" style="font-size: 40px;">
                                    <ion-icon name="calendar-number"></ion-icon>
                                </a>
                            </div>
                            <div class="menu-name">
                                <span class="text-center">Izin</span>
                            </div>
                        </div>
                        <div class="item-menu text-center">
                            <div class="menu-icon">
                                <a href="/presensi/histori" class="attendify-menu-amber" style="font-size: 40px;">
                                    <ion-icon name="document-text"></ion-icon>
                                </a>
                            </div>
                            <div class="menu-name">
                                <span class="text-center">Histori</span>
                            </div>
                        </div>
                        <div class="item-menu text-center">
                            <div class="menu-icon">
                                <a href="/proseslogout" class="attendify-menu-pink" style="font-size: 40px;">
                                    <ion-icon name="log-out-outline"></ion-icon>
                                </a>
                            </div>
                            <div class="menu-name">
                                Keluar
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section mt-2" id="presence-section">
            <div class="todaypresence">
                <div class="row">
                    <div class="col-6">
                        <div class="card attendify-presence-card attendify-presence-card--in">
                            <div class="card-body">
                                <div class="presencecontent mt-0" style="min-height: 48px;">
                                    <div class="iconpresence">
                                        @if ($presensitoday != null)
                                            @php
                                                $path = Storage::url('public/uploads/absensi/' . $presensitoday->foto_in);
                                            @endphp
                                            <img src="{{ $path }}" alt="Foto Masuk" class="imaged w64">
                                        @else
                                            <ion-icon name="camera-outline"></ion-icon>
                                        @endif
                                    </div>
                                    <div class="presencedetail">
                                        <h4 class="presencetitle">Presensi Masuk</h4>
                                        <span class="attendify-presence-time">{{ $presensitoday != null ? $presensitoday->jam_in : 'Belum Absen' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card attendify-presence-card attendify-presence-card--out">
                            <div class="card-body">
                                <div class="presencecontent" style="min-height: 48px;">
                                    <div class="iconpresence">
                                        @if ($presensitoday != null && $presensitoday->jam_out != null)
                                            @php
                                                $path = Storage::url('public/uploads/absensi/' . $presensitoday->foto_out);
                                            @endphp
                                            <img src="{{ $path }}" alt="Foto Keluar" class="imaged w64">
                                        @else
                                            <ion-icon name="camera-outline"></ion-icon>
                                        @endif
                                    </div>
                                    <div class="presencedetail">
                                        <h4 class="presencetitle">Presensi Pulang</h4>
                                        <span class="attendify-presence-time">{{ $presensitoday != null && $presensitoday->jam_out != null ? $presensitoday->jam_out : 'Belum Absen' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($presensitoday == null)
                    <div class="attendify-empty-state">
                        <svg class="attendify-empty-state__svg" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="2" y="2" width="44" height="44" rx="14" fill="#2E3AC7" fill-opacity="0.10" />
                            <path d="M24 10L26.4 18.6L35 21L26.4 23.4L24 32L21.6 23.4L13 21L21.6 18.6L24 10Z" fill="#2E3AC7" />
                            <path d="M35 10L36 13.5L39.5 14.5L36 15.5L35 19L34 15.5L30.5 14.5L34 13.5L35 10Z" fill="#5AD8E6" />
                            <path d="M14 29L14.8 31.7L17.5 32.5L14.8 33.3L14 36L13.2 33.3L10.5 32.5L13.2 31.7L14 29Z" fill="#F7A8D8" />
                        </svg>
                        <div class="attendify-empty-state__text">
                            <p class="attendify-empty-state__title">Belum ada presensi hari ini</p>
                            <p class="attendify-empty-state__desc">Ketuk tombol kamera untuk mencatat kehadiran masuk dengan GPS dan selfie.</p>
                        </div>
                    </div>
                @endif
            </div>

            <!--Rekap Presensi Bulan Ini Start-->
            <div class="rekappresensi">
                <h3>Rekap Presensi Bulan {{ $namabulan[$bulanini] }} Tahun {{ $tahunini }}</h3>
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="card attendify-stat-card">
                            <div class="card-body d-flex align-items-center justify-content-start gap-2">
                                <div class="presensiicon attendify-stat-badge attendify-stat-badge--hadir">
                                    <ion-icon name="accessibility-outline"></ion-icon>
                                </div>
                                <div class="presensidetail text-start ml-1">
                                    <h4>Hadir</h4>
                                    <span>{{ $rekappresensi->jmlhadir }} hari</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card attendify-stat-card">
                            <div class="card-body d-flex align-items-center justify-content-start gap-2">
                                <div class="presensiicon attendify-stat-badge attendify-stat-badge--izin">
                                    <ion-icon name="newspaper-outline"></ion-icon>
                                </div>
                                <div class="presensidetail ml-1">
                                    <h4>Izin</h4>
                                    <span>{{ $rekapizin->jmlizin }} hari</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Spacer only visible on mobile -->
                    <div class="d-block d-md-none w-100 mt-2"></div>

                    <div class="col-6 col-md-3">
                        <div class="card attendify-stat-card">
                            <div class="card-body d-flex align-items-center justify-content-start gap-2">
                                <div class="presensiicon attendify-stat-badge attendify-stat-badge--pink">
                                    <ion-icon name="medkit-outline"></ion-icon>
                                </div>
                                <div class="presensidetail ml-1">
                                    <h4>Sakit</h4>
                                    <span>{{ $rekapizin->jmlsakit }} hari</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card attendify-stat-card">
                            <div class="card-body d-flex align-items-center justify-content-start gap-2">
                                <div class="presensiicon attendify-stat-badge attendify-stat-badge--terlambat">
                                    <ion-icon name="alarm-outline"></ion-icon>
                                </div>
                                <div class="presensidetail ml-1">
                                    <h4>Terlambat</h4>
                                    <span>{{ $rekappresensi->jmlterlambat }} hari</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Rekap Presensi Bulan Ini End-->

            <div class="presencetab mt-3">
                <div class="tab-pane fade show active" id="pilled" role="tabpanel">
                    <ul class="nav nav-tabs style1" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#home" role="tab">
                                30 Hari Terakhir
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#profile" role="tab">
                                Leaderboard
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="tab-content mt-2" style="margin-bottom:100px;">
                    <div class="tab-pane fade show active" id="home" role="tabpanel">
                        <ul class="listview image-listview attendify-listview">
                            @foreach ($historibulanini as $d)
                            @php
                                $path = Storage::url('public/uploads/absensi/' . $d->foto_in);
                            @endphp
                            <li>
                                <div class="item">
                                    <div class="icon-box bg-primary">
                                        <ion-icon name="finger-print-outline"></ion-icon>
                                    </div>
                                    <div class="in">
                                        {{-- <div>{{ date("d-m-Y",strtotime($d->tgl_presensi))}}</div> --}}
                                        <div class="font-weight-bold">{{ \Carbon\Carbon::parse($d->tgl_presensi)->translatedFormat('l, d F Y') }}</div>
                                        <div class="mt-1">
                                            <span class="badge badge-primary attendify-badge attendify-badge--hadir">{{ $d->jam_in }}</span>
                                            <span class="badge badge-danger attendify-badge attendify-badge--alpha">{{ $presensitoday != null && $d->jam_out != null ? $d->jam_out : 'Belum Absen' }}</span>
                                            @php
                                                $statusColors = [
                                                    'hadir' => 'primary',
                                                    'terlambat' => 'warning',
                                                    'izin' => 'info',
                                                    'sakit' => 'secondary',
                                                    'alpha' => 'danger'
                                                ];
                                            @endphp

                                            <span class="badge badge-{{ $statusColors[$d->status] ?? 'dark' }} attendify-badge attendify-badge--{{ $d->status }}">
                                                {{ ucfirst($d->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach

                        </ul>
                    </div>
                    <div class="tab-pane fade" id="profile" role="tabpanel">
                        <ul class="listview image-listview attendify-listview">
                            @foreach ( $leaderboard as $d)
                            <li>
                                <div class="item">
                                    <img src="assets/img/sample/avatar/avatar1.jpg" alt="image" class="image">
                                    <div class="in">
                                        <div class="font-weight-bold">{{$d->nama_lengkap}}</div>
                                        {{-- <span class="text-muted">Designer</span> --}}
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- * App Capsule -->

@endsection
