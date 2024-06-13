@extends('layout.main_tamplate')

@section('content')
    <section class="content-header">
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card card-dark">
                            <!-- /.card-header -->
                            <div class="card-header">
                                <h3 class="card-title">EDIT &raquo; {{ $user->nama_lengkap }}</h3>
                            </div>
                            <div class="card-body">
                                <form action="/user/{{ $user->id }}" method="POST">
                                    @csrf
                                    <div class="mb-3 ">
                                        <label for="username" class="form-label">User Name</label>
                                        <input type="text" class="form-control" id="username" name="username"
                                            value="{{ $user->username }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="text" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="nama_lengkap"
                                            value="{{ $user->nama_lengkap }}" name="nama_lengkap" required>
                                    </div>
                                    <div class="mb-3">
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <label for="role_id" class="form-label col-lg-12">Role</label>
                                                <select class="custom-select col-lg-12" name="role_id" id="role_id"
                                                    required>
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role->id }}" @if ($user->role_id === $role->id)
                                                            selected
                                                    @endif>
                                                    {{ $role->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-lg-4">
                                                <label for="badanusaha_id" class="form-label col-lg-12">Badan Usaha</label>
                                                <select class="custom-select col-lg-12" id="badanusaha_id"
                                                    name="badanusaha_id" required>
                                                    @foreach ($badanusahas as $badanusaha)
                                                        <option value="{{ $badanusaha->id }}"
                                                            {{ $badanusaha->id === $user->badanusaha_id ? 'selected' : '' }}>
                                                            {{ $badanusaha->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-lg-4">
                                                <label for="divisi_id" class="form-label col-lg-12">Divisi</label>
                                                <select class="custom-select col-lg-12" id="divisi_id" name="divisi_id"
                                                    required>
                                                    @foreach ($divisis as $divisi)
                                                        <option value="{{ $divisi->id }}"
                                                            {{ $divisi->id === $user->divisi_id ? 'selected' : '' }}>
                                                            {{ $divisi->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <label for="region_id" class="form-label col-lg-12">Region</label>
                                                <select class="custom-select col-lg-12" name="region_id" id="region_id"
                                                    required>
                                                    @foreach ($regions as $region)
                                                        <option value="{{ $region->id }}"
                                                            {{ $region->id === $user->region_id ? 'selected' : '' }}>
                                                            {{ $region->name.' - '.$region->divisi->name.' - '.$region->badanusaha->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-lg-4">
                                                <label for="cluster_id" class="form-label col-lg-12">Cluster</label>
                                                <select class="custom-select col-lg-12" id="cluster_id" name="cluster_id"
                                                    required>
                                                    @foreach ($clusters as $cluster)
                                                        <option value="{{ $cluster->id }}"
                                                            {{ $cluster->id === $user->cluster_id ? 'selected' : '' }}>
                                                            {{ $cluster->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-lg-4">
                                                <label for="cluster_id" class="form-label col-lg-12">Cluster (Optional)</label>
                                                <select class="custom-select col-lg-12" id="cluster_id2" name="cluster_id2">
                                                    <option value="" disabled selected>Pilih Cluster</option>
                                                    @foreach ($clusters as $cluster)
                                                        <option value="{{ $cluster->id }}" {{ $cluster->id === $user->cluster_id2 ? 'selected' : '' }}>
                                                            {{ $cluster->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-success mt-3">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </section>
    </section>
    <!-- /.content -->
@endsection
