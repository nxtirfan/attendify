<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Siswa;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class SiswaController extends Controller
{
    public function index(Request $request)
    {

        $query = Siswa::query();
        $query->select('siswa.*', 'nama_angkatan');
        $query->join('angkatan', 'siswa.angkatan_id', '=', 'angkatan.angkatan_id');
        $query->orderBy('nis');
        if (!empty($request->nama_lengkap)) {
            $query->where('nama_lengkap', 'like', '%' . $request->nama_lengkap . '%');
        }

        if (!empty($request->angkatan_id)) {
            $query->where('siswa.angkatan_id', $request->angkatan_id);
        }
        $siswa = $query->paginate(10);


        // $siswa = DB::table('siswa')->orderBy('nama_angkatan')


        $angkatan = DB::table('angkatan')->get();

        return view('siswa.index', compact('siswa', 'angkatan'));
    }

    // public function store(Request $request) {
    //     $nis = $request->nis;
    //     $nama_lengkap = $request->nama_lengkap;
    //     $kelas = $request->kelas;
    //     $no_hp = $request->no_hp;
    //     $angkatan_id = $request->angkatan_id;
    //     $siswa = DB::table('siswa')->where('nis', $nis)->first(); // Ambil data siswa berdasarkan NIS

    //     if ($request->hasFile('foto')) {
    //         $foto = $nis. "." . $request->file('foto')->getClientOriginalExtension(); // Ambil file foto
    //     } else {
    //         $foto = $siswa->foto; // Jika tidak ada foto yang diupload, gunakan foto lama
    //     }

    //     try {

    //         $data = [
    //             'nis'=>$nis,
    //             'nama_lengkap'=>$nama_lengkap,
    //             'kelas'=>$kelas,
    //             'no_hp'=>$no_hp,
    //             'angkatan_id'=>$angkatan_id,
    //             'foto'=>$foto,
    //         ];
    //     $simpan = DB::table('siswa')->insert($data);

    //     if($simpan) {
    //         if ($request->hasFile('foto')) {
    //             $folderPath = "public/uploads/siswa/"; // Folder penyimpanan foto
    //             $request->file('foto')->storeAs($folderPath, $foto); // Simpan foto ke folder
    //         }
    //         return Redirect::back()->with('success', 'Data Berhasil Disimpan');
    //     }
    //     } catch (\Exception $e) {
    //         dd($e);
    //         // return Redirect::back()->with('warning', 'Data Gagal Disimpan');
    //     }

    // }


    private function generateUsernameFromNama($nama_lengkap)
    {
        $kataUmum = ['muhammad', 'm', 'siti', 'ahmad', 'muhamad', 'mohammad', 'mohamad', 'a'];
        $namaArr = explode(' ', strtolower(trim(preg_replace('/\s+/', ' ', $nama_lengkap))));

        // Ambil kata khas dari nama
        $kataKhas = array_values(array_filter($namaArr, function ($kata) use ($kataUmum) {
            return !in_array($kata, $kataUmum);
        }));

        // Gunakan kata khas pertama atau fallback ke nama pertama
        $utama = isset($kataKhas[0]) ? $kataKhas[0] : $namaArr[0];

        // Ambil dua huruf awal dari dua kata terakhir (jika ada)
        $jumlah = count($kataKhas);
        $inisialAkhir = '';
        if ($jumlah >= 3) {
            $inisialAkhir = $kataKhas[$jumlah - 2][0] . $kataKhas[$jumlah - 1][0];
        } elseif ($jumlah == 2) {
            $inisialAkhir = $kataKhas[1][0] . $kataKhas[1][1];
        } elseif ($jumlah == 1 && strlen($kataKhas[0]) > 1) {
            $inisialAkhir = substr($kataKhas[0], 0, 2);
        }

        return strtolower($utama . $inisialAkhir);
    }

    public function store(Request $request)
    {
        $nis = $request->nis;
        $nama_lengkap = $request->nama_lengkap;
        $kelas = $request->kelas;
        $no_hp = $request->no_hp;
        $angkatan_id = $request->angkatan_id;

        // // 1. Generate username dari nama_lengkap
        // $namaArr = explode(' ', trim($nama_lengkap));
        // $depan = strtolower(substr($namaArr[0], 0, 8));
        // $belakang = isset($namaArr[2]) ? strtolower(substr($namaArr[2], 0, 2)) : (isset($namaArr[1]) ? strtolower(substr($namaArr[1], 0, 2)) : '');
        // $username = $depan . $belakang;

        // 1. Generate username dari nama_lengkap
        $username = $this->generateUsernameFromNama($nama_lengkap);

        // Pastikan username unik
        $originalUsername = $username;
        $counter = 1;
        while (DB::table('user')->where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        $password = bcrypt($nis); // Atau password default lain
        $role = 'Siswa';

        // 2. Insert ke tabel user dan siswa dalam satu transaksi
        $data = [
            'user_id' => null,
            'nis' => $nis,
            'nama_lengkap' => $nama_lengkap,
            'kelas' => $kelas,
            'no_hp' => $no_hp,
            'angkatan_id' => $angkatan_id,
            'foto' => $request->hasFile('foto') ? $nis . "." . $request->file('foto')->getClientOriginalExtension() : null,
        ];

        try {
            $simpan = DB::transaction(function () use ($username, $password, $role, &$data) {
                $data['user_id'] = DB::table('user')->insertGetId([
                    'username' => $username,
                    'password' => $password,
                    'role' => $role,
                ]);
                return DB::table('siswa')->insert($data);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            return Redirect::back()->with('warning', 'Data Gagal Disimpan');
        }

        if ($simpan) {
            if ($request->hasFile('foto')) {
                $folderPath = "public/uploads/siswa/";
                $request->file('foto')->storeAs($folderPath, $foto);
            }
            return Redirect::back()->with('success', 'Data Siswa Berhasil Disimpan');
        } else {
            return Redirect::back()->with('warning', 'Data Gagal Disimpan');
        }
    }

    public function edit(Request $request) {
        $nis = $request->nis;
        $angkatan = DB::table('angkatan')->get();
        $siswa = DB::table('siswa')->where('nis',$nis)->first();

        return view('siswa.edit',compact('angkatan','siswa'));
    }

    public function update($nis, Request $request) {
        $nis = $request->nis;
        $nama_lengkap = $request->nama_lengkap;
        $kelas = $request->kelas;
        $no_hp = $request->no_hp;
        $angkatan_id = $request->angkatan_id;

        // Foto lama diambil dari database, bukan dari request (tidak bisa dipercaya)
        $siswa = DB::table('siswa')->where('nis', $nis)->first();
        $old_foto = $siswa->foto;

        if ($request->hasFile('foto')) {
            $foto = $nis . "." . $request->file('foto')->getClientOriginalExtension();
        } else {
            $foto = $old_foto;
        }

        $data = [
            'nama_lengkap' => $nama_lengkap,
            'kelas' => $kelas,
            'no_hp' => $no_hp,
            'angkatan_id' => $angkatan_id,
            'foto' => $foto,
        ];

        $update = DB::table('siswa')->where('nis',$nis)->update($data);

        if ($update) {
            if ($request->hasFile('foto')) {
                $folderPath = "public/uploads/siswa/";
                $folderPathOld = "public/uploads/siswa/" . $old_foto;
                Storage::delete($folderPathOld);
                $request->file('foto')->storeAs($folderPath, $foto);
            }
            return Redirect::back()->with('success', 'Data Berhasil Diupdate');
        } else {
            return Redirect::back()->with('warning', 'Data Gagal Diupdate');
        }
    }

    public function delete($nis) {
        $siswa = DB::table('siswa')->where('nis', $nis)->first();
        if (!$siswa) {
            return Redirect::back()->with('warning', 'Data Gagal Dihapus');
        }

        DB::transaction(function () use ($siswa) {
            DB::table('siswa')->where('siswa_id', $siswa->siswa_id)->delete();
            DB::table('user')->where('user_id', $siswa->user_id)->delete();
        });

        if ($siswa->foto) {
            Storage::delete('public/uploads/siswa/' . $siswa->foto);
        }

        return Redirect::back()->with('success', 'Data Berhasil Dihapus');
    }
}
