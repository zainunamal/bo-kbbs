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
    <div class="row mb-3">
        <div class="col-lg-10">
            <h2>Merchants</h2>
        </div>
        <div class="col-lg-10">
            @can('merchant-create')
                <a class="btn btn-success" href="{{ route('merchant.create') }}">Create New Merchant</a>
            @endcan
            @can('merchant-export')
                <a href="{{ route('merchants.export') }}" class="btn btn-info">Export to Excel</a>
            @endcan
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif

    <form action="{{ route('merchant.index') }}" method="GET">
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search Merchant"
                        value="{{ request()->input('search') }}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                        <a href="{{ route('merchant.index') }}" class="btn btn-outline-danger">Clear</a>
                    </div>
                </div>
            </div>
        </div>
    </form>






    <table class="table table-bordered">
        <tr>
            <th>Merchant ID</th>
            <th>Merchant Name</th>
            <th>Merchant City</th>
            <th>Action</th>
            <th>QRIS (Static)</th> <!-- Kolom baru untuk QR Code -->
            @if ($user->hasRole(['Admin', 'Superadmin']))
                <th>SEND EMAIL STATUS</th>
            @endif
        </tr>

        @foreach ($merchants as $row)
            <tr>
                {{-- {{dd($merchant)}} --}}
                <td>{{ $row->ID }}</td>
                <td>{{ $row->MERCHANT_NAME }}</td>
                <td>{{ $row->MERCHANT_CITY }}</td>
                <td>
                    <a class="btn btn-info" href="{{ route('merchant.show', Crypt::encrypt($row->ID)) }}">Detail</a>

                    @can('merchant-edit')
                        <a class="btn btn-warning" href="{{ route('merchant.edit', Crypt::encrypt($row->ID)) }}">Edit</a>
                    @endcan

                    <button class="btn btn-success" onclick="ceksaldo(`{{ Crypt::encrypt($row->ID) }}`)">Saldo</button>
                    <button class="btn btn-primary" onclick="cekmutasi(`{{ Crypt::encrypt($row->ID) }}`)">Mutasi</button>
                    @can('merchant-delete')
                        <a class="btn btn-danger" href="{{ route('merchant.delete', Crypt::encrypt($row->ID)) }}">Delete</a>
                    @endcan
                </td>
                <td>
                    <button class="btn btn-secondary"
                        onclick="showQRModal('{{ asset("data_pten/{$row->NMID}_A01.png") }}')">Show QR</button>
                </td>
                @if ($user->hasRole(['Admin', 'Superadmin']))
                    <td>
                        @if ($row->EMAIL_STATUS == 0)
                            <span class="text-warning">Waiting Approval PTEN</span>
                        @elseif ($row->EMAIL_STATUS == 1)
                            <span class="text-success">Success</span>
                        @elseif ($row->EMAIL_STATUS == 2)
                            <span class="text-danger">Failed</span>
                        @endif
                    </td>
                @endif


            </tr>
        @endforeach
    </table>


    {!! $merchants->links() !!}

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

    <div class="modal fade" id="qrCodeModal" tabindex="-1" role="dialog" aria-labelledby="qrCodeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrCodeModalLabel">Merchant QR Code</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img id="qrImage" src="" alt="QR Code" class="img-fluid" style="display: none;">
                    <!-- Awalnya sembunyikan -->
                    <p id="qrMessage" class="alert alert-warning" style="display: none;"></p>
                    <!-- Pesan untuk tidak ada QR -->
                </div>
            </div>
        </div>
    </div>


    <script>
        function showQRModal(imageUrl) {
            var image = new Image();
            image.onload = function() {
                // Jika gambar berhasil dimuat
                $('#qrImage').attr('src', imageUrl).show();
                $('#qrMessage').hide(); // Sembunyikan pesan error jika ada
                $('#qrCodeModal').modal('show');
            };
            image.onerror = function() {
                // Jika gambar tidak bisa dimuat
                $('#qrImage').hide(); // Sembunyikan gambar
                $('#qrMessage').text('No QR Code available.').show(); // Tampilkan pesan error
                $('#qrCodeModal').modal('show');
            };
            image.src = imageUrl; // Trigger pemuatan gambar
        }
    </script>



    <script>
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
                success: function(response) {
                    $("#modalSaldo").modal('show');
                    $('#norek').val(response.norek);
                    $('#name').val(response.name);
                    $('#balance').val(response.balance);
                },
                error: function(error) {
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
                success: function(response) {
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
                error: function(error) {
                    // $("#modalSaldo").modal('show');
                }
            });
        };
    </script>

    <style>
        .text-success {
            color: green;
        }

        .text-danger {
            color: red;
        }
    </style>

@endsection
