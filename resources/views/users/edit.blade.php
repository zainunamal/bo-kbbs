@extends('adminlte::page')

@section('title', 'Dashboard')


@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#role-select').change(function() {
                var role = $(this).val();
                if (role === 'KC' || role === 'KCP') {
                    $('#cabang').closest('.form-group').show();
                    $('#merchant-select').closest('.form-group').hide();
                } else if (role === 'Merchant') {
                    $('#cabang').closest('.form-group').hide();
                    $('#merchant-select').closest('.form-group').show();
                } else {
                    $('#cabang').closest('.form-group').hide();
                    $('#merchant-select').closest('.form-group').hide();
                }
            });
            // Trigger on load
            $('#role-select').trigger('change');

            $('#cabang').on('change', function() {
                var kodeCabang = $(this).val();
                var kodeLokasi = $('#cabang option:selected').data('lokasi');

                $('#kode_cabang').val(kodeCabang); // kalau pakai hidden cabang
                $('#kode_lokasi').val(kodeLokasi);
            });
        });
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>Edit User</h2>
            </div>
            <div class="pull-right">
                <a class="btn btn-primary" href="{{ route('users.index') }}"> Back</a>
            </div>
        </div>
    </div>


    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {!! Form::model($user, ['method' => 'PUT','route' => ['users.update', $user->id]]) !!}
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Name:</strong>
                {!! Form::text('name', null, ['placeholder' => 'Name', 'class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Email:</strong>
                {!! Form::text('email', null, ['placeholder' => 'Email', 'class' => 'form-control', 'readonly' => 'readonly']) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Password:</strong>
                {!! Form::password('password', ['placeholder' => 'Password', 'class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Confirm Password:</strong>
                {!! Form::password('confirm-password', ['placeholder' => 'Confirm Password', 'class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Role:</strong>
                {!! Form::select('roles', $roles, $userRole, ['class' => 'form-control', 'id' => 'role-select']) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group" style="display: none;">
                <label>Cabang</label>
                <select class="form-control" name="cabang" id="cabang">
                    <!-- Opsi default -->
                    <option value="">-- Pilih Cabang --</option>

                    @foreach ($cabangs as $cabang)
                        <option value="{{ $cabang->CPC_MC_KODE_CABANG }}" data-lokasi="{{ $cabang->CPC_MC_KODE_LOKASI }}" {{ ($userCabang->CPC_MC_KODE_LOKASI == $cabang->CPC_MC_KODE_LOKASI && $userCabang->CPC_MC_KODE_CABANG == $cabang->CPC_MC_KODE_CABANG) ? 'selected' : '' }}>
                            {{ $cabang->CPC_MC_NAMA }}
                        </option>
                    @endforeach
                </select>
                {{-- Hidden field --}}
                <input type="hidden" name="kode_lokasi" id="kode_lokasi" value="{{ $userCabang ? $userCabang->pivot->KODE_LOKASI : '' }}">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group" style="display: none;">
                <strong>Merchant:</strong>
                {!! Form::select('merchants', $merchants, $userMerchant, [
                    'class' => 'form-control',
                    'id' => 'merchant-select',
                    'placeholder' => '-- Pilih Merchant --',
                ]) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </div>
    {!! Form::close() !!}
@endsection
