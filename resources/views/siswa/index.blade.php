@extends('layouts.admin.tabler')
@section('content')
    <div class="page-header d-print-none" aria-label="Page header">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Data Master</div>
                    <h2 class="page-title">Data Siswa</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            <div class="row">
                                <div class="col-12">
                                    @if (Session::get('success'))
                                        <div class="alert alert-success">{{ Session::get('success') }}</div>
                                    @endif
                                    @if (Session::get('warning'))
                                        <div class="alert alert-warning">{{ Session::get('warning')}}</div>
                                    @endif
                                </div>
                            </div>

                            <!-- BEGIN FILTER -->
                            <div class="row">
                                <div class="col-12">
                                    <a href="#" class="btn btn-primary" id="btnTambahSiswa"><svg
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M12 5l0 14" />
                                            <path d="M5 12l14 0" />
                                        </svg>Tambah Data</a>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-12">
                                    <form action="/siswa" method="GET" id="filterData">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <input type="text" name="nama_lengkap" id="nama_lengkap"
                                                        class="form-control" placeholder="Nama Siswa"
                                                        value="{{ Request('nama_lengkap') }}">
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-group">
                                                    <select name="angkatan_id" id="angkatan_id" class="form-select">
                                                        <option value="">Pilih Angkatan</option>
                                                        @foreach ($angkatan as $d)
                                                            <option {{ Request('angkatan_id') == $d->angkatan_id ? 'selected' : '' }} value="{{  $d->angkatan_id }}">{{ $d->nama_angkatan }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-2">
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-search">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                                                            <path d="M21 21l-6 -6" />
                                                        </svg>
                                                        Cari
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- END FILTER -->

                            <!-- BEGIN TABLE -->
                            <div class="row mt-2">
                                <div class="col-12">
                                    <div class="table-responsive">
                                    <table class="table table-bordered table-vcenter table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>NIS</th>
                                                <th>Nama</th>
                                                <th>Kelas</th>
                                                <th>No. HP</th>
                                                <th>Foto</th>
                                                <th>Angkatan</th>
                                                <th>Aksi</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($siswa as $d)
                                                @php
                                                    $path = Storage::url('public/uploads/siswa/' . $d->foto);
                                                @endphp

                                                <tr>
                                                    <td>{{ $loop->iteration + $siswa->firstItem() - 1}}</td>
                                                    <td>{{ $d->nis }}</td>
                                                    <td>{{ $d->nama_lengkap }}</td>
                                                    <td>{{ $d->kelas }}</td>
                                                    <td>{{ $d->no_hp }}</td>
                                                    <td>
                                                        @if (empty($d->foto))
                                                            <img src="{{asset('assets/img/sample/avatar/avatar1.jpg')}}"
                                                                class="avatar" alt="No Photo">
                                                        @else
                                                            <img src="{{ $path }}" class="avatar" alt="Foto Profil">

                                                        @endif
                                                    </td>
                                                    <td>{{ $d->nama_angkatan }}</td>
                                                    <td>

                                                        <div class="btn-group">
                                                            <a href="#" class="edit btn btn-primary btn-sm" nis="{{ $d->nis }}"><svg
                                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-edit">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path
                                                                    d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                                                                <path
                                                                    d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" />
                                                                <path d="M16 5l3 3" />
                                                            </svg></a>

                                                            <form action="/siswa/{{ $d->nis }}/delete" method="POST" style="margin-left:5px" >
                                                            @csrf

                                                                <a class="btn btn-danger btn-sm delete-confirm">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                        stroke-width="2" stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                        <path d="M4 7l16 0" />
                                                                        <path d="M10 11l0 6" />
                                                                        <path d="M14 11l0 6" />
                                                                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                                        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                                    </svg>
                                                                </a>
                                                            </form>

                                                        </div>
                                                    </td>

                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    </div>
                                </div>
                            </div>
                            <!-- END TABLE -->

                            <!-- START PAGINATION -->
                            {{ $siswa->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- BEGIN MODAL TAMBAH DATA SISWA -->
    <div class="modal modal-blur fade" id="modal-inputsiswa" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Data Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/siswa/store" method="POST" id="frmSiswa" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <div class="input-icon mb-3">
                                    <span class="input-icon-addon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-barcode">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M4 7v-1a2 2 0 0 1 2 -2h2" />
                                            <path d="M4 17v1a2 2 0 0 0 2 2h2" />
                                            <path d="M16 4h2a2 2 0 0 1 2 2v1" />
                                            <path d="M16 20h2a2 2 0 0 0 2 -2v-1" />
                                            <path d="M5 11h1v2h-1z" />
                                            <path d="M10 11l0 2" />
                                            <path d="M14 11h1v2h-1z" />
                                            <path d="M19 11l0 2" />
                                        </svg>
                                    </span>
                                    <input type="text" value="" id="nis" class="form-control" name="nis"
                                        placeholder="Nomor Induk Siswa (NIS)">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="input-icon mb-3">
                                    <span class="input-icon-addon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-user">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                            <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                        </svg>
                                    </span>
                                    <input type="text" value="" id="nama_lengkap" class="form-control" name="nama_lengkap"
                                        placeholder="Nama Lengkap Siswa">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="input-icon mb-3">
                                    <span class="input-icon-addon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-school">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M22 9l-10 -4l-10 4l10 4l10 -4v6" />
                                            <path d="M6 10.6v5.4a6 3 0 0 0 12 0v-5.4" />
                                        </svg>
                                    </span>
                                    <input type="text" value="" id="kelas" class="form-control" name="kelas"
                                        placeholder="Kelas">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="input-icon mb-3">
                                    <span class="input-icon-addon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-phone">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" />
                                        </svg>
                                    </span>
                                    <input type="text" value="" id="no_hp" class="form-control" name="no_hp"
                                        placeholder="No. HP">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mt-2">
                                    <input type="file" id="foto" class="form-control" name="foto">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-12">
                                <select name="angkatan_id" id="angkatan_id" class="form-select">
                                    <option value="">Pilih Angkatan</option>
                                    @foreach ($angkatan as $d)
                                        <option {{ Request('angkatan_id') == $d->angkatan_id ? 'selected' : '' }}
                                            value="{{  $d->angkatan_id }}">{{ $d->nama_angkatan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-12">
                                <div class="form-group">
                                    <button class="btn btn-primary w-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-send">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M10 14l11 -11" />
                                            <path
                                                d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5" />
                                        </svg>
                                        Simpan
                                    </button>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- END MODAL TAMBAH DATA SISWA -->

    <!-- BEGIN MODAL EDIT SISWA -->
    <div class="modal modal-blur fade" id="modal-editsiswa" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="loadeditform">

                </div>
            </div>
        </div>
    </div>
    <!-- END MODAL EDIT SISWA -->

@endsection

@push('myscript')
    <script>
        $(function () {
            $("#btnTambahSiswa").click(function () {
                $("#modal-inputsiswa").modal("show");
            });

            $(".edit").click(function () {
                var nis = $(this).attr('nis');
                $.ajax({
                    type: 'POST',
                    url: '/siswa/edit',
                    cache: false,
                    data: {
                        _token: "{{  csrf_token() }}",
                        nis: nis,
                    },
                    success: function (respond) {
                        $("#loadeditform").html(respond);
                    }
                });

                $("#modal-editsiswa").modal("show");
            });

            $(".delete-confirm").click(function (e) {
                var form = $(this).closest("form");
                e.preventDefault();
                Swal.fire({
                    title: 'Anda Yakin?',
                    text: "Data Siswa Akan Dihapus !",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus !'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire('Deleted!', 'Data Siswa Berhasil Dihapus !', 'success');
                        form.submit();
                    }

                })
            });


            $("#frmSiswa").submit(function () {
                var nis = $("#nis").val();
                var nama_lengkap = $("#frmSiswa").find("#nama_lengkap").val();
                var kelas = $("#kelas").val();
                var no_hp = $("#no_hp").val();
                var angkatan_id = $("#frmSiswa").find("#angkatan_id").val();
                // var angkatan_id = $("#angkatan_id").val();

                if (nis == "") {
                    Swal.fire({
                        title: 'Warning!',
                        text: 'NIS Harus Diisi !',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    }).then((result) => {
                        $("#nis").focus();
                    });
                    return false;
                } else if (nama_lengkap == "") {
                    Swal.fire({
                        title: 'Warning!',
                        text: 'Nama Lengkap Harus Diisi !',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    }).then((result) => {
                        $("#nama_lengkap").focus();
                    });
                    return false;
                } else if (kelas == "") {
                    Swal.fire({
                        title: 'Warning!',
                        text: 'Kelas Harus Diisi !',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    }).then((result) => {
                        $("#kelas").focus();
                    });
                    return false;
                } else if (no_hp == "") {
                    Swal.fire({
                        title: 'Warning!',
                        text: 'No. HP Harus Diisi !',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    }).then((result) => {
                        $("#no_hp").focus();
                    });
                    return false;
                } else if (angkatan_id == "") {
                    Swal.fire({
                        title: 'Warning!',
                        text: 'Angkatan Harus Dipilih !',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    }).then((result) => {
                        $("#angkatan_id").focus();
                    });
                    return false;
                }
            });
        });
    </script>
@endpush
