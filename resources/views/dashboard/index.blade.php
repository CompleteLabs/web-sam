@extends('layout.main_tamplate')


@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <h5 class="my-2">Dashboard</h5>
            <div class="row">
            @if (Auth::user()->role_id === 5)
                <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
            <a href="/user">
                <div class="info-box-content">
                    <span class="info-box-text text-dark">User</span>
                    <span class="info-box-number text-dark">{{ $user }}</span>
                </div>
            </a>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <!-- /.col -->
        @endif
        <div class="col-md-3 col-sm-6 col-12">
          <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-store"></i></span>

            <a href="/outlet">
                <div class="info-box-content">
                    <span class="info-box-text text-dark">Outlet</span>
                    <span class="info-box-number text-dark">{{ $outlet }}</span>
                </div>
            </a>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-12">
          <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-store-alt"></i></span>

            <a href="/noo">
                <div class="info-box-content">
                    <span class="info-box-text text-dark">Noo</span>
                    <span class="info-box-number text-dark">{{ $noo }}</span>
                </div>
            </a>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <!-- /.col -->
        @if (Auth::user()->role_id === 5 || Auth::user()->role_id === 4)
        <div class="col-md-3 col-sm-6 col-12">
          <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-map-marker-alt"></i></span>

            <a href="/visit">
                <div class="info-box-content">
                    <span class="info-box-text text-dark">Visit</span>
                    <span class="info-box-number text-dark">{{ $visit }}</span>
                </div>
            </a>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        @endif
        @if (Auth::user()->role_id === 5)
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
              <span class="info-box-icon bg-danger"><i class="fas fa-map-marked"></i></span>
              <a href="/planvisit">
                <div class="info-box-content">
                    <span class="info-box-text text-dark">Plan Visit</span>
                    <span class="info-box-number text-dark">{{ $planvisit }}</span>
                </div>
            </a>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
        <!-- /.col -->
       </div>
      </div>
    @endif
  </section>

@endsection
