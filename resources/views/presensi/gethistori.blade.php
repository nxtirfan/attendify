@if ($histori->isEmpty())
    <div class="attendify-empty-state attendify-empty-state--tab">
        <svg class="attendify-empty-state__svg" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <rect x="2" y="2" width="44" height="44" rx="14" fill="#FFC94D" fill-opacity="0.16" />
            <path d="M24 11L26.1 18.9L34 21L26.1 23.1L24 31L21.9 23.1L14 21L21.9 18.9L24 11Z" fill="#2E3AC7" />
            <circle cx="14" cy="33" r="2.2" fill="#F7A8D8" />
        </svg>
        <div class="attendify-empty-state__text">
            <p class="attendify-empty-state__title">Belum ada data presensi</p>
            <p class="attendify-empty-state__desc">Tidak ditemukan catatan kehadiran pada bulan dan tahun yang dipilih.</p>
        </div>
    </div>
@else
    <ul class="listview image-listview attendify-listview">
        @foreach ($histori as $d)
            <li>
                <div class="item">
                    @php
                        $path = Storage::url('public/uploads/absensi/' . $d->foto_in);
                    @endphp
                    @if (!empty($d->foto_in))
                        <img src="{{ $path }}" alt="Foto Masuk" class="imaged w64 attendify-history-thumb">
                    @else
                        <div class="icon-box bg-primary">
                            <ion-icon name="finger-print-outline"></ion-icon>
                        </div>
                    @endif
                    <div class="in">
                        <div>
                            <div class="attendify-history-date">{{ \Carbon\Carbon::parse($d->tgl_presensi)->translatedFormat('l, d F Y') }}</div>
                            <div class="attendify-history-badges">
                                @if ($d->jam_in != null)
                                    <span class="badge badge-primary attendify-badge attendify-badge--hadir">
                                        Masuk {{ $d->jam_in }}
                                    </span>
                                    <span class="badge {{ $d->jam_out != null ? 'badge-info attendify-badge--izin' : 'badge-warning attendify-badge--terlambat' }} attendify-badge">
                                        {{ $d->jam_out ?? 'Belum Absen' }}
                                    </span>
                                @endif
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
                </div>
            </li>
        @endforeach
    </ul>
@endif
