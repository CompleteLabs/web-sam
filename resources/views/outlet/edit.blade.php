@extends('layout.main_tamplate')

@section('content')
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-dark mt-3">
                        <div class="card-header">
                            <h4 class="card-title">EDIT &raquo; {{ $outlet->nama_outlet }}</h4>
                        </div>
                        <div class="card-body">
                            <form action="/outlet/{{ $outlet->id }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="kode_outlet">Kode Outlet</label>
                                        <input type="text" class="form-control" id="kode_outlet"
                                            value="{{ $outlet->kode_outlet }}" name="kode_outlet">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="nama_outlet">Nama Outlet</label>
                                        <input type="text" class="form-control" id="nama_outlet"
                                            value="{{ $outlet->nama_outlet }}" name="nama_outlet">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="nama_pemilik_outlet">Nama Pemilik</label>
                                        <input type="text" class="form-control" id="nama_pemilik_outlet"
                                            value="{{ $outlet->nama_pemilik_outlet }}" name="nama_pemilik_outlet">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="nomer_tlp_outlet">Nomer Pemilik</label>
                                        <input type="text" class="form-control" id="nomer_tlp_outlet"
                                            value="{{ $outlet->nomer_tlp_outlet }}" name="nomer_tlp_outlet">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="alamat_outlet">Alamat Outlet</label>
                                        <input type="text" class="form-control" id="alamat_outlet"
                                            value="{{ $outlet->alamat_outlet }}" name="alamat_outlet">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="latlong">LatLong</label>
                                        <input type="text" class="form-control" id="latlong"
                                            value="{{ $outlet->latlong }}" name="latlong">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <label for="badanusaha_id" class="form-label col-lg-12">Badan Usaha</label>
                                        <select class="custom-select col-lg-12" id="badanusaha_id" name="badanusaha_id"
                                            required>
                                            @foreach ($badanusahas as $badanusaha)
                                                <option value="{{ $badanusaha->id }}"
                                                    {{ $badanusaha->id === $outlet->badanusaha_id ? 'selected' : '' }}>
                                                    {{ $badanusaha->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-4">
                                        <label for="divisi_id" class="form-label col-lg-12">Divisi</label>
                                        <select class="custom-select col-lg-12" id="divisi_id" name="divisi_id" required>
                                            @foreach ($divisis as $divisi)
                                                <option value="{{ $divisi->id }}"
                                                    {{ $divisi->id === $outlet->divisi_id ? 'selected' : '' }}>
                                                    {{ $divisi->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-lg-4">
                                        <label for="region_id" class="form-label col-lg-12">Region</label>
                                        <select class="custom-select col-lg-12" name="region_id" id="region_id" required>
                                            @foreach ($regions as $region)
                                                <option value="{{ $region->id }}"
                                                    {{ $region->id === $outlet->region_id ? 'selected' : '' }}>
                                                    {{ $region->name.' - '.$region->divisi->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-4">
                                        <label for="cluster_id" class="form-label col-lg-12">Cluster</label>
                                        <select class="custom-select col-lg-12" id="cluster_id" name="cluster_id" required>
                                            @foreach ($clusters as $cluster)
                                                <option value="{{ $cluster->id }}"
                                                    {{ $cluster->id === $outlet->cluster_id ? 'selected' : '' }}>
                                                    {{ $cluster->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-lg-4">
                                        <label for="radius">Radius</label>
                                        <input type="number" class="form-control" id="radius"
                                            value="{{ $outlet->radius }}" name="radius">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <label for="status_outlet" class="form-label col-lg-12">Status</label>
                                        <select class="custom-select col-lg-12" id="status_outlet" name="status_outlet">
                                            <option value="MAINTAIN"
                                                {{ $outlet->status_outlet === 'MAINTAIN' ? 'selected' : '' }}>
                                                MAINTAIN
                                            </option>
                                            <option value="UNMAINTAIN"
                                                {{ $outlet->status_outlet === 'UNMAINTAIN' ? 'selected' : '' }}>
                                                UNMAINTAIN
                                            </option>
                                            <option value="UNPRODUCTIVE"
                                                {{ $outlet->status_outlet === 'UNPRODUCTIVE' ? 'selected' : '' }}>
                                                UNPRODUCTIVE
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group col-lg-4">
                                        <label for="limit" class="form-label col-lg-12">Limit</label>
                                        <input type="number" class="form-control" id="limit"
                                            value="{{ $outlet->limit }}" name="limit">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
