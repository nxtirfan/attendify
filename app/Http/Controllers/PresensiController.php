<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Ramsey\Uuid\Codec\OrderedTimeCodec;
use App\Models\Izin;

class PresensiController extends Controller
{
    public function create()
    {
        $hariini = date('Y-m-d');
        $siswa_id = Auth::user()->siswa->siswa_id; // Ambil ID siswa dari user yang sedang login

        // Cek untuk menampilkan tombol presensi masuk atau keluar di view
        $cek = DB::table('presensi')->where('tgl_presensi', $hariini)->where('siswa_id', $siswa_id)->count();
        return view('presensi.create', compact('cek', 'hariini') + [
            'sekolah_lat' => config('presensi.sekolah_lat'),
            'sekolah_lng' => config('presensi.sekolah_lng'),
            'radius_m' => config('presensi.radius_m'),
        ]);
    }

    public function store(Request $request)
    {
        $siswa_id = Auth::user()->siswa->siswa_id; // Ambil ID siswa dari user yang sedang login
        $nis = Auth::user()->siswa->nis; // Ambil NIS dari user yang sedang login
        $tgl_presensi = date('Y-m-d'); // Ambil tanggal saat ini
        $jam = date('H:i:s'); // Ambil jam saat ini

        $status = $jam > config('presensi.jam_masuk', '07:00:00') ? 'terlambat' : 'hadir'; // Efisienkan status presensi masuk


        $latitudesekolah = config('presensi.sekolah_lat'); // Latitude dan Longitude sekolah
        $longitudeskolah = config('presensi.sekolah_lng');

        $lokasi = $request->lokasi; // Ambil lokasi
        $lokasiuser = explode(',', $lokasi); // Pisahkan lokasi menjadi array
        $latitudeuser = $lokasiuser[0]; // Ambil latitude
        $longitudeuser = $lokasiuser[1]; // Ambil longitude

        $jarak = $this->distance($latitudesekolah, $longitudeskolah, $latitudeuser, $longitudeuser); // Hitung jarak
        $radius = round($jarak['meters']); // Ambil jarak dalam meter
        $radiusMax = config('presensi.radius_m', 20); // Radius presensi dari konfigurasi

        // Cek apakah siswa sudah melakukan presensi pada hari ini
        $presensiHariIni = DB::table('presensi')->where('tgl_presensi', $tgl_presensi)->where('siswa_id', $siswa_id)->first();
        if ($presensiHariIni && !empty($presensiHariIni->jam_out)) {
            // Sudah presensi masuk dan pulang, tolak presensi berulang
            echo "error|Mohon maaf, $nis, Anda sudah melakukan presensi masuk dan pulang hari ini|out";
            return;
        }
        if ($presensiHariIni) {
            // Keterangan untuk presensi keluar
            $ket = "out"; // Status keluar
        } else {
            // Keterangan untuk presensi masuk
            $ket = "in"; // Status masuk
        }

        $image = $request->image; // Ambil file gambar
        $folderPath = "public/uploads/absensi/";
        $formatName = $nis . "-" . $tgl_presensi . "-" . $ket; // Format nama file berdasarkan NIS dan tanggal presensi
        $image_parts = explode(";base64,", $image);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $formatName . ".png"; // Format nama file
        $file = $folderPath . $fileName;


        // Validasi jarak presensi
        if ($radius > $radiusMax) {
            echo "error|Mohon maaf, $nis, jarak presensi Anda terlalu jauh dari sekolah. Jarak Anda saat ini $radius meter|radius"; // Jarak terlalu jauh
        } else {
            // Jika jarak presensi valid, lanjutkan proses presensi
            if ($ket == "out") {

                // Data untuk presensi keluar
                $data_pulang = [
                    'jam_out' => $jam, // Jam presensi keluar
                    'foto_out' => $fileName, // Simpan nama file gambar keluar
                    'lokasi_out' => $lokasi, // Lokasi presensi keluar
                ];

                // Update data presensi keluar jika sudah ada
                $update = DB::table('presensi')
                    ->where('tgl_presensi', $tgl_presensi)
                    ->where('siswa_id', $siswa_id)
                    ->update($data_pulang);
                if ($update) {
                    echo "success|Terima kasih, $nis, telah presensi pulang|out"; // Berhasil update jam keluar
                    Storage::put($file, $image_base64);
                } else {
                    echo "error|Mohon maaf, $nis, gagal presensi pulang. Silakan coba lagi|out"; // Gagal update jam keluar
                }
            } else {

                // Data untuk presensi masuk
                $data = [
                    'siswa_id' => $siswa_id, // ID siswa
                    'tgl_presensi' => $tgl_presensi, // Tanggal presensi
                    'jam_in' => $jam, // Jam presensi masuk
                    'foto_in' => $fileName, // Simpan nama file gambar
                    'lokasi_in' => $lokasi, // Lokasi presensi masuk
                    'status' => $status, // Status presensi (Hadir / Terlambat)
                ];

                // Simpan data presensi masuk
                try {
                    $simpan = DB::table('presensi')->insert($data);
                } catch (\Illuminate\Database\QueryException $e) {
                    // Duplikasi presensi (misal dua request bersamaan) ditolak constraint unique
                    $simpan = false;
                }
                if ($simpan) {
                    echo "success|Terima kasih, $nis, telah presensi masuk|in"; // Berhasil menyimpan data presensi
                    Storage::put($file, $image_base64);
                } else {
                    echo "error|Mohon maaf, $nis, gagal presensi masuk. Silakan coba lagi|in"; // Gagal menyimpan data presensi
                }
            }
        }
    }

