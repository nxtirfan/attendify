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

@foreach ($presensi as $d )
@php
    $foto_in = Storage::url('public/uploads/absensi/' . $d->foto_in);
    $foto_out = Storage::url('public/uploads/absensi/' . $d->foto_out);
@endphp
    <tr>
        <td>{{ $loop->iteration}}</td>
        <td>{{ $d->nis }}</td>
        <td>{{ $d->nama_lengkap }}</td>
        <td>{{ $d->nama_angkatan }}</td>
        <td>{{ $d->jam_in }}</td>
        <td><img src="{{ $foto_in }}" class="avatar" alt="Foto Masuk"></td>
        <td>{!! $d->jam_out != null ? $d->jam_out : '<span class="badge bg-danger attendify-badge-alpha">Belum Absen</span>' !!}</td>
        <td>
            @if ($d->jam_out != null)
                <img src="{{ $foto_out }}" class="avatar" alt="Foto Keluar">
            @else
                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-hourglass-high"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6.5 7h11" /><path d="M6 20v-2a6 6 0 1 1 12 0v2a1 1 0 0 1 -1 1h-10a1 1 0 0 1 -1 -1z" /><path d="M6 4v2a6 6 0 1 0 12 0v-2a1 1 0 0 0 -1 -1h-10a1 1 0 0 0 -1 1z" /></svg>
            @endif
        </td>
        <td>
            @if ($d->status == 'hadir')
                <span class="badge bg-primary attendify-badge-hadir">Tepat Waktu</span>
            @elseif ($d->jam_in >= '07:00' && $d->status == 'terlambat')
                @php
                    $jamterlambat = selisih('07:00:00', $d->jam_in);
                @endphp
                <span class="badge bg-warning attendify-badge-terlambat">Terlambat {{ $jamterlambat}}</span>
            @elseif ($d->status == 'izin')
                <span class="badge bg-info attendify-badge-izin">Izin</span>
            @elseif ($d->status == 'sakit')
                <span class="badge bg-primary attendify-badge-sakit">Sakit</span>
            @elseif ($d->status == 'alpha')
                <span class="badge bg-danger attendify-badge-alpha">Alpha</span>
            @else
                <span class="badge bg-secondary">Tidak Diketahui</span>
            @endif

            {{-- @switch($d->status)
                @case('hadir')
                    <span class="badge bg-success">Hadir</span>
                    @break
                @case('terlambat')
                    <span class="badge bg-warning text-dark">Terlambat</span>
                    @break
                @case('izin')
                    <span class="badge bg-info attendify-badge-izin">Izin</span>
                    @break
                @case('sakit')
                    <span class="badge bg-primary attendify-badge-sakit">Sakit</span>
                    @break
                @case('alpha')
                    <span class="badge bg-danger">Alpha</span>
                    @break
                @default
                    <span class="badge bg-secondary">Tidak Diketahui</span>
            @endswitch --}}
        </td>

        <td>
            <a href="#" class="btn btn-primary tampilkanpeta" id="{{ $d->presensi_id }}">
                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-map-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 18.5l-3 -1.5l-6 3v-13l6 -3l6 3l6 -3v7.5" /><path d="M9 4v13" /><path d="M15 7v5.5" /><path d="M21.121 20.121a3 3 0 1 0 -4.242 0c.418 .419 1.125 1.045 2.121 1.879c1.051 -.89 1.759 -1.516 2.121 -1.879z" /><path d="M19 18v.01" /></svg>
            </a>
        </td>
    </tr>
@endforeach

<script>
    $(function () {
        $(".tampilkanpeta").click(function (e) {
            var presensi_id = $(this).attr('id');
            $.ajax({
                type: 'POST',
                url: '/tampilkanpeta',
                data: {
                    _token: "{{  csrf_token() }}",
                    presensi_id: presensi_id,
                },
                cache: false,

                success: function (respond) {
                    $("#loadmap").html(respond);
                }
            });
            $("#modal-tampilkanpeta").modal("show");
        });
    });
</script>