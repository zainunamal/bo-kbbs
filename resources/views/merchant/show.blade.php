@extends('adminlte::page')

@section('title', 'Detail Merchant')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Merchant Details</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="col-md-12">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">QR Code</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-12 text-center">
                        @if ($imageExists)
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#qrCodeModal">
                                Tampilkan QR Code
                            </button>
                            <a href="{{ asset($imagePath) }}" class="btn btn-success" download>Download QR Code</a>

                            <div class="modal fade" id="qrCodeModal" tabindex="-1" role="dialog"
                                aria-labelledby="qrCodeModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lgheader">
                                    <h5 class="modal-title" id="qrCodeModalLabel">QR Code</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>

                                    <img src="{{ asset($imagePath) }}" alt="QR Code" style="max-width: 100%;">
                                </div>
                            </div>
                    </div>
                </div>
            @else
                <p>QR Code tidak tersedia.</p>
                @endif
            </div>
        </div>
    </div>


    <div class="row">
        {{-- Card untuk data $merchant --}}
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Data Merchant</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach ($merchant->toArray() as $key => $value)
                            @if ($key != 'TYPE_QR' && $key != 'ID')
                                <div class="form-group col-6">
                                    <label>{{ $key }}</label>
                                    <input type="text" class="form-control" value="{{ $value }}" readonly>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Card untuk data $merchant_details --}}
        <div class="col-md-12">
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Merchant Details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach ($merchant_detail->toArray() as $key => $value)
                            <div class="form-group col-6">
                                <label>{{ $key }}</label>
                                <input type="text" class="form-control" value="{{ $value }}" readonly>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Card untuk data $merchant_domestic --}}
        <div class="col-md-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Merchant Domestic</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach ($merchant_domestic->toArray() as $key => $value)
                            <div class="form-group col-6">
                                <label>{{ $key }}</label>
                                <input type="text" class="form-control" value="{{ $value }}" readonly>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<script>
    function toggleQrCode() {
        var qrCodeContainer = document.getElementById('qrCodeContainer');
        if (qrCodeContainer.style.display === 'none') {
            qrCodeContainer.style.display = 'block';
        } else {
            qrCodeContainer.style.display = 'none';
        }
    }
</script>
