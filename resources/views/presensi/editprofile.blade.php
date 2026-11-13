@extends('layouts.presensi')
@section('header')

    <!-- App Header -->
    <div class="appHeader bg-primary text-light attendify-subheader">
        <div class="left">
            <a href="javascript:;" class="headerButton goBack">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">Ubah Profil</div>
        <div class="right"></div>
    </div>
    <!-- * App Header -->
@endsection

@section('content')
<div class="attendify-subpage" style="margin-top: 68px">
    <div class="row">
        <div class="col">
            @php
                $messagesuccess = Session::get('success');
                $messageerror = Session::get('error');
            @endphp
            @if (Session::get('success'))
            <div class="alert alert-info attendify-alert-info mb-2">
                <ion-icon name="checkmark-circle-outline"></ion-icon>
                <div>{{ $messagesuccess }}</div>
            </div>
            @endif
            @if (Session::get('error'))
            <div class="alert alert-danger attendify-alert-error mb-2">
                <ion-icon name="alert-circle-outline"></ion-icon>
                <div>{{ $messageerror }}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="card attendify-card mb-2 attendify-profile-banner">
        <div class="card-body d-flex align-items-center gap-3">
            <div class="attendify-profile-avatar">
                @if(!empty($siswa->foto))
                    @php
                        $path = Storage::url('public/uploads/siswa/' . $siswa->foto);
                    @endphp
                    <img src="{{ $path }}" alt="Foto Profil" class="imaged w64 rounded">
                @else
                    <img src="{{ asset('assets/img/sample/avatar/avatar1.jpg') }}" alt="Foto Profil" class="imaged w64 rounded">
                @endif
            </div>
            <div class="attendify-profile-meta">
                <h4 class="attendify-profile-name">{{ $siswa->nama_lengkap }}</h4>
                <div class="attendify-profile-tags">
                    <span class="attendify-badge attendify-badge--hadir">NIS: {{ $siswa->nis }}</span>
                    @if(!empty($siswa->kelas))
                        <span class="attendify-badge attendify-badge--izin">Kelas {{ $siswa->kelas }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card attendify-card">
        <div class="card-body">
            <form action="/presensi/{{ $siswa->nis }}/updateprofile" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="col p-0">
                    <div class="form-group boxed">
                        <label class="attendify-field-label">Nama Lengkap</label>
                        <div class="input-wrapper">
                            <input type="text" class="form-control attendify-input" value="{{ $siswa->nama_lengkap }}" name="nama_lengkap" placeholder="Nama Lengkap" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group boxed">
                        <label class="attendify-field-label">Nomor HP / WhatsApp</label>
                        <div class="input-wrapper">
                            <input type="text" class="form-control attendify-input" value="{{ $siswa->no_hp }}" name="no_hp" placeholder="No. HP" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group boxed">
                        <label class="attendify-field-label">Kata Sandi Baru <small class="text-muted">(Opsional)</small></label>
                        <div class="input-wrapper">
                            <input type="password" class="form-control attendify-input" name="password" placeholder="Kosongkan jika tidak ingin mengubah kata sandi" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group boxed">
                        <label class="attendify-field-label">Foto Profil</label>
                        <div class="custom-file-upload attendify-upload-box" id="fileUpload1">
                            <input type="file" name="foto" id="fileuploadInput" accept=".png, .jpg, .jpeg">
                            <label for="fileuploadInput">
                                <span>
                                    <strong>
                                        <ion-icon name="cloud-upload-outline" role="img" class="md hydrated" aria-label="cloud upload outline"></ion-icon>
                                        <i>Ketuk untuk Mengunggah Foto Baru (.jpg / .png)</i>
                                    </strong>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group boxed mt-2 mb-0">
                        <div class="input-wrapper">
                            <button type="submit" class="btn btn-primary btn-block attendify-btn-action">
                                <ion-icon name="refresh-outline"></ion-icon>
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
