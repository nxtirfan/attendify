<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PresensiController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Jika ingin mengembalikan ke versi awal, uncomment bagian ini
/*
Route::get('/', function () {
    return view('auth.login');
})->name('login');

Route::post('/proseslogin', 'App\Http\Controllers\AuthController@proseslogin')->name('proseslogin');


// Rute untuk halaman utama aplikasi
Route::get('/', function () {
    // Jika user sudah login, arahkan ke dashboard
    // Jika belum, arahkan ke login
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
}); 
*/


Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;

        // Jika user sudah login, arahkan ke rute sesuai role
        switch ($role) {
            case 'Admin':
                return redirect()->route('admin');
            case 'Guru':
                return redirect()->route('dashboard.guru'); // pastikan rute ini ada
            case 'Siswa':
                return redirect()->route('dashboard');
            default:
                Auth::logout();
                return redirect()->route('login')->withErrors('Role tidak valid');
        }
    }

    return redirect()->route('login');
});


// Rute hanya bisa diakses oleh user yang BELUM login
Route::middleware(['guest'])->group(function () {
    Route::get('/login', function () {
        // User tamu, tampilkan halaman login
        return view('auth.login');
    })->name('login');

    Route::post('/proseslogin', [AuthController::class, 'proseslogin'])->name('proseslogin');
});

// Rute hanya bisa diakses oleh user yang SUDAH login
Route::middleware(['auth'])->group(function () {
    Route::get('/proseslogout', [App\Http\Controllers\AuthController::class, 'proseslogout'])->name('proseslogout');
});

// Rute Siswa
Route::middleware(['auth', 'cekrole:Siswa'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    //Presensi Routes
    Route::get('/presensi/create', [App\Http\Controllers\PresensiController::class, 'create'])->name('create');
    Route::post('/presensi/store', [App\Http\Controllers\PresensiController::class, 'store'])->name('store');

    //Edit Profile
    Route::get('/editprofile', [App\Http\Controllers\PresensiController::class, 'editprofile'])->name('editprofile');
    Route::post('/presensi/{nis}/updateprofile', [App\Http\Controllers\PresensiController::class, 'updateprofile'])->name('updateprofile');

    //Histori
    Route::get('/presensi/histori', [App\Http\Controllers\PresensiController::class, 'histori'])->name('histori');
    Route::post('/gethistori', [App\Http\Controllers\PresensiController::class, 'gethistori'])->name('gethistori');

    //Izin
    Route::get('/presensi/izin', [App\Http\Controllers\PresensiController::class, 'izin'])->name('izin');
    Route::get('/presensi/buatizin', [App\Http\Controllers\PresensiController::class, 'buatizin'])->name('buatizin');
    Route::post('/presensi/storeizin', [App\Http\Controllers\PresensiController::class, 'storeizin'])->name('storeizin');
    Route::post('/presensi/cekizin', [App\Http\Controllers\PresensiController::class, 'cekizin'])->name('cekpengajuanizin');

});

// Rute Admin
Route::middleware(['auth', 'cekrole:Admin'])->group(function () {
    Route::get('/admin', [App\Http\Controllers\DashboardController::class, 'admin'])->name('admin');

    //Siswa
    Route::get('/siswa', [App\Http\Controllers\SiswaController::class, 'index'])->name('siswa');
    Route::post('/siswa/store', [App\Http\Controllers\SiswaController::class, 'store'])->name('store');
    Route::post('/siswa/edit', [App\Http\Controllers\SiswaController::class, 'edit'])->name('edit');
    Route::post('/siswa/{nis}/update', [App\Http\Controllers\SiswaController::class, 'update'])->name('update');
    Route::post('/siswa/{nis}/delete', [App\Http\Controllers\SiswaController::class, 'delete'])->name('delete');

    // Angkatan
    Route::get('/angkatan', [App\Http\Controllers\AngkatanController::class, 'index'])->name('angkatan');
    Route::post('/angkatan/store', [App\Http\Controllers\AngkatanController::class, 'store'])->name('store');
    Route::post('/angkatan/edit', [App\Http\Controllers\AngkatanController::class, 'edit'])->name('edit');
    Route::post('/angkatan/{angkatan_id}/update', [App\Http\Controllers\AngkatanController::class, 'update'])->name('update');
    Route::post('/angkatan/{angkatan_id}/delete', [App\Http\Controllers\AngkatanController::class, 'delete'])->name('delete');

    // Monitoring
    Route::get('/monitoring', [App\Http\Controllers\PresensiController::class, 'monitoring'])->name('monitoring');
    Route::post('/getpresensi', [App\Http\Controllers\PresensiController::class, 'getpresensi'])->name('getpresensi');
    Route::post('/tampilkanpeta', [App\Http\Controllers\PresensiController::class, 'tampilkanpeta'])->name('tampilkanpeta');
    Route::get('/laporan', [App\Http\Controllers\PresensiController::class, 'laporan'])->name('laporan');
    Route::post('/presensi/cetaklaporan', [App\Http\Controllers\PresensiController::class, 'cetaklaporan'])->name('cetaklaporan');
    Route::get('/rekap', [App\Http\Controllers\PresensiController::class, 'rekap'])->name('rekap');
    Route::post('/presensi/cetakrekap', [App\Http\Controllers\PresensiController::class, 'cetakrekap'])->name('cetakrekap');
    Route::get('/izinsakit', [App\Http\Controllers\PresensiController::class, 'izinsakit'])->name('izinsakit');
    Route::post('/presensi/approveizinsakit', [App\Http\Controllers\PresensiController::class, 'approveizinsakit'])->name('approveizinsakit');
    Route::get('/presensi/{izin_id}/batalkanizinsakit', [App\Http\Controllers\PresensiController::class, 'batalkanizinsakit'])->name('batalkanizinsakit');
});

Route::middleware(['auth', 'cekrole:Guru'])->group(function () {
    //guru
    Route::get('/guru', [App\Http\Controllers\DashboardController::class, 'guru'])->name('guru');
    Route::get('/monitoringguru', [App\Http\Controllers\GuruController::class, 'monitoring'])->name('monitoring');
    Route::post('/getpresensiguru', [App\Http\Controllers\GuruController::class, 'getpresensi'])->name('getpresensi');
    Route::post('/tampilkanpetaguru', [App\Http\Controllers\GuruController::class, 'tampilkanpeta'])->name('tampilkanpeta');
    Route::get('/laporanguru', [App\Http\Controllers\GuruController::class, 'laporan'])->name('laporan');
    Route::post('/presensi/cetaklaporanguru', [App\Http\Controllers\GuruController::class, 'cetaklaporan'])->name('cetaklaporan');
    Route::get('/rekapguru', [App\Http\Controllers\GuruController::class, 'rekap'])->name('rekap');
    Route::post('/presensi/cetakrekapguru', [App\Http\Controllers\GuruController::class, 'cetakrekap'])->name('cetakrekap');
    Route::get('/izinsakitguru', [App\Http\Controllers\GuruController::class, 'izinsakit'])->name('izinsakit');
    Route::post('/presensi/approveizinsakitguru', [App\Http\Controllers\GuruController::class, 'approveizinsakit'])->name('approveizinsakit');
    Route::get('/presensi/{izin_id}/batalkanizinsakitguru', [App\Http\Controllers\GuruController::class, 'batalkanizinsakit'])->name('batalkanizinsakit');
});

