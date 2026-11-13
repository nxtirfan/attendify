<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Attendify — Laporan Presensi Siswa</title>

    <!-- Normalize or reset CSS with your favorite library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css">

    <!-- Load paper.css for happy printing -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.4.1/paper.css">

    <!-- Set page size here: A5, A4 or A3 -->
    <!-- Set also "landscape" if you need -->
    <style>
        @page {
            size: A4
        }

        #title {
            font-family: 'Inter', Arial, Helvetica, sans-serif;
            font-size: 18px;
            font-weight: bold;
            color: #0D1442;
        }

        .tabeldatasiswa {
            margin-top: 40px;
        }

        .tabeldatasiswa tr td {
            padding: 5px;
        }

        .tabelpresensi {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .tabelpresensi tr th {
            border: 1px solid #1E2A78;
            padding: 8px;
            background-color: #EEF0FE;
            color: #0D1442;
        }

        .tabelpresensi tr td {
            border: 1px solid #000000;
            padding: 5px;
            font-size: 12px;
        }

        .foto {
            width: 40px;
            height: 30px;
        }
    </style>
</head>

<!-- Set "A5", "A4" or "A3" for class name -->
<!-- Set also "landscape" if you need -->

<body class="A4">
    @php
        function selisih($jam_masuk, $jam_keluar)
        {
            list($h, $m, $s) = explode(":", $jam_masuk);
            $dtAwal = mktime($h, $m, $s, "1", "1", "1");
            list($h2, $m2, $s2) = explode(":", $jam_keluar);
            $dtAkhir = mktime($h2, $m2, $s2, "1", "1", "1");
            $dtSelisih = $dtAkhir - $dtAwal;
            $totalmenit = $dtSelisih / 60;
            $jam = floor($totalmenit / 60);
            $menit = $totalmenit % 60;
            if ($jam > 0) {
                return $jam . ' jam ' . $menit . ' menit';
            } else {
                return $menit . ' menit';
            }
        }
    @endphp

    <!-- Each sheet element should have the class "sheet" -->
    <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
    <section class="sheet padding-10mm">

        <table style="width: 100%">
            <tr>
                <td style="width: 30px">
                    <img src="{{ asset('assets/img/ssd24.jpg') }}" width="70" height="70" alt="">
                </td>
                <td>
                    <span id="title">
                        LAPORAN PRESENSI SISWA<br>
                        PERIODE {{ strtoupper($namabulan[$bulan]) }} {{ $tahun }}<br>
                        STATISTIKA DAN SAINS DATA<br>
                    </span>
                    <span><i>Jl. Sekaran, Kec. Gn. Pati, Kota Semarang, Jawa Tengah 50229</i></span>
                </td>
            </tr>
        </table>
        <table class="tabeldatasiswa">
            <tr>
                <td rowspan="6">
                    @php
                        $path = !empty($siswa->foto) ? Storage::url('public/uploads/siswa/' . $siswa->foto) : null;
                    @endphp
                    @if (empty($siswa->foto))
                        <img src="{{ asset('assets/img/sample/avatar/avatar1.jpg') }}" class="avatar" alt="No Photo"
                            width="120px" height="150px">
                    @else
                        <img src="{{ $path }}" class="avatar" alt="Foto Profil" width="120px" height="150px">
                    @endif
                </td>
            </tr>
            <tr>
                <td>NIS</td>
                <td>:</td>
                <td>{{ $siswa->nis }}</td>
            </tr>
            <tr>
                <td>Nama Siswa</td>
                <td>:</td>
                <td>{{ $siswa->nama_lengkap }}</td>
            </tr>
            <tr>
                <td>Kelas</td>
                <td>:</td>
                <td>{{ $siswa->kelas }}</td>
            </tr>
            <tr>
                <td>Angkatan</td>
                <td>:</td>
                <td>{{ $siswa->nama_angkatan }}</td>
            </tr>
            <tr>
                <td>No. HP</td>
                <td>:</td>
                <td>{{ $siswa->no_hp }} {{ $tahun }}</td>
            </tr>
        </table>
        <table class="tabelpresensi">
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Foto</th>
                <th>Jam Pulang</th>
                <th>Foto</th>
                <th>Keterangan</th>
                <th>Waktu Belajar</th>
            </tr>
            @foreach ($presensi as $d)
                @php
                    $path_in = Storage::url('public/uploads/absensi/' . $d->foto_in);
                    $path_out = Storage::url('public/uploads/absensi/' . $d->foto_out);
                    $jamterlambat = selisih('07:00:00', $d->jam_in);

                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ date('d-m-Y', strtotime($d->tgl_presensi)) }}</td>
                    <td>{{ $d->jam_in }}</td>
                    <td><img src="{{ $path_in }}" class="foto" alt="Foto Masuk"></td>
                    <td>{!! $d->jam_out != null ? $d->jam_out : '<span class="badge bg-danger">Belum Absen</span>' !!}</td>
                    <td> @if ($d->jam_out != null)
                        <img src="{{ $path_out }}" class="foto" alt="Foto Keluar">
                    @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-hourglass-high">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M6.5 7h11" />
                                <path d="M6 20v-2a6 6 0 1 1 12 0v2a1 1 0 0 1 -1 1h-10a1 1 0 0 1 -1 -1z" />
                                <path d="M6 4v2a6 6 0 1 0 12 0v-2a1 1 0 0 0 -1 -1h-10a1 1 0 0 0 -1 1z" />
                            </svg>
                        @endif
                    </td>
                    <td>
                        @if ($d->status == 'hadir')
                            <span class="badge bg-primary" style="color: #2E3AC7; font-weight: bold;">Tepat Waktu</span>
                        @elseif ($d->jam_in >= '07:00' && $d->status == 'terlambat')
                            <span class="badge bg-danger">Terlambat {{ $jamterlambat}}</span>
                        @elseif ($d->status == 'izin')
                            <span class="badge bg-info text-dark">Izin</span>
                        @elseif ($d->status == 'sakit')
                            <span class="badge bg-primary">Sakit</span>
                        @elseif ($d->status == 'alpha')
                            <span class="badge bg-warning text-dark">Alpha</span>
                        @else
                            <span class="badge bg-secondary">Tidak Diketahui</span>
                        @endif
                    </td>
                    <td>
                        @if ($d->jam_out != null)
                            @php
                                $jmljam = selisih($d->jam_in, $d->jam_out);
                            @endphp
                        @else
                            @php
                                $jmljam = 0;
                            @endphp
                        @endif
                        {{ $jmljam }}
                    </td>
                </tr>
            @endforeach
        </table>

        <table width="100%" style="margin-top: 100px">
            <tr>
                <td colspan="2" style="text-align: right">Semarang, {{ date('d-m-Y') }}</td>
            </tr>
            
            <tr>
                <td style="text-align: center; vertical-align: bottom;" height="100px">
                    <u>Dimas Tejo Gumilang, S.Pd.</u><br>
                    <i><b>Guru</b></i><br>
                </td>
                <td style="text-align: center; vertical-align: bottom;">
                    <u>Riski Cahya Alfiansyah</u><br>
                    <i><b>Admin</b></i><br>
                </td>
            </tr>
        </table>
    </section>

</body>

</html>