@extends('adminlte::page')

@section('title', 'Dashboard')


@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
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
                <h2>Edit MCC </h2>
            </div>
            <div class="pull-right">

            </div>
        </div>
    </div>


    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif



    <form action="{{ route('merchant.categoriesUpdate', $mcc->ID) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">

                    <input type="text" class="form-control" name="merchantType" id="merchantType" value="addMcc" hidden >
                    <input type="text" class="form-control" name="ID" id="ID" value="{{ $mcc->ID }}" hidden >
                 
                    <strong>Code MCC:</strong>
                    <input type="number" name="CODE_MCC" value="{{ $mcc->CODE_MCC }}" class="form-control col-3">
                    <strong>Desc MCC:</strong>
                    <input type="text" name="DESC_MCC" value="{{ $mcc->DESC_MCC }}"
                        class="form-control col-3">
                   
                </div>
                <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                    <a class="btn btn-primary" href="{{ route('merchant.index') }}"> Cancel </a>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>

            </div>

        </div>


    </form>



@endsection
