@extends('layout.main_tamplate')

@section('content')
    <section class="content-header">
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <!-- ./col -->
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-store"></i>
                                    Detail Outlet
                                </h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-3">Nama Outlet</dt>
                                    <dd class="col-sm-9">{{ $outlet->nama_outlet }}</dd>
                                    <dt class="col-sm-3">Alamat</dt>
                                    <dd class="col-sm-9">{{ $outlet->alamat_outlet }}</dd>
                                    <dt class="col-sm-3">Nama Pemilik</dt>
                                    <dd class="col-sm-9">{{ $outlet->nama_pemilik_outlet }}</dd>
                                    <dt class="col-sm-3">Nomer Telepon</dt>
                                    <dd class="col-sm-9">{{ $outlet->nomer_tlp_outlet }}</dd>
                                    <dt class="col-sm-3">Region</dt>
                                    <dd class="col-sm-9">{{ $outlet->region }}</dd>
                                    <dt class="col-sm-3">Cluster</dt>
                                    <dd class="col-sm-9">{{ $outlet->cluster->name }}</dd>
                                    <dt class="col-sm-3">Radius</dt>
                                    <dd class="col-sm-9">{{ $outlet->radius }}</dd>
                                    <dt class="col-sm-3">Lokasi</dt>
                                    <dd class="col-sm-9"><a target="_blank"
                                            href="http://www.google.com/maps/place/{{ $outlet->latlong }}">Lihat Lokasi</a>
                                    </dd>
                                    <dt class="col-sm-3">Status</dt>
                                    <dd class="col-sm-9">{{ $outlet->status_outlet }}</dd>
                                </dl>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- ./col -->
                </div>
            </div><!-- /.container-fluid -->
        </section>
    </section>
    <!-- /.content -->
    <!-- /.content-wrapper -->
@endsection
