<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('angkatan')->insert([
            ['angkatan_id' => 22, 'nama_angkatan' => 'Angkatan 2022'],
            ['angkatan_id' => 23, 'nama_angkatan' => 'Angkatan 2023'],
            ['angkatan_id' => 24, 'nama_angkatan' => 'Angkatan 2024'],
        ]);

        $passwordSiswa = Hash::make('password1');
        $passwordAdmin = Hash::make('admin123');

        $userAdmin = DB::table('user')->insertGetId([
            'username' => 'admin1',
            'password' => $passwordAdmin,
            'role' => 'Admin',
        ]);

        $userGuru = DB::table('user')->insertGetId([
            'username' => 'guru1',
            'password' => $passwordSiswa,
            'role' => 'Guru',
        ]);

        DB::table('guru')->insert([
            'user_id' => $userGuru,
            'nama_lengkap' => 'Dimas Tejo Gumilang, S.Pd.',
            'no_hp' => '081200000005',
            'email' => 'guru.contoh@example.com',
        ]);

        $dataSiswa = [
            ['nis' => '24023', 'nama_lengkap' => 'M Irfan Al Hakim', 'kelas' => 'X', 'angkatan_id' => 24, 'no_hp' => '081200000001'],
            ['nis' => '23002', 'nama_lengkap' => 'Salfa Putri Lestari', 'kelas' => 'XI', 'angkatan_id' => 23, 'no_hp' => '081200000002'],
            ['nis' => '22018', 'nama_lengkap' => 'Kanaya Azalea Chandra', 'kelas' => 'XII', 'angkatan_id' => 22, 'no_hp' => '081200000003'],
        ];

        foreach ($dataSiswa as $s) {
            $user_id = DB::table('user')->insertGetId([
                'username' => strtolower(str_replace(' ', '', $s['nama_lengkap'])),
                'password' => $passwordSiswa,
                'role' => 'Siswa',
            ]);
            DB::table('siswa')->insert([
                'user_id' => $user_id,
                'nis' => $s['nis'],
                'nama_lengkap' => $s['nama_lengkap'],
                'kelas' => $s['kelas'],
                'angkatan_id' => $s['angkatan_id'],
                'no_hp' => $s['no_hp'],
            ]);
        }

        $this->command->info('Seeder dummy selesai. Akun: admin1 (admin123), guru1 (password1), siswa NIS 24023/23002/22018 (password1).');
    }
}
