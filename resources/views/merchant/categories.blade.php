@extends('adminlte::page')

@section('title', 'Dashboard')


@section('css')
<!-- <link rel="stylesheet" href="/css/admin_custom.css"> -->
@stop

@section('js')
<script>
    console.log('Hi!');
</script>
@stop


@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Manage Categories</h2>
        </div>
        <div class="pull-right">
            @can('merchant-create')
            <a class="btn btn-success" href="{{ route('merchant.categoriesCreate') }}"> Create New MCC</a>
            @endcan
        </div>
    </div>
</div>
<br>

@if ($message = Session::get('success'))
<div class="alert alert-success">
    <p>{{ $message }}</p>
</div>
@endif


<table class="table table-bordered">
    <tr>
        <th>No</th>
        <th>Code MCC</th>
        <th>Desc</th>
        <th>Action</th>
    </tr>

    @foreach ($mcc as $row)

    {{-- {{{dd($mcc)}}} --}}

    <tr>
        {{-- {{dd($merchant)}} --}}
        <td>{{ $row->ID }}</td>
        <td>{{ $row->CODE_MCC }}</td>
        <td>{{ $row->DESC_MCC }}</td>
        <td>
            {{-- <a class="btn btn-info" href="{{ route('merchant.show', Crypt::encrypt($row->ID)) }}">Detail</a> --}}

            @can('merchant-edit')
            <a class="btn btn-warning" href="{{ route('merchant.categoriesEdit',Crypt::encrypt($row->ID)) }}">Edit</a>
            @endcan

            {{-- <button class="btn btn-success" onclick="ceksaldo(`{{ Crypt::encrypt($row->ID) }}`)">Saldo</button> --}}
            {{-- <button class="btn btn-primary" onclick="cekmutasi(`{{ Crypt::encrypt($row->ID) }}`)">Mutasi</button> --}}
        </td>
    </tr>
    @endforeach
</table>


{{-- {!! $mcc->links() !!} --}}

<div class="modal" id="modalSaldo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content center">
            <div class="modal-header">
                <h5 class="modal-title">Cek Saldo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group col-12">
                    <label>Nomor Rekening</label>
                    <input type="text" class="form-control" name="norek" id="norek" required readonly>
                </div>
                <div class="form-group col-12">
                    <label>Nama Rekening</label>
                    <input type="text" class="form-control" name="name" id="name" required readonly>
                </div>
                <div class="form-group col-12">
                    <label>Saldo</label>
                    <input type="text" class="form-control" name="balance" id="balance" required readonly>
                </div>
                <br>
                <div>
                    <h5 id="errorResp"></h5>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modalMutasi" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content center">
            <div class="modal-header">
                <h5 class="modal-title">Mutasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <div id="table_mutasi"></div>
                </div>
            </div>
        </div>
    </div>
</div>



@endsection