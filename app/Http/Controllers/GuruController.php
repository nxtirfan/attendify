<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Ramsey\Uuid\Codec\OrderedTimeCodec;
use App\Models\Izin;

class GuruController extends Controller
{
    public function monitoring()
    {
        return view('guru.monitoring');
    }

    public function getpresensi(Request $request)
    {
        $tanggal = $request->tanggal;
        $presensi = DB::table('presensi')
            ->select('presensi.*', 'siswa.nama_lengkap', 'siswa.nis', 'angkatan.nama_angkatan')
            ->join('siswa', 'presensi.siswa_id', '=', 'siswa.siswa_id')
            ->join('angkatan', 'siswa.angkatan_id', '=', 'angkatan.angkatan_id')
            ->where('tgl_presensi', $tanggal)
            ->get();
        return view('guru.getpresensi', compact('presensi'));
    }


    public function tampilkanpeta (Request $request) {
        $presensi_id = $request->presensi_id;
        $presensi = DB::table('presensi')->where('presensi_id', $presensi_id)
        ->join('siswa', 'presensi.siswa_id', '=', 'siswa.siswa_id')
        ->first();
        return view('guru.showmap', compact('presensi'));
    }

    public function laporan()
    {
        $namabulan = [
            '',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];
        $siswa = DB::table('siswa')->orderBy('nama_lengkap')->get();
        return view('guru.laporan',compact('namabulan', 'siswa'));
    }

    public function cetaklaporan(Request $request)
    {
        $nis = $request->nis;
        $bulan = (int) $request->bulan;
        $tahun = (int) $request->tahun;
        if ($bulan < 1 || $bulan > 12 || $tahun < 2000 || $tahun > 2100) {
            return Redirect::back()->with('error', 'Bulan atau tahun tidak valid');
        }
        $namabulan = [
            '',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];
        $siswa = DB::table('siswa')->where('nis', $nis)
        ->join('angkatan', 'siswa.angkatan_id', '=', 'angkatan.angkatan_id')
        ->first();
        $presensi = DB::table('presensi')
            ->join('siswa', 'presensi.siswa_id', '=', 'siswa.siswa_id')
            ->where('nis', $nis)
            ->whereMonth('tgl_presensi', $bulan)
            ->whereYear('tgl_presensi', $tahun)
            ->orderBy('tgl_presensi', 'asc')
            ->get();

        if (isset($_POST['exportexcel'])) {
            $time = date("d-M-Y H:i:s");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=Laporan Presensi " . $time . ".xls");
        }
        return view('guru.cetaklaporan', compact('presensi', 'siswa', 'bulan', 'tahun', 'namabulan'));
    }

    public function rekap()
    {
        $namabulan = [
            '',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];
        return view('guru.rekap',compact('namabulan' ));
    }

