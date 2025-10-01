@extends('adminlte::page')

@section('title', 'Dashboard')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
@stop

<style>
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 0 !important;  
        height: auto !important;     
        margin-top: -7px !important;  
    }
        
    </style>


@section('content')
    <!-- Import library Select2 -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1> Refund </h1>
                </div>
            </div>
        </div>
    </section>

    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif
    @if ($message = Session::get('errors'))
        <div class="alert alert-danger">
            <p>{{ $message }}</p>
        </div>
    @endif

    {{-- @if ($errors->any())
	<div class="alert alert-danger">
		<label>Whoops!</label> There were some problems with your input.<br><br>
		<ul>
			@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
			@endforeach
		</ul>
	</div>
	@endif --}}


    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <form action="{{ route('refund.hit') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">

                            <!-- Elemen Select dengan Select2 -->
                            <div class="form-group col-7">
                                <label>Select RNN : </label>
                                <select class="form-control select2" name="RRN" id="RRN" required>
                                    <option value=""></option>
                                    @foreach (array_reverse($data) as $dropdown)
                                        <option value="{{ $dropdown['RETRIEVAL_REFERENCE_NUMBER'] }}"
                                            data-fetch1="{{ $dropdown['AMOUNT'] }}"
                                            data-fetch2="{{ $dropdown['MERCHANT_ACC_NUMBER'] }}"
                                            data-fetch3="{{ $dropdown['INVOICE_NUMBER'] }}"
                                            data-fetch4="{{ $dropdown['ON_US'] }}"
                                            data-fetch5="{{ $dropdown['UPDATED_AT'] }}">
                                            {{ $dropdown['RETRIEVAL_REFERENCE_NUMBER'] }}
                                            ({{ $dropdown['ON_US'] == 1 ? 'ON US' : 'OF US' }})
                                            ->
                                            {{ date('Y-m-d H:i', strtotime($dropdown['CREATED_AT'])) }}

                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- <div class="form-group col-7">
                   <label> Select Invoice </label>
                   
                   <select class="form-control" name="INVOICE_NUMBER" id="INVOICE_NUMBER" required>
                                            <option value="">Select Invoice</option>
                    @foreach ($data as $dropdown)
    <option value="{{ $dropdown['INVOICE_NUMBER'] }}" data-fetch1="{{ $dropdown['AMOUNT'] }}" data-fetch2="{{ $dropdown['MERCHANT_ACC_NUMBER'] }}" > {{ $dropdown['INVOICE_NUMBER'] }}  </option>
    @endforeach
                   </select>
                   
                   

                  </div> -->
                            <div class="form-group col-7">
                                <label>Invoice :</label>
                                <input type="number" id="INVOICE_NUMBER" name="INVOICE_NUMBER" class="form-control">
                            </div>
                            <div class="form-group col-7">
                                <label>Transaction Date :</label>
                                <input type="text" id="UPDATED_AT" name="UPDATED_AT" class="form-control" readonly>
                            </div>
                            <div class="form-group col-7">
                                <label>Transaction Type :</label>
                                <input type="text" id="ON_US" name="ON_US" class="form-control" readonly>
                            </div>
                            <div class="form-group col-7">
                                <label>Nomor Rekening Merchant:</label>
                                <input type="number" id="ACC_SRC" name="ACC_SRC" class="form-control"readonly>
                            </div>
                            <div class="form-group col-7">
                                <label>Transaction Amount:</label>
                                <input type="number" id="AMOUNTS" name="AMOUNTS" class="form-control"readonly>
                            </div>

                            <div class="form-group col-7">
                                <label>Input Amount:</label>

                                @foreach ($data as $dropdown)
                                    @if (is_int($dropdown['AMOUNT']) && $dropdown['AMOUNT'] > 0)
                                        <input type="number" id="AMOUNT" name="AMOUNT" class="form-control" required>
                                    @else
                                        <input type="number" id="AMOUNT" name="AMOUNT" class="form-control" required>
                                    @endif
                                @break
                            @endforeach

                        </div>




                        <script src="https://code.jquery.com/jquery-3.6.0.min.js"
                            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
                    </div>
                </div>


                <div class="card-footer">
                    <a class="btn btn-info" href="{{ route('merchant.index') }}">Back</a>
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
        </div>
        </form>
    </div>
</div>
</div>

<!-- Script -->
<script>
    // Inisialisasi Select2 pada elemen <select>
    $(document).ready(function() {
        $('#RRN').select2();
    });

    $(document).ready(function() {
        $('#RRN').on('change', function() {
            const selected = $(this).find('option:selected');
            const out1 = selected.data('fetch1');
            const out2 = selected.data('fetch2');
            const out3 = selected.data('fetch3');
            const out4 = selected.data('fetch4');
            const out5 = selected.data('fetch5');

            if (out4 === 1) {
                $('#ON_US').val('ON US');
            } else {
                $('#ON_US').val('OF US');
            }



            $("#ACC_SRC").val(out2);
            $("#INVOICE_NUMBER").val(out3);
            $("#AMOUNTS").val(out1);
            $("#AMOUNT").val(out1);

            $("#UPDATED_AT").val(out5);
        });
    });
</script>

@endsection
