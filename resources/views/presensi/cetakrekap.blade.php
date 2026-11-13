<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Attendify — Rekap Presensi Siswa</title>

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

        @media screen {
            .tabelpresensi {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
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
            font-size: 10px;
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

<body class="A4 landscape">
    {{-- @php
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
    @endphp --}}

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
                        REKAP PRESENSI SISWA<br>
                        PERIODE {{ strtoupper($namabulan[$bulan]) }} {{ $tahun }}<br>
                        STATISTIKA DAN SAINS DATA<br>
                    </span>
                    <span><i>Jl. Sekaran, Kec. Gn. Pati, Kota Semarang, Jawa Tengah 50229</i></span>
                </td>
            </tr>
        </table>

        <table class="tabelpresensi">
            <tr>
                <th rowspan="2">NIS</th>
                <th rowspan="2">Nama Siswa</th>
                <th colspan="31">Tanggal</th>
                <th rowspan="2">TH</th>
                <th rowspan="2">TT</th>
            </tr>
            <tr>
                <?php
                    for ($i = 1; $i <= 31; $i++) {
                ?>
                    <th> {{ $i }}</th>
                <?php
                    } 
                ?>
            </tr>
            @foreach ($rekap as $d)
                <tr>
                    <td> {{ $d->nis }}</td>
                    <td> {{ $d->nama_lengkap }}</td>
                    <?php
                        $totalhadir = 0;
                        $totalterlambat = 0;
                        for ($i = 1; $i <= 31; $i++) {
                            $tgl = "tgl_".$i;

                            if(empty($d->$tgl)){
                                $hadir = ['', ''];
                                $totalhadir += 0;
                            } else {
                                $hadir = explode("-", $d->$tgl);
                                $totalhadir += 1;
                                if($hadir[0] > "07:00:00"){
                                // if($hadir[0] > "07:00:00" || $hadir[1] < "16:00:00"){
                                    $totalterlambat += 1;
                                }
                            }
                        ?>
                        <td> 
                            <span style="color: {{ $hadir[0] > "07:00:00" ? "red" : "" }}">{{ $hadir[0] }}</span><br>
                            <span style="color: {{ $hadir[1] < "16:00:00" ? "red" : "" }}">{{ $hadir[1] }}</span><br>
                            {{-- {{ $hadir[1] }} --}}
                        </td>
                        <?php
                        } 
                        ?>
                        <td> {{ $totalhadir }}</td>
                        <td> {{ $totalterlambat }}</td>
                </tr>
            @endforeach
        </table>

        <table width="100%" style="margin-top: 100px">
            <tr>
                <td></td>
                <td style="text-align: center">Semarang, {{ date('d-m-Y') }}</td>
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