    public function cetakrekap (Request $request) {
        $bulan = (int) $request->bulan;
        $tahun = (int) $request->tahun;
        if ($bulan < 1 || $bulan > 12 || $tahun < 2000 || $tahun > 2100) {
            return Redirect::back()->with('error', 'Bulan atau tahun tidak valid');
        }
        $namabulan = [
            '',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];
        $rekap = DB::table('presensi')
        ->selectRaw('
                siswa.nis,
                nama_lengkap,
                MAX(IF(DAY(tgl_presensi) = 1, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_1,
                MAX(IF(DAY(tgl_presensi) = 2, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_2,
                MAX(IF(DAY(tgl_presensi) = 3, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_3,
                MAX(IF(DAY(tgl_presensi) = 4, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_4,
                MAX(IF(DAY(tgl_presensi) = 5, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_5,
                MAX(IF(DAY(tgl_presensi) = 6, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_6,
                MAX(IF(DAY(tgl_presensi) = 7, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_7,
                MAX(IF(DAY(tgl_presensi) = 8, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_8,
                MAX(IF(DAY(tgl_presensi) = 9, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_9,
                MAX(IF(DAY(tgl_presensi) = 10, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_10,
                MAX(IF(DAY(tgl_presensi) = 11, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_11,
                MAX(IF(DAY(tgl_presensi) = 12, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_12,
                MAX(IF(DAY(tgl_presensi) = 13, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_13,
                MAX(IF(DAY(tgl_presensi) = 14, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_14,
                MAX(IF(DAY(tgl_presensi) = 15, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_15,
                MAX(IF(DAY(tgl_presensi) = 16, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_16,
                MAX(IF(DAY(tgl_presensi) = 17, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_17,
                MAX(IF(DAY(tgl_presensi) = 18, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_18,
                MAX(IF(DAY(tgl_presensi) = 19, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_19,
                MAX(IF(DAY(tgl_presensi) = 20, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_20,
                MAX(IF(DAY(tgl_presensi) = 21, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_21,
                MAX(IF(DAY(tgl_presensi) = 22, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_22,
                MAX(IF(DAY(tgl_presensi) = 23, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_23,
                MAX(IF(DAY(tgl_presensi) = 24, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_24,
                MAX(IF(DAY(tgl_presensi) = 25, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_25,
                MAX(IF(DAY(tgl_presensi) = 26, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_26,
                MAX(IF(DAY(tgl_presensi) = 27, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_27,
                MAX(IF(DAY(tgl_presensi) = 28, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_28,
                MAX(IF(DAY(tgl_presensi) = 29, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_29,
                MAX(IF(DAY(tgl_presensi) = 30, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_30,
                MAX(IF(DAY(tgl_presensi) = 31, CONCAT(jam_in, "-", IFNULL(jam_out, "00:00:00")),"")) as tgl_31')
        ->join('siswa', 'presensi.siswa_id', '=', 'siswa.siswa_id')
        ->whereMonth('tgl_presensi', $bulan)
        ->whereYear('tgl_presensi', $tahun)
        ->groupByRaw('siswa.nis,nama_lengkap')
        ->get();

        if (isset($_POST['exportexcel'])) {
            $time = date("d-M-Y H:i:s");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=Rekap Presensi " . $time . ".xls");
        }

        return view ('guru.cetakrekap',compact('bulan','tahun','namabulan','rekap'));

    }

    public function izinsakit (Request $request)
    {
        // $izinsakit = DB::table('izin')
        // ->join('siswa', 'izin.siswa_id', '=', 'siswa.siswa_id')
        // ->orderBy('tgl_izin', 'desc')
        // ->get();

        $query = Izin::query();
        $query->select('izin_id', 'tgl_izin', 'izin.siswa_id', 'nama_lengkap', 'kelas', 'status', 'status_approved', 'keterangan');
        $query->join('siswa', 'izin.siswa_id', '=', 'siswa.siswa_id');

        if (!empty($request->dari) && !empty($request->sampai)) {
            $query->whereBetween('tgl_izin', [$request->dari, $request->sampai]);
        }
        if (!empty($request->nis)) {
            $query->where('nis', $request->nis);
        }
        if (!empty($request->nama_lengkap)) {
            $query->where('nama_lengkap', 'like', '%' . $request->nama_lengkap . '%');
        }
        if (!empty($request->status_approved)) {
            $query->where('status_approved', $request->status_approved);
        }

        $query->orderBy('tgl_izin', 'desc');
        $izinsakit = $query->paginate(2);
        $izinsakit->appends($request->all());
        return view('guru.izinsakit', compact('izinsakit'));
    }

    public function approveizinsakit(Request $request)
    {
        $status_approved = $request->status_approved;
        $id_izinsakit_form = $request->id_izinsakit_form;
        $update = DB::table('izin')->where('izin_id', $id_izinsakit_form)->update(['status_approved' => $status_approved]);

        if($update) {
            return Redirect::back()->with('success', 'Data berhasil diupdate');
        } else {
            return Redirect::back()->with('error', 'Data gagal diupdate');
        }
    }

    public function batalkanizinsakit ($izin_id)
    {
        $update = DB::table('izin')->where('izin_id', $izin_id)->update(['status_approved' => "pending"]);

        if($update) {
            return Redirect::back()->with('success', 'Data berhasil diupdate');
        } else {
            return Redirect::back()->with('error', 'Data gagal diupdate');
        }
    }

}