    public function generateAlpha()
    {
        $tgl_presensi = date('Y-m-d');
        $allSiswa = DB::table('siswa')->pluck('siswa_id');
        $sudahPresensi = DB::table('presensi')->where('tgl_presensi', $tgl_presensi)->pluck('siswa_id');
        $siswaAlpha = $allSiswa->diff($sudahPresensi);

        foreach ($siswaAlpha as $siswa_id) {
            DB::table('presensi')->insert([
                'siswa_id' => $siswa_id,
                'tgl_presensi' => $tgl_presensi,
                'status' => 'alpha',
            ]);
        }

        // Tidak mengembalikan response
    }


    //Menghitung Jarak
    function distance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;
        return compact('meters');
    }

    // Edit Profile
    public function editprofile()
    {
        $nis = Auth::user()->siswa->nis; // Ambil NIS dari user yang sedang login
        $siswa = DB::table('siswa')->where('nis', $nis)->first(); // Ambil data siswa berdasarkan NIS

        return view('presensi.editprofile', compact('siswa'));
    }

    // Update Profile
    public function updateprofile(Request $request)
    {
        $user_id = Auth::user()->user_id; // Ambil ID user yang sedang login
        $nis = Auth::user()->siswa->nis; // Ambil ID siswa dari user yang sedang login
        $nama_lengkap = $request->nama_lengkap; // Ambil nama lengkap dari input
        $no_hp = $request->no_hp; // Ambil nomor HP dari input
        $password = Hash::make($request->password); // Hash password yang diinput
        $siswa = DB::table('siswa')->where('nis', $nis)->first(); // Ambil data siswa berdasarkan NIS

        // Cek apakah ada file foto yang diupload
        if ($request->hasFile('foto')) {
            $foto = $nis . "." . $request->file('foto')->getClientOriginalExtension(); // Ambil file foto
        } else {
            $foto = $siswa->foto; // Jika tidak ada foto yang diupload, gunakan foto lama
        }


        // Update identitas siswa
        $data = [
            'nama_lengkap' => $nama_lengkap,
            'no_hp' => $no_hp,
            'foto' => $foto
        ];
        $updateIdentitas = DB::table('siswa')->where('nis', $nis)->update($data);


        // Cek apakah password diinput atau tidak
        if (!empty($request->password)) {
            $updatePassword = DB::table('user')
                ->where('user_id', $user_id)
                ->update(['password' => $password]);
        } else {
            $updatePassword = false; // Jika password tidak diubah, set false
            // Jika password tidak diubah, tidak perlu update password
        }


        // Cek apakah update identitas dan password berhasil
        $update = $updateIdentitas || $updatePassword; // Cek apakah kedua update berhasil


        if ($update) {
            if ($request->hasFile('foto')) {
                $folderPath = "public/uploads/siswa/"; // Folder penyimpanan foto
                $request->file('foto')->storeAs($folderPath, $foto); // Simpan foto ke folder
            }
            return Redirect::back()->with('success', 'Profile berhasil diperbarui');
        } else {
            return Redirect::back()->with('error', 'Gagal memperbarui profile. Silakan coba lagi');
        }
    }

    // Histori Presensi
    public function histori()
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
        return view('presensi.histori', compact('namabulan'));
    }

    public function gethistori(Request $request)
    {
        $bulan = (int) $request->bulan;
        $tahun = (int) $request->tahun;
        $nis = Auth::user()->siswa->nis; // Ambil NIS dari user yang sedang login
        $siswa_id = Auth::user()->siswa->siswa_id; // Ambil siswa ID dari user yang sedang login

        $histori = collect();
        if ($bulan >= 1 && $bulan <= 12 && $tahun >= 2000 && $tahun <= 2100) {
            $histori = DB::table('presensi')
                ->where('siswa_id', $siswa_id)
                ->whereMonth('tgl_presensi', $bulan)
                ->whereYear('tgl_presensi', $tahun)
                ->orderBy('tgl_presensi', 'desc')
                ->get();
        }

        return view('presensi.gethistori', compact('histori'));
    }

    public function izin()
    {
        $siswa_id = Auth::user()->siswa->siswa_id; // Ambil data siswa dari user yang sedang login
        $dataizin = DB::table('izin')->where('siswa_id', $siswa_id)->get(); // Ambil data izin berdasarkan ID siswa
        return view('presensi.izin', compact('dataizin'));
    }

    public function buatizin()
    {
        return view('presensi.buatizin');
    }

    public function storeizin(Request $request)
    {
        $siswa_id = Auth::user()->siswa->siswa_id; // Ambil NIS siswa dari user yang sedang login

        $tgl_izin = $request->tgl_izin; // Ambil tanggal izin dari input
        $status = $request->status; // Ambil status izin dari input
        $keterangan = $request->keterangan; // Ambil keterangan izin dari input

        $data = [
            'siswa_id' => $siswa_id, // ID siswa
            'tgl_izin' => $tgl_izin, // Tanggal izin
            'status' => $status, // Status izin
            'keterangan' => $keterangan, // Keterangan izin
        ];

        // Simpan data izin ke database
        $simpan = DB::table('izin')->insert($data);

        if ($simpan) {
            return redirect('/presensi/izin')->with('success', 'Izin berhasil dibuat');
        } else {
            return redirect('/presensi/izin')->with('error', 'Gagal membuat izin. Silakan coba lagi');
        }
    }

    public function monitoring()
    {
        return view('presensi.monitoring');
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
        return view('presensi.getpresensi', compact('presensi'));
    }


    public function tampilkanpeta (Request $request) {
        $presensi_id = $request->presensi_id;
        $presensi = DB::table('presensi')->where('presensi_id', $presensi_id)
        ->join('siswa', 'presensi.siswa_id', '=', 'siswa.siswa_id')
        ->first();
        return view('presensi.showmap', compact('presensi'));
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
        return view('presensi.laporan',compact('namabulan', 'siswa'));
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
        return view('presensi.cetaklaporan', compact('presensi', 'siswa', 'bulan', 'tahun', 'namabulan'));
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
        return view('presensi.rekap',compact('namabulan' ));
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

        return view ('presensi.cetakrekap',compact('bulan','tahun','namabulan','rekap'));

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
        $izinsakit = $query->paginate(10);
        $izinsakit->appends($request->all());
        return view('presensi.izinsakit', compact('izinsakit'));
    }

    public function approveizinsakit(Request $request)
    {
        $status_approved = $request->status_approved;
        $id_izinsakit_form = $request->id_izinsakit_form;
        $update = DB::table('izin')->where('izin_id', $id_izinsakit_form)->update(['status_approved' => $status_approved]);

        if ($update) {
            // Sinkronkan hasil persetujuan dengan tabel presensi
            $izin = DB::table('izin')->where('izin_id', $id_izinsakit_form)->first();
            if ($status_approved == 'disetujui' && $izin) {
                $presensiUpdate = DB::table('presensi')
                    ->where('siswa_id', $izin->siswa_id)
                    ->where('tgl_presensi', $izin->tgl_izin)
                    ->update(['status' => $izin->status]);
                if (!$presensiUpdate) {
                    DB::table('presensi')->insert([
                        'siswa_id' => $izin->siswa_id,
                        'tgl_presensi' => $izin->tgl_izin,
                        'status' => $izin->status,
                    ]);
                }
            } elseif ($status_approved == 'ditolak' && $izin) {
                $this->hapusPresensiIzin($izin);
            }
            return Redirect::back()->with('success', 'Data berhasil diupdate');
        } else {
            return Redirect::back()->with('error', 'Data gagal diupdate');
        }
    }

    public function batalkanizinsakit ($izin_id)
    {
        $update = DB::table('izin')->where('izin_id', $izin_id)->update(['status_approved' => "pending"]);

        if($update) {
            // Kembalikan ke pending berarti cabut presensi izin yang sempat tersinkron
            $izin = DB::table('izin')->where('izin_id', $izin_id)->first();
            if ($izin) {
                $this->hapusPresensiIzin($izin);
            }
            return Redirect::back()->with('success', 'Data berhasil diupdate');
        } else {
            return Redirect::back()->with('error', 'Data gagal diupdate');
        }
    }

    private function hapusPresensiIzin($izin)
    {
        // Hapus presensi hasil sinkron izin hanya jika siswa memang tidak pernah absen (tanpa jam masuk)
        DB::table('presensi')
            ->where('siswa_id', $izin->siswa_id)
            ->where('tgl_presensi', $izin->tgl_izin)
            ->whereIn('status', ['izin', 'sakit'])
            ->whereNull('jam_in')
            ->delete();
    }

    public function cekizin(Request $request)
    {
        $siswa_id = Auth::user()->siswa->siswa_id; // Ambil NIS siswa dari user yang sedang login
        $tgl_izin = $request->tgl_izin; // Ambil tanggal izin dari input
        $cek = DB::table('izin')->where('siswa_id', $siswa_id)->where('tgl_izin', $tgl_izin)->count();
        return $cek;
    }
}
