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
                return redirect()->route('guru');
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

    Route::post('/proseslogin', [AuthController::class, 'proseslogin'])->name('proseslogin')->middleware('throttle:5,1');
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
    Route::post('/presensi/store', [App\Http\Controllers\PresensiController::class, 'store'])->name('presensi.store');

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
    Route::post('/siswa/store', [App\Http\Controllers\SiswaController::class, 'store'])->name('siswa.store');
    Route::post('/siswa/edit', [App\Http\Controllers\SiswaController::class, 'edit'])->name('siswa.edit');
    Route::post('/siswa/{nis}/update', [App\Http\Controllers\SiswaController::class, 'update'])->name('siswa.update');
    Route::post('/siswa/{nis}/delete', [App\Http\Controllers\SiswaController::class, 'delete'])->name('siswa.delete');

    // Angkatan
    Route::get('/angkatan', [App\Http\Controllers\AngkatanController::class, 'index'])->name('angkatan');
    Route::post('/angkatan/store', [App\Http\Controllers\AngkatanController::class, 'store'])->name('angkatan.store');
    Route::post('/angkatan/edit', [App\Http\Controllers\AngkatanController::class, 'edit'])->name('angkatan.edit');
    Route::post('/angkatan/{angkatan_id}/update', [App\Http\Controllers\AngkatanController::class, 'update'])->name('angkatan.update');
    Route::post('/angkatan/{angkatan_id}/delete', [App\Http\Controllers\AngkatanController::class, 'delete'])->name('angkatan.delete');

    // Monitoring
    Route::get('/monitoring', [App\Http\Controllers\PresensiController::class, 'monitoring'])->name('admin.monitoring');
    Route::post('/getpresensi', [App\Http\Controllers\PresensiController::class, 'getpresensi'])->name('admin.getpresensi');
    Route::post('/tampilkanpeta', [App\Http\Controllers\PresensiController::class, 'tampilkanpeta'])->name('admin.tampilkanpeta');
    Route::get('/laporan', [App\Http\Controllers\PresensiController::class, 'laporan'])->name('laporan');
    Route::post('/presensi/cetaklaporan', [App\Http\Controllers\PresensiController::class, 'cetaklaporan'])->name('admin.cetaklaporan');
    Route::get('/rekap', [App\Http\Controllers\PresensiController::class, 'rekap'])->name('rekap');
    Route::post('/presensi/cetakrekap', [App\Http\Controllers\PresensiController::class, 'cetakrekap'])->name('admin.cetakrekap');
    Route::get('/izinsakit', [App\Http\Controllers\PresensiController::class, 'izinsakit'])->name('admin.izinsakit');
    Route::post('/presensi/approveizinsakit', [App\Http\Controllers\PresensiController::class, 'approveizinsakit'])->name('admin.approveizinsakit');
    Route::get('/presensi/{izin_id}/batalkanizinsakit', [App\Http\Controllers\PresensiController::class, 'batalkanizinsakit'])->name('admin.batalkanizinsakit');
});

Route::middleware(['auth', 'cekrole:Guru'])->group(function () {
    //guru
    Route::get('/guru', [App\Http\Controllers\DashboardController::class, 'guru'])->name('guru');
    Route::get('/monitoringguru', [App\Http\Controllers\GuruController::class, 'monitoring'])->name('guru.monitoring');
    Route::post('/getpresensiguru', [App\Http\Controllers\GuruController::class, 'getpresensi'])->name('guru.getpresensi');
    Route::post('/tampilkanpetaguru', [App\Http\Controllers\GuruController::class, 'tampilkanpeta'])->name('guru.tampilkanpeta');
    Route::get('/laporanguru', [App\Http\Controllers\GuruController::class, 'laporan'])->name('laporanguru');
    Route::post('/presensi/cetaklaporanguru', [App\Http\Controllers\GuruController::class, 'cetaklaporan'])->name('guru.cetaklaporan');
    Route::get('/rekapguru', [App\Http\Controllers\GuruController::class, 'rekap'])->name('rekapguru');
    Route::post('/presensi/cetakrekapguru', [App\Http\Controllers\GuruController::class, 'cetakrekap'])->name('guru.cetakrekap');
    Route::get('/izinsakitguru', [App\Http\Controllers\GuruController::class, 'izinsakit'])->name('guru.izinsakit');
    Route::post('/presensi/approveizinsakitguru', [App\Http\Controllers\GuruController::class, 'approveizinsakit'])->name('guru.approveizinsakit');
    Route::get('/presensi/{izin_id}/batalkanizinsakitguru', [App\Http\Controllers\GuruController::class, 'batalkanizinsakit'])->name('guru.batalkanizinsakit');
});

