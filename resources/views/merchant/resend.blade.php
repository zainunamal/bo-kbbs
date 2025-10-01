@extends('adminlte::page')

@section('title', 'Dashboard')


@section('css')
<!-- <link rel="stylesheet" href="/css/admin_custom.css"> -->
@stop

@section('js')
<!-- <script>
        console.log('Hi!');
    </script> -->
@stop

@section('content')
<div class="row mb-3">
    <div class="col-lg-10">
        <h2>Resend Email</h2>
    </div>
</div>

@if ($message = Session::get('success'))
    <div class="alert alert-success">
        <p>{{ $message }}</p>
    </div>
@endif

@error('msg')
    <div class="alert alert-danger">
        <p>{{ $message }}</p>
    </div>
@endif

<form action="{{ route('merchant.resend') }}" method="GET">
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search Merchant"
                    value="{{ request()->input('search') }}">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                    <a href="{{ route('merchant.resend') }}" class="btn btn-outline-danger">Clear</a>
                </div>
            </div>
        </div>
    </div>
</form>

<table class="table table-bordered">
    <tr>
        <th width="10%">Merchant ID</th>
        <th>Merchant Name</th>
        <th>NMID</th>
        <th>Action</th>
    </tr>

    @foreach ($merchants as $row)
        <tr>
            <td>{{ $row->ID }}</td>
            <td>{{ $row->MERCHANT_NAME }}</td>
            <td>{{ $row->NMID }}</td>
            <td>
                @can('resend-email')
                    <a class="btn btn-info" href="{{ route('merchant.resend.email', Crypt::encrypt($row->NMID)) }}">Resend</a>
                @endcan
            </td>
        </tr>
    @endforeach
</table>

{!! $merchants->links() !!}

<script>
    function showQRModal(imageUrl) {
        var image = new Image();
        image.onload = function () {
            // Jika gambar berhasil dimuat
            $('#qrImage').attr('src', imageUrl).show();
            $('#qrMessage').hide(); // Sembunyikan pesan error jika ada
            $('#qrCodeModal').modal('show');
        };
        image.onerror = function () {
            // Jika gambar tidak bisa dimuat
            $('#qrImage').hide(); // Sembunyikan gambar
            $('#qrMessage').text('No QR Code available.').show(); // Tampilkan pesan error
            $('#qrCodeModal').modal('show');
        };
        image.src = imageUrl; // Trigger pemuatan gambar
    }

    //action cek
    function ceksaldo(id) {
        // define variable
        let token = $("meta[name='csrf-token']").attr("content");

        //ajax
        $.ajax({
            url: `merchant/saldo`,
            type: "POST",
            cache: false,
            data: {
                "id": id,
                "_token": token
            },
            success: function (response) {
                $("#modalSaldo").modal('show');
                $('#norek').val(response.norek);
                $('#name').val(response.name);
                $('#balance').val(response.balance);
            },
            error: function (error) {
                $("#modalMutasi").modal('show');
            }
        });
    };

    function cekmutasi(id) {
        // define variable
        let token = $("meta[name='csrf-token']").attr("content");

        //ajax
        $.ajax({
            url: `merchant/mutasi`,
            type: "POST",
            cache: false,
            data: {
                "id": id,
                "_token": token
            },
            success: function (response) {
                $("#modalMutasi").modal('show');
                body = '';
                for (let index = 0; index < response.length; index++) {
                    const element = JSON.parse(response[index]);

                    // Format the amount with thousand separator
                    let formattedAmount = parseFloat(element.AMT).toLocaleString(
                        'id-ID'); // for Indonesian format, change if needed

                    body += '<tr>\
                        <td>' + element.TIME + '</td>\
                        <td>' + formattedAmount + '</td>\
                        <td>' + element.DESC + '</td>\
                        <td>' + element.MOD + '</td>\
                        <td>' + element.TYPE + '</td>\
                        <td>' + element.REF + '</td>\
                    </tr>';
                }
                html = document.getElementById("table_mutasi");
                html.innerHTML = '<table class="table" width="100%">\
                <thead>\
                    <tr>\
                        <th>Date</th>\
                        <th>Amount</th>\
                        <th>Desc</th>\
                        <th>Trx</th>\
                        <th>Type</th>\
                        <th>Refnum</th>\
                    </tr>\
                </thead>\
                <tbody>' + body + '</tbody></table>';
            },
            error: function (error) {
                // $("#modalSaldo").modal('show');
            }
        });
    };
</script>

@endsection