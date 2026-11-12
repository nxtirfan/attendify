<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $hariini = date('Y-m-d');
        $bulanini = date('m') * 1; // Ambil bulan dan tahun hari ini
        $tahunini = date('Y'); // Ambil tahun hari ini
        $siswa_id = Auth::user()->siswa->siswa_id;
        $nis = Auth::user()->siswa->nis;

        $presensitoday = DB::table('presensi')->where('siswa_id', $siswa_id)->where('tgl_presensi', $hariini)->first();
        $historibulanini = DB::table('presensi')
            ->where('siswa_id', $siswa_id)
            ->whereMonth('tgl_presensi', $bulanini)
            ->whereYear('tgl_presensi', $tahunini)
            ->orderByDesc('tgl_presensi')
            ->get(); // Ambil data presensi bulan ini

        // dd($historibulanini);

        $rekappresensi = DB::table('presensi')
            ->selectRaw('COUNT(siswa_id) as jmlhadir, IFNULL(SUM(IF(jam_in > \'07:00\',1,0)), 0) as jmlterlambat')
            ->where('siswa_id', $siswa_id)
            ->whereMonth('tgl_presensi', $bulanini)
            ->whereYear('tgl_presensi', $tahunini)
            ->first();

        $leaderboard = DB::table('presensi')
            ->join('siswa', 'presensi.siswa_id', '=', 'siswa.siswa_id')
            ->whereMonth('tgl_presensi', $bulanini)
            ->whereYear('tgl_presensi', $tahunini)
            ->get();
        
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

        $rekapizin = DB::table('izin')
            ->selectRaw('IFNULL(SUM(IF(status="izin",1,0)), 0) as jmlizin,IFNULL(SUM(IF(status="sakit",1,0)),0) as jmlsakit')
            ->where('siswa_id', $siswa_id)
            ->whereMonth('tgl_izin', $bulanini)
            ->whereYear('tgl_izin', $tahunini)
            ->where('status_approved', 'disetujui')
            ->first();

        return view('dashboard.dashboardhome', compact('presensitoday', 
        'historibulanini', 'namabulan', 'bulanini', 'tahunini', 'rekappresensi', 
        'leaderboard', 'rekapizin'));
    }

    public function admin()
    {
        $hariini = date('Y-m-d');
        $rekappresensi = DB::table('presensi')
            ->selectRaw('COUNT(siswa_id) as jmlhadir, IFNULL(SUM(IF(jam_in > \'07:00\',1,0)), 0) as jmlterlambat')
            ->where('tgl_presensi', $hariini)
            ->first();

        $rekapizin = DB::table('izin')
            ->selectRaw('IFNULL(SUM(IF(status="izin",1,0)), 0) as jmlizin,IFNULL(SUM(IF(status="sakit",1,0)),0) as jmlsakit')
            ->where('status_approved', 'disetujui')
            ->first();

        // Logika untuk dashboard admin
        return view('dashboard.dashboardadmin',compact('rekappresensi', 'rekapizin'));
    }

    public function guru()
    {
        $hariini = date('Y-m-d');
        $rekappresensi = DB::table('presensi')
            ->selectRaw('COUNT(siswa_id) as jmlhadir, IFNULL(SUM(IF(jam_in > \'07:00\',1,0)), 0) as jmlterlambat')
            ->where('tgl_presensi', $hariini)
            ->first();

        $rekapizin = DB::table('izin')
            ->selectRaw('IFNULL(SUM(IF(status="izin",1,0)), 0) as jmlizin,IFNULL(SUM(IF(status="sakit",1,0)),0) as jmlsakit')
            ->where('status_approved', 'disetujui')
            ->first();

        // Logika untuk dashboard guru
        return view('dashboard.dashboardguru',compact('rekappresensi', 'rekapizin'));
    }
}
