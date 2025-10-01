@extends('adminlte::page')

@section('title', 'Dashboard')

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
@stop

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Transaction Report</h1>
                </div>
            </div>
        </div>
    </section>

    <!-- Filter and Export Buttons -->
    <div class="row mb-4">
        <div class="col-md-4">
            <input type="text" id="start-date" class="form-control" placeholder="Start Date">
        </div>
        <div class="col-md-4">
            <input type="text" id="end-date" class="form-control" placeholder="End Date">
        </div>
        {{-- <div class="col-md-4 text-right">
            <button id="exportButton" class="btn btn-success">Export Data</button>
        </div> --}}
    </div>

    <!-- modal detail -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Detail Transaksi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group col-12">
                        <label>Status Transaksi</label>
                        <input type="text" class="form-control" name="MERCHANT_ACC_NUMBER" id="MERCHANT_ACC_NUMBER"
                            required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Nama Accquier</label>
                        <input type="text" class="form-control" name="TIP_INDICATOR" id="TIP_INDICATOR" required
                            readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Rekening Sumber</label>
                        <input type="text" class="form-control" name="ACCOUNT_NUMBER" id="ACCOUNT_NUMBER" required
                            readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Nama Merchant </label>
                        <input type="text" class="form-control" name="MERCHANT_NAME" id="MERCHANT_NAME" required
                            readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Alamat Merchant </label>
                        <input type="text" class="form-control" name="MERCHANT_ADDRESS" id="MERCHANT_ADDRESS" required
                            readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Kode Pos </label>
                        <input type="text" class="form-control" name="POSTAL_CODE" id="POSTAL_CODE" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Kode Negara </label>
                        <input type="text" class="form-control" name="MERCHANT_CURRENCY_CODE" id="MERCHANT_CURRENCY_CODE"
                            required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Merchant PAN </label>
                        <input type="text" class="form-control" name="balance" id="balance" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Terminal ID / Merchant ID </label>
                        <input type="text" class="form-control" name="MERCHANT_ID" id="MERCHANT_ID" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Costumer PAN </label>
                        <input type="text" class="form-control" name="balance" id="balance" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label> Tipe Transaksi </label>
                        <input type="text" class="form-control" name="balance" id="balance" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Retrieval Reference Number </label>
                        <input type="text" class="form-control" name="RETRIEVAL_REFERENCE_NUMBER"
                            id="RETRIEVAL_REFERENCE_NUMBER" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Nomor Invoice </label>
                        <input type="text" class="form-control" name="INVOICE_NUMBER" id="INVOICE_NUMBER" required
                            readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Nominal Transaksi </label>
                        <input type="text" class="form-control" name="AMOUNT" id="AMOUNT" required readonly>
                    </div>

                </div>

            </div>
        </div>
    </div>
    <!-- modal detail -->

    <!-- modal refund -->

    <div class="modal" id="refundModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Detail Refund</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group col-12">
                        <label>Waktu Transaksi</label>
                        <input type="text" class="form-control" name="bit_12_RF" id="bit_12_RF" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Status Transaksi</label>
                        <input type="text" class="form-control" name="STATUS_TRANSACTION_RF"
                            id="STATUS_TRANSACTION_RF" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Accquiring Institution Name</label>
                        <input type="text" class="form-control" name="" id="" value="Bank KBBS"
                            required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Merchant PAN</label>
                        <input type="text" class="form-control" name="MPAN_RF" id="MPAN_RF" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Issuing Institution Name </label>
                        <input type="text" class="form-control" name="ISSUING_INSTITUTION_NAME"
                            id="ISSUING_INSTITUTION_NAME" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Issuing Costumer Name </label>
                        <input type="text" class="form-control" name="ISSUING_CUSTOMER_NAME"
                            id="ISSUING_CUSTOMER_NAME" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Costumer PAN</label>
                        <input type="text" class="form-control" name="CUSTOMER_PAN" id="CUSTOMER_PAN" required
                            readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>RRN Refund</label>
                        <input type="text" class="form-control" name="RETRIEVAL_REFERENCE_NUMBER_RF"
                            id="RETRIEVAL_REFERENCE_NUMBER_RF" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Refund Amount </label>
                        <input type="text" class="form-control" name="CURRENT_AMOUNT_REFUND_RF"
                            id="CURRENT_AMOUNT_REFUND_RF" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Convinience fee/tips </label>
                        <input type="text" class="form-control" name="FEE_AMOUNT_RF" id="FEE_AMOUNT_RF" required
                            readonly>
                    </div>
                    <div class="form-group col-12">
                        <label> Transaction Type </label>
                        <input type="text" class="form-control" name="TRANSACTION_TYPE_RF" id="TRANSACTION_TYPE_RF"
                            required readonly>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- modal refund -->
    <!-- modal succes trx -->

    <div class="modal" id="trxModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Detail Transaksi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group col-12">
                        <label>Waktu Transaksi</label>
                        <input type="text" class="form-control" name="bit_12_TRX" id="bit_12_TRX" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Status Transaksi</label>
                        <input type="text" class="form-control" name="STATUS_TRANSACTION_TRX"
                            id="STATUS_TRANSACTION_TRX" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Accquiring Institution Name</label>
                        <input type="text" class="form-control" name="" id="" required
                            value="Bank KBBS" readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Merchant PAN</label>
                        <input type="text" class="form-control" name="MPAN_TRX" id="MPAN_TRX" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Issuing Institution Name </label>
                        <input type="text" class="form-control" name="ISSUING_INSTITUTION_NAME_TRX"
                            id="ISSUING_INSTITUTION_NAME_TRX" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Issuing Costumer Name </label>
                        <input type="text" class="form-control" name="ISSUING_CUSTOMER_NAME_TRX"
                            id="ISSUING_CUSTOMER_NAME_TRX" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Costumer PAN</label>
                        <input type="text" class="form-control" name="CUSTOMER_PAN_TRX" id="CUSTOMER_PAN_TRX"
                            required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>RRN</label>
                        <input type="text" class="form-control" name="RETRIEVAL_REFERENCE_NUMBER_TRX"
                            id="RETRIEVAL_REFERENCE_NUMBER_TRX" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Payment Amount </label>
                        <input type="text" class="form-control" name="AMOUNT_TRX" id="AMOUNT_TRX" required readonly>
                    </div>
                    <div class="form-group col-12">
                        <label>Convinience fee/tips </label>
                        <input type="text" class="form-control" name="FEE_AMOUNT_TRX" id="FEE_AMOUNT_TRX" required
                            readonly>
                    </div>
                    <div class="form-group col-12">
                        <label> Transaction Type </label>
                        <input type="text" class="form-control" name="TRANSACTION_TYPE_TRX" id="TRANSACTION_TYPE_TRX"
                            required readonly>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <div class="card-body">
        <table id="tbl_list" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>NO</th>
                    {{-- <th>TRANSACTION_ID</th> --}}
                    <th>MERCHANT NAME</th>
                    {{-- <th>EXPIRE_DATE_TIME</th> --}}
                    <th>PAID DATE</th>
                    {{-- <th>FEE_AMOUNT</th> --}}
                    {{-- <th>STATUS_TRANSFER</th> --}}
                    <th>RRN</th>
                    <th>AMOUNT</th>
                    <th>AMOUNT_REFUND</th>
                    <th>AMOUNT_MDR</th>
                    <th>QR Type</th>
                    <th>ACQUIRING_INSTITUTION_NAME</th>
                    <th>ISSUING_CUSTOMER_NAME</th>
                    {{-- <th>SHOW</th> --}}
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
@endsection

