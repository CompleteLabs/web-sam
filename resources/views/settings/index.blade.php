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
                                    @switch($title)
                                        @case('Role')
                                            <h3 class="card-title">Role Setting</h3>
                                            <button type="button" data-toggle="modal" data-target="#addRole"
                                                class="badge bg-success mx-3 elevation-0">Add</button>
                                        @break

                                        @case('Badan Usaha')
                                            <h3 class="card-title">Badan Usaha Setting</h3>
                                            <button type="button" data-toggle="modal" data-target="#addBu"
                                                class="badge bg-success mx-3 elevation-0">Add</button>
                                        @break

                                        @case('Divisi')
                                            <h3 class="card-title">Divisi Setting</h3>
                                            <button type="button" data-toggle="modal" data-target="#addDivisi"
                                                class="badge bg-success mx-3 elevation-0">Add</button>
                                        @break

                                        @case('Region')
                                            <h3 class="card-title">Region Setting</h3>
                                            <button type="button" data-toggle="modal" data-target="#addRegion"
                                                class="badge bg-success mx-3 elevation-0">Add</button>
                                        @break

                                        @default
                                            <h3 class="card-title">Cluster Setting</h3>
                                            <button type="button" data-toggle="modal" data-target="#addCluster"
                                                class="badge bg-success mx-3 elevation-0">Add</button>
                                    @endswitch
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
                            <!-- /.card-header -->
                            <div class="card-body table-responsive p-0" style="height: 500px;">
                                <table class="table table-head-fixed text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Name</th>
                                            @switch($title)
                                                @case('Divisi')
                                                    <th>Badan Usaha</th>
                                                @break

                                                @case('Region')
                                                    <th>Badan Usaha</th>
                                                    <th>Divisi</th>
                                                @break

                                                @case('Cluster')
                                                    <th>Badan Usaha</th>
                                                    <th>Divisi</th>
                                                    <th>Region</th>
                                                @break

                                                @default
                                            @endswitch
                                            <th>Edit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @switch($title)
                                            @case('Role')
                                                @foreach ($roles as $role)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $role->name }}</td>
                                                        <td>
                                                            <a href="/setting/role/{{ $role->id }}"
                                                                class="badge bg-warning"><span><i
                                                                        class="fas fa-edit"></i></span></a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @break

                                            @case('Badan Usaha')
                                                @foreach ($badanusahas as $badanusaha)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $badanusaha->name }}</td>
                                                        <td>
                                                            <a href="/setting/badanusaha/{{ $badanusaha->id }}"
                                                                class="badge bg-warning"><span><i
                                                                        class="fas fa-edit"></i></span></a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @break

                                            @case('Divisi')
                                                @foreach ($divisis as $divisi)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $divisi->name }}</td>
                                                        <td>{{ $divisi->badanusaha->name }}</td>
                                                        <td>
                                                            <a href="/setting/divisi/{{ $divisi->id }}"
                                                                class="badge bg-warning"><span><i
                                                                        class="fas fa-edit"></i></span></a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @break

                                            @case('Region')
                                                @foreach ($regions as $region)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $region->name }}</td>
                                                        <td>{{ $region->badanusaha->name }}</td>
                                                        <td>{{ $region->divisi->name }}</td>
                                                        <td>
                                                            <a href="/setting/region/{{ $region->id }}"
                                                                class="badge bg-warning"><span><i
                                                                        class="fas fa-edit"></i></span></a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @break

                                            @default
                                                @foreach ($clusters as $cluster)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $cluster->name }}</td>
                                                        <td>{{ $cluster->badanusaha->name }}</td>
                                                        <td>{{ $cluster->divisi->name }}</td>
                                                        <td>{{ $cluster->region->name }}</td>
                                                        <td>
                                                            <a href="/setting/cluster/{{ $cluster->id }}"
                                                                class="badge bg-warning"><span><i
                                                                        class="fas fa-edit"></i></span></a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                        @endswitch

                                    </tbody>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
    </section>
    <!-- /.content -->
    <!-- /.content-wrapper -->

    <!-- Modal Add Role-->
    <div class="modal fade" id="addRole" tabindex="-1" role="dialog" aria-labelledby="addRoleLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="/setting/role">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRoleLabel">Add Role</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 col-lg-12">
                            <label for="name" class="form-label">Nama Role</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Add Badan Usaha-->
    <div class="modal fade" id="addBu" tabindex="-1" role="dialog" aria-labelledby="addBuLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="/setting/badanusaha">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addBuLabel">Add Badan Usaha</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 col-lg-12">
                            <label for="name" class="form-label">Nama Badan Usaha</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Add Divisi-->
    <div class="modal fade" id="addDivisi" tabindex="-1" role="dialog" aria-labelledby="addDivisiLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="/setting/divisi">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addDivisiLabel">Add Divisi</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 col-lg-12">
                            <label for="name" class="form-label">Nama Divisi</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3 col-lg-12">
                            <label for="badanusaha_id" class="form-label">Badan Usaha</label>
                            <select class="custom-select" id="badanusaha_id" name="badanusaha_id" required>
                                @foreach ($badanusahas as $badanusaha)
                                    <option value="{{ $badanusaha->id }}">{{ $badanusaha->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Modal Add Region-->
    <div class="modal fade" id="addRegion" tabindex="-1" role="dialog" aria-labelledby="addRegionLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="/setting/region">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRegionLabel">Add Region</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 col-lg-12">
                            <label for="name" class="form-label">Nama Region</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3 col-lg-12">
                            <label for="badanusaha_id" class="form-label">Badan Usaha</label>
                            <select class="custom-select" id="badanusaha_id" name="badanusaha_id" required>
                                @foreach ($badanusahas as $badanusaha)
                                    <option value="{{ $badanusaha->id }}">{{ $badanusaha->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 col-lg-12">
                            <label for="divisi_id" class="form-label">Divisi</label>
                            <select class="custom-select" id="divisi_id" name="divisi_id" required>
                                @foreach ($divisis as $divisi)
                                    <option value="{{ $divisi->id }}">{{ $divisi->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Modal Add Cluster-->
    <div class="modal fade" id="addCluster" tabindex="-1" role="dialog" aria-labelledby="addClusterLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="/setting/cluster">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addClusterLabel">Add Cluster</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 col-lg-12">
                            <label for="name" class="form-label">Nama Cluster</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3 col-lg-12">
                            <label for="badanusaha_id" class="form-label">Badan Usaha</label>
                            <select class="custom-select" id="badanusaha_id" name="badanusaha_id" required>
                                @foreach ($badanusahas as $badanusaha)
                                    <option value="{{ $badanusaha->id }}">{{ $badanusaha->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 col-lg-12">
                            <label for="divisi_id" class="form-label">Divisi</label>
                            <select class="custom-select" id="divisi_id" name="divisi_id" required>
                                @foreach ($divisis as $divisi)
                                    <option value="{{ $divisi->id }}">{{ $divisi->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 col-lg-12">
                            <label for="region_id" class="form-label">Region</label>
                            <select class="custom-select" name="region_id" id="region_id" required>
                                @foreach ($regions as $region)
                                    <option value="{{ $region->id }}">
                                        {{ $region->name . '-' . $region->divisi->name . '-' . $region->badanusaha->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    {{-- <script>
        $('#badanusaha_id').change(function() {
            var badanusaha_id = $(this).val();
            if (badanusaha_id) {
                $.ajax({
                    type: "GET",

                    url: "/setting/getdivisi?badanusaha_id=" + badanusaha_id,
                    dataType: 'JSON',
                    success: function(res) {
                        if (res) {
                            $("#divisi_id").empty();
                            $("#cluster_id").empty();
                            $("#divisi_id").append('<option>---Pilih Region---</option>');
                            $("#cluster_id").append('<option>---Pilih Cluster---</option>');
                            $.each(res, function(nama, kode) {
                                $("#divisi_id").append('<option value="' + kode + '">' + nama +
                                    '</option>');
                            });
                        } else {
                            $("#divisi_id").empty();
                            $("#cluster_id").empty();
                        }
                    }
                });
            } else {
                $("#divisi_id").empty();
                $("#cluster_id").empty();
            }
        });

        $('#region_id').change(function(){
    var conceptName = $('#aioConceptName').find(":selected").text();
    var region_id = $(this).val();
    if(region_id){
        $.ajax({
           type:"GET",
           url:"/setting/getregion?badanusaha_id=" + badanusaha_id+"divisi_id="+,
           dataType: 'JSON',
           success:function(res){
               console.log(res);
            if(res){
                $("#cluster_id").empty();
                $("#cluster_id").append('<option>---Pilih Cluster---</option>');
                $.each(res,function(nama,kode){
                    $("#cluster_id").append('<option value="'+kode+'">'+nama+'</option>');
                });
            }else{
               $("#cluster_id").empty();
            }
           }
        });
    }else{
        $("#cluster_id").empty();
    }
   });
    </script> --}}
@endsection
