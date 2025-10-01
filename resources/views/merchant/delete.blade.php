@extends('adminlte::page')

@section('title', 'Edit Merchant')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Delete Merchant Details</h1>
                </div>
            </div>
        </div>
    </section>

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

    <form action="{{ route('merchant.destroy', $merchant->ID) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-12">
                <div class="card card-default">
                    <div class="card-body">
                        <div class="row">

                            <input type="text" class="form-control" name="ID_MERCHANT" value="{{ $merchant->ID }}" hidden
                                readonly>
                            <input type="text" class="form-control" name="ID_MERCHANT_DETAILS"
                                value="{{ $merchant_detail->ID }}" hidden readonly>
                            <input type="text" class="form-control" name="ID_MERCHANT_DOMESTIC"
                                value="{{ $merchant->QRIS_MERCHANT_DOMESTIC_ID }}" hidden readonly>

                            <!-- Field lainnya -->
                            <input type="text" class="form-control" name="CREATED_AT" value="{{ $merchant->CREATED_AT }}"
                                hidden readonly>
                            <input type="text" class="form-control" name="UPDATED_AT" value="{{ $merchant->UPDATED_AT }}"
                                hidden readonly>
                            <input type="text" class="form-control" name="TERMINAL_LABEL"
                                value="{{ $merchant->TERMINAL_LABEL }}" hidden readonly>
                            <input type="text" class="form-control" name="MERCHANT_COUNTRY"
                                value="{{ $merchant->MERCHANT_COUNTRY }}" hidden readonly>
                            <input type="text" class="form-control" name="QRIS_MERCHANT_DOMESTIC_ID"
                                value="{{ $merchant->QRIS_MERCHANT_DOMESTIC_ID }}" hidden readonly>
                            <input type="text" class="form-control" name="TYPE_QR" value="{{ $merchant->TYPE_QR }}"
                                hidden readonly>
                            <input type="text" class="form-control" name="MERCHANT_NAME"
                                value="{{ $merchant->MERCHANT_NAME }}" hidden readonly>
                            <input type="text" class="form-control" name="MERCHANT_CITY"
                                value="{{ $merchant->MERCHANT_CITY }}" hidden readonly>
                            <input type="text" class="form-control" name="POSTAL_CODE"
                                value="{{ $merchant->POSTAL_CODE }}" hidden readonly>
                            <input type="text" class="form-control" name="MERCHANT_CURRENCY_CODE"
                                value="{{ $merchant->MERCHANT_CURRENCY_CODE }}" hidden readonly>
                            <input type="text" class="form-control" name="MERCHANT_TYPE"
                                value="{{ $merchant->MERCHANT_TYPE }}" hidden readonly>
                            <input type="text" class="form-control" name="MERCHANT_EXP"
                                value="{{ $merchant->MERCHANT_EXP }}" hidden readonly>
                            <input type="text" class="form-control" name="MERCHANT_CODE"
                                value="{{ $merchant->MERCHANT_CODE }}" hidden readonly>
                            <input type="text" class="form-control" name="MERCHANT_ADDRESS"
                                value="{{ $merchant->MERCHANT_ADDRESS }}" hidden readonly>
                            <input type="text" class="form-control" name="STATUS" value="{{ $merchant->STATUS }}"
                                hidden readonly>
                            <input type="text" class="form-control" name="NMID" value="{{ $merchant->NMID }}" hidden
                                readonly>
                            <input type="text" class="form-control" name="ACCOUNT_NUMBER"
                                value="{{ $merchant->ACCOUNT_NUMBER }}" hidden readonly>
                            <input type="text" class="form-control" name="MAX_LIMIT_TRANSACTION"
                                value="{{ $merchant->MAX_LIMIT_TRANSACTION }}" hidden readonly>
                            <input type="text" class="form-control" name="USER_ID" value="{{ $merchant->USER_ID }}"
                                hidden readonly>
                            <input type="text" class="form-control" name="KTP" value="{{ $merchant->KTP }}"
                                hidden readonly>
                            <input type="text" class="form-control" name="NPWP" value="{{ $merchant->NPWP }}"
                                hidden readonly>
                            <input type="text" class="form-control" name="USER_ID_MOBILE"
                                value="{{ $merchant->USER_ID_MOBILE }}" hidden readonly>
                            <input type="text" class="form-control" name="PHONE_MOBILE"
                                value="{{ $merchant->PHONE_MOBILE }}" hidden readonly>
                            <input type="text" class="form-control" name="EMAIL_MOBILE"
                                value="{{ $merchant->EMAIL_MOBILE }}" hidden readonly>
                            <input type="text" class="form-control" name="QR_TYPE" value="{{ $merchant->QR_TYPE }}"
                                hidden readonly>
                            <input type="text" class="form-control" name="MERCHANT_TYPE_2"
                                value="{{ $merchant->MERCHANT_TYPE_2 }}" hidden readonly>

                            <div class="form-group col-6">
                                <label>NMID</label>
                                <input type="text" class="form-control" name="NMID" value="{{ $merchant->NMID }}"
                                    readonly>
                            </div>

                            <div class="form-group col-6">
                                <label>Nama Merchant (max 50)</label>
                                <input type="text" class="form-control" name="MERCHANT_NAME"
                                    value="{{ $merchant->MERCHANT_NAME }}" required readonly>
                            </div>

                            <div class="form-group col-6">
                                <label>MPAN</label>
                                <input type="text" class="form-control" name="MPAN"
                                    value="{{ $merchant_detail->MPAN }}" readonly readonly>
                            </div>

                            <div class="form-group col-6">
                                <label>MID</label>
                                <input type="text" class="form-control" name="MID"
                                    value="{{ $merchant_detail->MID }}" readonly readonly>
                            </div>

                            <div class="form-group col-6">
                                <label>Kota</label>
                                <input type="text" class="form-control" name="MERCHANT_CITY"
                                    value="{{ $merchant->MERCHANT_CITY }}" readonly>
                            </div>

                            <div class="form-group col-6">
                                <label>Kodepos</label>
                                <input type="text" class="form-control" name="POSTAL_CODE"
                                    value="{{ $merchant->POSTAL_CODE }}" readonly>
                            </div>

                            <div class="form-group col-6">
                                <label>Kriteria Merchant</label>
                                <select class="form-control" name="CRITERIA" id="CRITERIA" readonly required>
                                    @foreach ($criteria as $option)
                                        <option value="{{ $option['id'] }}"
                                            {{ $merchant_detail->CRITERIA == $option['id'] ? 'selected' : '' }}>
                                            {{ $option['id'] }} - {{ $option['desc'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-6">
                                <label>Kategori Merchant (MCC)</label>
                                <select class="form-control" name="MCC" id="MCC"  readonly required>
                                    @foreach ($mcc as $dropdown)
                                        <option value="{{ $dropdown['CODE_MCC'] }}"
                                            {{ $merchant_domestic->MCC == $dropdown['CODE_MCC'] ? 'selected' : '' }}>
                                            {{ $dropdown['CODE_MCC'] }} - {{ $dropdown['DESC_MCC'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>



                            <div class="form-group col-6">
                                <label>Jumlah Terminal</label>
                                <input type="number" class="form-control" name="JML_TERMINAL" value="1" readonly>
                            </div>

                            <div class="form-group col-6">
                                <label>Tipe Merchant</label>
                                <select class="form-control" name="MERCHANT_TYPE_2" id="merchantTipe"
                                    onchange="toggleFields()" readonly required>
                                    <option value="1" {{ $merchant->MERCHANT_TYPE_2 == '1' ? 'selected' : '' }}>
                                        Individu</option>
                                    <option value="2" {{ $merchant->MERCHANT_TYPE_2 == '2' ? 'selected' : '' }}>Badan
                                        Usaha</option>
                                </select>
                            </div>


                            <div class="form-group col-6" id="ktpField" style="display:none;">
                                <label>KTP</label>
                                <input type="text" class="form-control" name="KTP" id="KTP"
                                    value="{{ $merchant->KTP }}" readonly>
                            </div>

                            <div class="form-group col-6" id="npwpField" style="display:none;">
                                <label>NPWP</label>
                                <input type="text" class="form-control" name="NPWP" id="NPWP"
                                    value="{{ $merchant->NPWP }}" readonly>
                            </div>

                            <div class="form-group col-6">
                                <label>Tipe QR</label>
                                <select class="form-control" name="QR_TYPE" id="QR_TYPE" readonly required>
                                    <option value="D" {{ $merchant->QR_TYPE == 'D' ? 'selected' : '' }} disabled>Dinamis
                                    </option>
                                    <option value="S" {{ $merchant->QR_TYPE == 'S' ? 'selected' : '' }} disabled>Statis
                                    </option>
                                    <option value="B" {{ $merchant->QR_TYPE == 'B' ? 'selected' : '' }} disabled>Statis &
                                        Dinamis</option>
                                </select>
                            </div>

                            @if (!empty($merchant->NMID))
                                <div class="form-group col-12">
                                    <label>Catatan Pengahapusan</label>
                                    <textarea class="form-control" name="KETERANGAN_UPDATE"></textarea>
                                </div>
                            @else
                                <div class="form-group col-12">
                                    <label>Catatan Pengahapusan</label>
                                    <textarea class="form-control" name="KETERANGAN_UPDATE" readonly></textarea>
                                </div>
                            @endif


                        </div>
                    </div>
                    <div class="card-footer">
                        <a class="btn btn-info" href="{{ route('merchant.index') }}">Back</a>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script>
        function toggleFields() {
            var selection = document.getElementById('merchantTipe').value;
            var ktpField = document.getElementById('ktpField');
            var npwpField = document.getElementById('npwpField');

            if (selection === '1') {
                ktpField.style.display = 'block';
                npwpField.style.display = 'none';
            } else if (selection === '2') {
                ktpField.style.display = 'none';
                npwpField.style.display = 'block';
            }
        }

        // Call toggleFields on page load to set the correct state based on the current value
        document.addEventListener('DOMContentLoaded', toggleFields);
    </script>

@endsection
