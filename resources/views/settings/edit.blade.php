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
                                @switch($title)
                                    @case('Role')
                                        <h3 class="card-title">EDIT &raquo; {{ $role->name }}</h3>
                                    @break
                                    @case('Badan Usaha')
                                        <h3 class="card-title">EDIT &raquo; {{ $badanusaha->name }}</h3>
                                    @break
                                    @case('Divisi')
                                        <h3 class="card-title">EDIT &raquo; {{ $divisi->name }}</h3>
                                    @break
                                    @case('Region')
                                        <h3 class="card-title">EDIT &raquo; {{ $region->name }}</h3>
                                    @break

                                    @default
                                        <h3 class="card-title">EDIT &raquo; {{ $cluster->name }}</h3>
                                @endswitch
                            </div>
                            <div class="card-body">
                                @switch($title)
                                    @case('Role')
                                        <form action="/setting/role/{{ $role->id }}" method="POST">
                                            @csrf
                                            <div class="mb-3 col-lg-3">
                                                <label for="name" class="form-label">Nama Role</label>
                                                <input type="text" class="form-control" id="name" name="name"
                                                    value="{{ $role->name }}" required>
                                            </div>
                                            <button type="submit" class="btn btn-success mt-3">Update</button>
                                        </form>
                                    @break
                                    @case('Badan Usaha')
                                        <form action="/setting/badanusaha/{{ $badanusaha->id }}" method="POST">
                                            @csrf
                                            <div class="mb-3 col-lg-3">
                                                <label for="name" class="form-label">Nama Badan Usaha</label>
                                                <input type="text" class="form-control" id="name" name="name"
                                                    value="{{ $badanusaha->name }}" required>
                                            </div>
                                            <button type="submit" class="btn btn-success mt-3">Update</button>
                                        </form>
                                    @break
                                    @case('Divisi')
                                        <form action="/setting/divisi/{{ $divisi->id }}" method="POST">
                                            @csrf
                                            <div class="mb-3 col-lg-3">
                                                <label for="name" class="form-label">Nama Divisi</label>
                                                <input type="text" class="form-control" id="name" name="name"
                                                    value="{{ $divisi->name }}" required>
                                            </div>
                                            <button type="submit" class="btn btn-success mt-3">Update</button>
                                        </form>
                                    @break
                                    @case('Region')
                                        <form action="/setting/region/{{ $region->id }}" method="POST">
                                            @csrf
                                            <div class="mb-3 col-lg-3">
                                                <label for="name" class="form-label">Nama Region</label>
                                                <input type="text" class="form-control" id="name" name="name"
                                                    value="{{ $region->name }}" required>
                                            </div>
                                            <button type="submit" class="btn btn-success mt-3">Update</button>
                                        </form>
                                    @break

                                    @default
                                        <form action="/setting/cluster/{{ $cluster->id }}" method="POST">
                                            @csrf
                                            <div class="mb-3 col-lg-3">
                                                <label for="name" class="form-label">Nama Cluster</label>
                                                <input type="text" class="form-control" id="name" name="name"
                                                    value="{{ $cluster->name }}" required>
                                            </div>
                                            <button type="submit" class="btn btn-success mt-3">Update</button>
                                        </form>

                                @endswitch

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
