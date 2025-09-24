@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content')
<section class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1>Add New Merchant</h1>
			</div>
		</div>
	</div>
</section>

@if ($errors->any())
<div class="alert alert-danger">
	<label>Whoops!</label> There were some problems with your input.<br><br>
	<ul>
		@foreach ($errors->all() as $error)
		<li>{{ $error }}</li>
		@endforeach
	</ul>
</div>
@endif

<div class="row">
	<div class="col-md-12">
		<div class="card card-default">
			<form action="{{ route('merchant.store') }}" method="POST">
				@csrf
				<div class="card-body">
					<div class="row">
						<div class="form-group col-6">
							<input type="text" class="form-control" name="merchantType" id="merchantType" value="addMcc" hidden >
							<label>Code MCC</label>
							<input type="number" class="form-control" name="code" id="code"  required>
						</div>
						<div class="form-group col-6">
							<label>Deskripsi MCC</label>
							<input type="text" class="form-control" name="desc" id="desc"  required>
						</div>
						
						
						
					</div>
				</div>
				<div class="card-footer">
					<a class="btn btn-info" href="{{ route('merchant.index') }}">Back</a>
					<button type="submit" class="btn btn-success">Submit</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection
