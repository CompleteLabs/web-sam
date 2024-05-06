@extends('layout.main_tamplate')
@section('content')
    <section class="content-header">
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-dark">
                                <div class="row d-inline-flex">
                                    @php
                                        // Mendapatkan tanggal hari ini
                                        $today = date('Y-m-d');
                                        // Menggunakan strtotime untuk mendapatkan tanggal bulan kemarin
                                        $lastMonth = date('Y-m-d', strtotime('last month', strtotime($today)));
                                        // Mendapatkan nama bulan dalam format "April" untuk bulan kemarin
                                        $lastMonthName = date('F', strtotime($lastMonth));
                                    @endphp
                                    <h3 class="card-title">
                                        Noo {{ $monthName ?? $lastMonthName }} - {{ $noomounth }}
                                        ({{ $avgnooday }}/Hari)
                                    </h3>
                                </div>
                                <div class="card-tools">
                                    <div class="input-group input-group-sm">
                                        <form class="form-inline" action="/dashboard">
                                            <input type="month" class="form-control" id="daterangesearch"
                                                name="daterangesearch">
                                        </form>
                                    </div>
                                </div>

                            </div>
                            @if ($message = Session::get('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @endif
                            @if ($message = Session::get('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @endif
                        </div>
                        <div class="card-body table-responsive p-0" style="height: 500px;">
                            <table class="table table-head-fixed text-nowrap">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Dibuat oleh</th>
                                        <th>Jumlah Noo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($datanoomounth as $data)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $data->created_by }}</td>
                                            <td>{{ $data->total }}
                                                ({{ ceil(($data->total / $jumlahhari) * 100) / 100 }}/hari)
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </section>
    </section>
    <!-- Main content -->
@endsection
