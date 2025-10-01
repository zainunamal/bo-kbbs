@extends('adminlte::page')

@section('title', 'Dashboard')

@section('css')
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"> -->
@stop

@section('js')
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script> -->
@stop

@section('content')
<!-- Script -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<style type="text/css">
    .left {
        text-align: left;
    }

    .right {
        text-align: right;
    }

    .center {
        text-align: center;
    }

    .justify {
        text-align: justify;
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Generate QRIS </h1>
            </div>
        </div>
    </div>
</section>

@if (!empty(Session::get('error_code')) && Session::get('error_code') == 5)
    <script>
        $(function () {
            $('#modalQr').modal('modalQr');
        });
    </script>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card card-default">
            <form action="{{ route('qris.hit') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <input type="hidden" id="rawAmount" name="rawAmount">

                        <div class="form-group col-6">
                            <label>QR Type :</label>
                            <select class="form-control select" name="TYPE" id="TYPE" required onchange="checkQRType()">
                                <option value="DINAMIS">DINAMIS</option>
                                {{-- <option value="STATIS">STATIS</option> --}}
                            </select>
                        </div>
                        <div class="form-group col-6">
                            <label>Merchant Code:</label>
                            <select class="form-control select2" name="MERCHANT_ID" id="MERCHANT_ID" required
                                onchange="checkMerchantType()">
                                @foreach (array_reverse($merchant) as $dropdown)
                                    <option value="{{ $dropdown->ID }}" data-qr-type="{{ $dropdown->QR_TYPE }}">
                                        {{ $dropdown->MERCHANT_NAME }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-6">
                            <label>AMOUNT:</label>
                            <input type="text" id="AMOUNT" name="AMOUNT" class="form-control" placeholder="Min 10.000"
                                oninput="formatAmount(this)">
                        </div>
                        <div class="form-group col-6">
                            <label>VA:</label>
                            <input type="number" id="VA" name="VA" class="form-control" placeholder="   ">
                        </div>
                        <div class="form-group col-6">
                            <label>TIP_INDICATOR:</label>
                            <input type="number" id="TIP_INDICATOR" class="form-control">
                        </div>
                        <div class="form-group col-6">
                            <label>FEE_AMOUNT:</label>
                            <input type="number" id="FEE_AMOUNT" class="form-control">
                        </div>
                        <div class="form-group col-6">
                            <label>FEE_AMOUNT_PERCENTAGE: (%) </label>
                            <input type="number" id="FEE_AMOUNT_PERCENTAGE" class="form-control">
                        </div>
                    </div>
                </div>
                {{-- <div
                    class=" col-xs-12 col-sm-12 col-md-12 text-center card-footer d-flex justify-content-between align-items-center  ">
                    --}}
                    <div class="col-xs-12 col-sm-12 col-md-12 text-center card-footer d-flex justify-content-between">
                        <!-- Tombol Submit -->
                        <button type="button" class="btn btn-success" id="store">Submit</button>
                        <small id="submitHint" style="display: none; color: #6c757d;"
                            class="form-text text-muted"></small>
                        <!-- Menggunakan tag <small> dan class text-muted untuk keterangan -->

                        {{-- <button type="submit" class="btn btn-success" id="store">sumbit</button> --}}


                        <div id="loading" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>

            </form>
        </div>
    </div>
</div>


<!-- tambahkan elemen untuk animasi loading -->
<div class="modal" id="modalQr" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content center">
            <div class="modal-header">
                <h5 class="modal-title">Generate QR </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="previewQr"></div>
                <br>
                <div>
                    <h5 id="errorResp"> </h5>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btnDownload">Unduh</button>
            </div>
        </div>
    </div>
</div>


{{-- <div class="col-md-12">
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">QR STATIS</h3>
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
            <p>QRIS Statis tidak tersedia.</p>
            @endif
        </div>
    </div>
</div> --}}




<script>
    //button create post event
    $(document).ready(function () {
        $('.select2').select2();
    });

    //disable amount if dinamis qr selected
    function checkQRType() {
        var qrType = document.getElementById("TYPE").value;
        var amountInput = document.getElementById("AMOUNT");

        if (qrType === "STATIS") {
            amountInput.disabled = true;
        } else {
            amountInput.disabled = false;
        }
    }

    function formatAmount(input) {
        let value = input.value.replace(/[^0-9]/g, ''); // Remove non-numeric characters

        if (value.length > 0) {
            // Format the number with thousand separators
            input.value = parseInt(value, 10).toLocaleString(
                'id-ID'); // 'id-ID' for Indonesia (dot as thousand separator)
        }
    }




    //action create post
    $('#store').click(function (e) {
        e.preventDefault();

        // Tampilkan animasi loading
        $('#loading').show();

        // Nonaktifkan tombol submit
        $(this).prop('disabled', true);

        let amountValue = $('#AMOUNT').val().replace(/[^0-9]/g, '');
        $('#rawAmount').val(amountValue);
        // $('#AMOUNT').val(amountValue);

        let qrType = $('#qrType').val();
        let MERCHANT_ID = $('#MERCHANT_ID').val();
        let AMOUNT = $('#rawAmount').val();
        let VA = $('#VA').val();
        let TIP_INDICATOR = $('#TIP_INDICATOR').val();
        let FEE_AMOUNT = $('#FEE_AMOUNT').val();
        let FEE_AMOUNT_PERCENTAGE = $('#FEE_AMOUNT_PERCENTAGE').val();
        let TYPE = $('#TYPE').val();
        let token = $("meta[name='csrf-token']").attr("content");

        $.ajax({
            url: `qris/hit`,
            type: "POST",
            cache: false,
            data: {
                "qrType": qrType,
                "MERCHANT_ID": MERCHANT_ID,
                "AMOUNT": AMOUNT,
                "VA": VA,
                "TIP_INDICATOR": TIP_INDICATOR,
                "FEE_AMOUNT": FEE_AMOUNT,
                "FEE_AMOUNT_PERCENTAGE": FEE_AMOUNT_PERCENTAGE,
                "TYPE": TYPE,
                "_token": token
            },
            success: function (response) {
                $("#modalQr").modal('show');
                $('#previewQr').html(`<img src="data:image/png;base64,` + response.qr + `" \>`);
                $('#errorResp').html(JSON.stringify(response.error));

                // Sembunyikan animasi loading
                $('#loading').hide();

                // Aktifkan kembali tombol submit
                $('#store').prop('disabled', false);
            },
            error: function (error) {
                $("#modalQr").modal('show');
                // Sembunyikan animasi loading
                $('#loading').hide();

                // Aktifkan kembali tombol submit
                $('#store').prop('disabled', false);
            }
        });
    });


    // Tindakan saat tombol Simpan diklik
    $('#btnSave').click(function () {
        // Tambahkan kode untuk menyimpan QR di sini
        alert('QR berhasil disimpan.');
    });

    // Tindakan saat tombol Unduh diklik
    $('#btnDownload').click(function () {
        // Mendapatkan gambar QR dari elemen img di dalam modal
        var qrImage = $('#previewQr img').attr('src');

        // Membuat elemen <a> untuk mengunduh gambar
        var downloadLink = document.createElement('a');
        downloadLink.href = qrImage;
        downloadLink.download = 'QR_Code.png';

        // Menambahkan elemen <a> ke dalam dokumen dan mengkliknya secara otomatis
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);

        alert('QR berhasil diunduh.');
    });
</script>


@endsection
<script>
    function checkMerchantType() {
        const merchantSelect = document.getElementById('MERCHANT_ID');
        const selectedOption = merchantSelect.options[merchantSelect.selectedIndex];
        const qrType = selectedOption.getAttribute('data-qr-type');
        const submitButton = document.getElementById('store');
        const submitHint = document.getElementById('submitHint');

        if (qrType === 'S') {
            submitButton.disabled = true;
            submitHint.style.display = 'block'; // Menampilkan keterangan
            submitHint.textContent = 'Submit tidak tersedia untuk QR Statik.'; // Pesan ketika dinonaktifkan
        } else {
            submitButton.disabled = false;
            submitHint.style.display = 'none'; // Menyembunyikan keterangan
            submitHint.textContent = ''; // Mengosongkan teks
        }
    }

    $(document).ready(function () {
        $('.select2').select2();
        checkMerchantType(); // Memastikan status tombol dan keterangan sesuai saat halaman dimuat
    });
</script>