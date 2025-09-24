@extends('adminlte::page')

@section('title', 'Dashboard')


@section('content')
<div class="row">
  <div class="col-lg-12 margin-tb">
    <div class="pull-left">
      <h2>Users Management</h2>
    </div>
    <div class="pull-right">
      @can('user-create')
      <a class="btn btn-success" href="{{ route('users.create') }}"> Create New User</a>
    @endcan
    </div>
    <br>
  </div>
</div>

@if ($message = Session::get('success'))
  <div class="alert alert-success">
    <p>{{ $message }}</p>
  </div>
@endif

<form action="{{ route('users.search') }}" method="POST">
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="input-group">
              @csrf
                <input type="text" name="search" class="form-control" placeholder="Search"
                    value="{{ request()->input('search') }}">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-danger">Clear</a>
                </div>
            </div>
        </div>
    </div>
</form>

<table class="table table-bordered">
  <tr>
    <th>No</th>
    <th>Name</th>
    <th>Email</th>
    <th>Roles</th>
    <th width="280px">Action</th>
  </tr>
  @foreach ($data as $key => $user)
    <tr>
    <td>{{ ++$i }}</td>
    <td>{{ $user->name }}</td>
    <td>{{ $user->email }}</td>
    <td>
      @if(!empty($user->getRoleNames()))
      @foreach($user->getRoleNames() as $v)
      <label class="badge badge-success">{{ $v }}</label>
    @endforeach
    @endif
    </td>
    <td>
      <a class="btn btn-info" href="{{ route('users.show', Crypt::encrypt($user->id)) }}">Show</a>
      @can('user-edit')
      <a class="btn btn-primary" href="{{ route('users.edit', Crypt::encrypt($user->id)) }}">Edit</a>
    @endcan
      @can('user-delete')
      {!! Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id], 'style' => 'display:inline']) !!}
      {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
      {!! Form::close() !!}
    @endcan
    </td>
    </tr>
  @endforeach
</table>

{!! $data->render() !!}
@stop

@section('css')
<!-- <link rel="stylesheet" href="/css/admin_custom.css"> -->
@stop

@section('js')
<!-- <script>
  console.log('Hi!'); 
</script> -->
@stop