@section('js')


    {{-- <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script> --}}
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        var startDateInput = document.getElementById('start-date');
        var endDateInput = document.getElementById('end-date');

        // Mengatur tanggal hari ini sebagai nilai default
        var today = new Date().toISOString().slice(0, 10);
        startDateInput.value = today;
        endDateInput.value = today;
    </script>


    <script type="text/javascript">
        $(document).ready(function() {
            var table = $('#tbl_list').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: "{{ route('transactions.data') }}",
                    data: function(d) {
                        d.start_date = $('#start-date').val();
                        d.end_date = $('#end-date').val();
                    }
                },
                dom: 'Bfrtip', // Needed to show export options
                buttons: [
                    'csv', 'excel', 'print'
                ],
                order: [
                    [2, 'desc']
                ],
                columns: [{
                        render: function(data, type, row, meta) {
                            console.log(row)
                            return meta.row + meta.settings._iDisplayStart + 1;
                        },
                    },
                    // {
                    //     data: 'TRANSACTION_ID'
                    // },
                    {
                        data: 'MERCHANT.MERCHANT_NAME'
                    },
                    // {
                    //     data: 'EXPIRE_DATE_TIME'
                    // },
                    {
                        data: 'CREATED_AT'
                    },
                    // {
                    //     data: 'FEE_AMOUNT'
                    // },
                    // {
                    //     data: 'STATUS_TRANSFER'
                    // },
                    {
                        data: 'RETRIEVAL_REFERENCE_NUMBER'
                    },
                    {
                        data: 'AMOUNT',
                        render: function(data, type, row) {
                            return new Intl.NumberFormat('id-ID').format(data);
                        }
                    },
                    {
                        data: 'AMOUNT_REFUND',
                        render: function(data, type, row) {
                            return new Intl.NumberFormat('id-ID').format(data);
                        }
                    },
                    {
                        data: 'AMOUNT_MDR',
                        // render: function(data, type, row) {
                        //     return new Intl.NumberFormat('id-ID').format(data);
                        // }
                    },
                    {
                        data: 'QR_TYPE',
                        render: function(data, type, row, meta) {
                            return data === 11 ? 'static' : data === 12 ? 'dynamic' : '';
                        },
                    },
                    {
                        data: 'NNS'

                    },
                    {
                        data: 'ISSUING_CUSTOMER_NAME'

                    },
                    // {
                    //     render: function(data, type, row, meta) {
                    //         return '<div class="btn-group">' +
                    //             '<button class="btn btn-sm btn-info detail-btn" onclick="showDetail(\'' +
                    //             row.ID + '\', this)">' +
                    //             'Detail' +
                    //             '<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>' +
                    //             '</button>' +
                    //             '</div>';
                    //     }
                    // },
                    {
                        render: function(data, type, row, meta) {
                            if (row.TRANSFER_STATUS === 3 && row.RC_FUND == 68) {
                                return '<div class="btn-group">' +
                                    '<button class="btn btn-sm btn-danger refund-btn" onclick="refundDetail(\'' +
                                    row.ID + '\', this)">' +
                                    '<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>' +
                                    'Suspect</button>' +
                                    '</div>';
                            } else if (row.TRANSFER_STATUS === 3 && row.RC_FUND == 00) {
                                return '<div class="btn-group">' +
                                    '<button class="btn btn-sm btn-success refund-btn" onclick="refundDetail(\'' +
                                    row.ID + '\', this)">' +
                                    '<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>' +
                                    'Refund</button>' +
                                    '</div>';
                            } else {
                                return '<div class="btn-group">' +
                                    '<button class="btn btn-sm btn-primary detail-btn" onclick="trxDetail(\'' +
                                    row.ID + '\', this)">' +
                                    '<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>' +
                                    'Detail</button>' +
                                    '</div>';
                            }
                        }
                    },

                ]
            });

            // Inisialisasi Date Picker untuk Start Date
            $('#start-date').daterangepicker({
                singleDatePicker: true,
                locale: {
                    format: 'YYYY-MM-DD'
                },
                autoUpdateInput: false
            }, function(start) {
                $('#start-date').val(start.format('YYYY-MM-DD'));
                table.draw(); // Trigger the table redraw when the date is selected
            });

            // Inisialisasi Date Picker untuk End Date
            $('#end-date').daterangepicker({
                singleDatePicker: true,
                locale: {
                    format: 'YYYY-MM-DD'
                },
                autoUpdateInput: false
            }, function(end) {
                $('#end-date').val(end.format('YYYY-MM-DD'));
                table.draw(); // Trigger the table redraw when the date is selected
            });

        });

        function formatNumberWithThousandSeparator(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }

        function showDetail(id) {
            var button = event.target;
            button.disabled = true;
            var spinner = button.querySelector('.spinner-border');
            spinner.classList.remove('d-none');

            var url = "{{ route('transactions.detail', ':id') }}";
            url = url.replace(':id', id);

            console.log(url)

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    spinner.classList.add('d-none');
                    button.disabled = false;

                    $('#detailModal').modal('show');
                    $('#ID').val(data.ID);
                    $('#INVOICE_NUMBER').val(data.INVOICE_NUMBER);
                    $('#RETRIEVAL_REFERENCE_NUMBER').val(data.RETRIEVAL_REFERENCE_NUMBER);
                    $('#AMOUNT').val(formatNumberWithThousandSeparator(data.AMOUNT));
                    $('#MERCHANT_ID').val(data.MERCHANT_ID);
                    $('#MERCHANT_NAME').val(data.MERCHANT['MERCHANT_NAME']);
                    $('#POSTAL_CODE').val(data.MERCHANT['POSTAL_CODE']);
                    $('#MERCHANT_ADDRESS').val(data.MERCHANT['MERCHANT_ADDRESS']);
                    $('#MERCHANT_CURRENCY_CODE').val(data.MERCHANT['MERCHANT_CURRENCY_CODE']);
                    $('#ACCOUNT_NUMBER').val(data.MERCHANT['ACCOUNT_NUMBER']);
                    $('#MERCHANT_NAME').val(data.MERCHANT['MERCHANT_NAME']);
                })
                .catch(error => {
                    spinner.classList.add('d-none');
                    button.disabled = false;
                    console.error(error);
                });
        }

        function trxDetail(id) {
            var button = event.target;
            button.disabled = true;
            var spinner = button.querySelector('.spinner-border');
            spinner.classList.remove('d-none');
            var url = "{{ route('transactions.detail', ':id') }}";
            url = url.replace(':id', id);

            fetch(url)
                .then(response => response.json())
                .then(data => {

                    let time = data.bit_12.substring(0, 2) + ':' + data.bit_12.substring(2, 4) + ':' + data.bit_12
                        .substring(4, 6) + ' ' + data.PAID_AT.substring(0, 10);
                    $('#trxModal').modal('show');
                    $('#ID').val(data.ID);
                    $('#CREATED_AT_TRX').val(data.CREATED_AT);
                    $('#RETRIEVAL_REFERENCE_NUMBER_TRX').val(data.RETRIEVAL_REFERENCE_NUMBER);
                    $('#AMOUNT_TRX').val(formatNumberWithThousandSeparator(data.AMOUNT));
                    $('#ACQUIRING_INSTITUTION_NAME_TRX').val(data.ACQUIRING_INSTITUTION_NAME);
                    $('#ISSUING_INSTITUTION_NAME_TRX').val(data.NNS);
                    $('#ISSUING_CUSTOMER_NAME_TRX').val(data.ISSUING_CUSTOMER_NAME);
                    $('#CUSTOMER_PAN_TRX').val(data.CUSTOMER_PAN);
                    $('#MPAN_TRX').val(data.MPAN);
                    $('#FEE_AMOUNT_TRX').val(data.FEE_AMOUNT);
                    $('#bit_12_TRX').val(time);

                    if (data.TRANSFER_STATUS === 1) {
                        $('#TRANSACTION_TYPE_TRX').val('Payment');
                    } else {
                        $('#TRANSACTION_TYPE_TRX').val('Refund');
                    }

                    if (data.TRANSFER_STATUS === 1) {
                        $('#STATUS_TRANSACTION_TRX').val('Success');
                    } else if (data.TRANSFER_STATUS === 3 && data.RC_FUND == 68) {
                        $('#STATUS_TRANSACTION_TRX').val('Suspect');
                    } else {
                        $('#STATUS_TRANSACTION_TRX').val('Refund');
                    }
                    spinner.classList.add('d-none');
                    button.disabled = false;
                })
                .catch(error => {
                    spinner.classList.add('d-none');
                    button.disabled = false;
                    console.error(error);
                });
        }

        function refundDetail(id) {
            var button = event.target;
            button.disabled = true;
            var spinner = button.querySelector('.spinner-border');
            spinner.classList.remove('d-none');

            var url = "{{ route('transactions.detail', ':id') }}";
            url = url.replace(':id', id);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    let time = data.bit_12.substring(0, 2) + ':' + data.bit_12.substring(2, 4) + ':' + data.bit_12
                        .substring(4, 6) + ' ' + data.UPDATED_AT.substring(0, 10);
                    $('#refundModal').modal('show');
                    $('#ID').val(data.ID);
                    $('#CREATED_AT_RF').val(data.CREATED_AT);
                    $('#RETRIEVAL_REFERENCE_NUMBER_RF').val(data.RRN_REFUND);
                    $('#AMOUNT').val(data.AMOUNT);
                    $('#AMOUNT_REFUND').val(data.AMOUNT_REFUND);
                    $('#ACQUIRING_INSTITUTION_NAME').val(data.ACQUIRING_INSTITUTION_NAME);
                    $('#ISSUING_INSTITUTION_NAME').val(data.NNS);
                    $('#ISSUING_CUSTOMER_NAME').val(data.ISSUING_CUSTOMER_NAME);
                    $('#CUSTOMER_PAN').val(data.CUSTOMER_PAN);
                    $('#FEE_AMOUNT_RF').val(data.FEE_AMOUNT);
                    $('#CURRENT_AMOUNT_REFUND_RF').val(data.CURRENT_AMOUNT_REFUND);
                    $('#MPAN_RF').val(data.MPAN);
                    $('#bit_12_RF').val(time);

                    if (data.TRANSFER_STATUS === 1) {
                        $('#TRANSACTION_TYPE_RF').val('Payment');
                    } else {
                        $('#TRANSACTION_TYPE_RF').val('Refund');
                    }

                    if (data.TRANSFER_STATUS === 1) {
                        $('#STATUS_TRANSACTION_RF').val('Success');
                    } else if (data.TRANSFER_STATUS === 3 && data.RC_FUND == 68) {
                        $('#STATUS_TRANSACTION_RF').val('REFUND IN PROGRESS');
                    } else {
                        $('#STATUS_TRANSACTION_RF').val('Refund');
                    }
                    spinner.classList.add('d-none');
                    button.disabled = false;
                })
                .catch(error => {
                    spinner.classList.add('d-none');
                    button.disabled = false;
                    console.error(error);
                });
        }
    </script>


@endsection
