@extends('adminlte::page')

@section('title', 'Dashboard')


@section('css')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-switch/css/bootstrap2/bootstrap-switch.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor\jquery-ui\jquery-ui.min.css') }}">
<style>
    .modal-body {
        word-wrap: break-word;
        white-space: pre-wrap; /* Menangani spasi dan baris baru */
    }
</style>
@stop

@section('js')

<script src="{{ asset('vendor/bootstrap-switch/js/bootstrap-switch.min.js') }}"></script>
<script src="{{ asset('vendor\jquery-ui\jquery-ui.min.js') }}"></script>
<script>

function fetchData() {
    console.log('Fetching Data....')
    $.ajax({
    url: '{{ route('health.fetchData') }}',
    method: 'GET',
            success: function (data) {
                let rows = '';
                data.data.forEach(function (row) {
                    let statusClass = '';
                    let statusMsg = '';
                    if (row.ACTIVE == 1) {
                        statusClass = 'bg-success';
                        statusMsg = 'OK';
                    } else if (row.ACTIVE == 2) {
                        statusClass = 'bg-danger';
                        statusMsg = 'INACTIVE SERVICE';
                    } else if (row.ACTIVE == 3) {
                        statusClass = 'bg-danger';
                        statusMsg = 'INACTIVE PORT';
                    } else if (row.ACTIVE == 4) {
                        statusClass = 'bg-warning';
                        statusMsg = 'Out Exception';
                    } else if (row.ACTIVE == 5) {
                        statusClass = 'bg-danger';
                        statusMsg = 'Timeout';
                    }
                    rows += `
                            <tr class="${statusClass}">
                                <td>${row.ID}</td>
                                <td>${row.SERVICE_NAME}</td>
                                <td>${row.HOST}</td>
                                <td>${row.PORT}</td>
                                <td>${statusMsg}</td>
                                <td><button class="btn btn-secondary"  onclick="detail('${row.ID}')">Detail</button>
                                <input type="hidden" id="${row.ID}" value='${row.ACTIVE_DESC}'/>
                                </td>
                            </tr>
                        `;
                });
                $('#health-data').html(rows);
            }
}
)}
function detail(id) {
// Escape karakter khusus
    let  activeDesc = $('#' + id).val();
    var escapedDesc = activeDesc.replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;")
            .replace(/(\r\n|\n|\r)/gm, " ");
    document.getElementById('modalContent').innerHTML = escapedDesc;
    $('#detailModal').modal('show');
}
function addslashes(str) {
    return (str + '')
            .replace(/\\/g, '\\\\')
            .replace(/'/g, '\\\'')
            .replace(/"/g, '\\"')
            .replace(/\0/g, '\\0');
}
$(document).ready(function () {

    let intervalId;
    let intervalTime = 5000;
    $("#toggleSwitch").bootstrapSwitch();
    if ($('#toggleSwitch').is(':checked')) {
        intervalId = setInterval(fetchData, intervalTime); // 5 detik
        console.log("Interval started");
    }
    $('#toggleSwitch').on('switchChange.bootstrapSwitch',
            function (event, state) {
                if (state) {
                    intervalId = setInterval(fetchData, intervalTime); // 5 detik
                    console.log("Interval started");
                } else {
                    clearInterval(intervalId);
                    intervalId = null;
                    console.log("Interval stopped");
                }
            }
    );

//    $("#intervalSlider").slider({
//        range: "min",
//        value: 5,
//        min: 1,
//        max: 60,
//        slide: function (event, ui) {
//            $("#intervalValue").val(ui.value + " detik");
//            intervalTime = ui.value * 1000; // Konversi ke milidetik
//            if ($('#toggleSwitch').is(':checked')) {
//                clearInterval(intervalId);
//                intervalId = setInterval(fetchData, intervalTime);
//                console.log("Interval updated to " + intervalTime + " ms");
//            }
//        }
//    });
//    $("#intervalValue").val($("#intervalSlider").slider("value") + " detik");
});
</script>
@stop


@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Health Check Monitoring</h2>
        </div>
    </div>
</div>
<br>
<div class="row">
    <div class="col">
        <div class="form-group ml-5 d-flex justify-content-end">
            AUTO REFRESH&nbsp;  <input type="checkbox" name="my-checkbox" id="toggleSwitch" checked>
        </div>
    </div>
    <!--    <div class="form-group ml-5">
            <label for="intervalSlider">Interval (detik):</label>
            <input type="text" id="intervalValue" readonly style="border:0; color:#f6931f; font-weight:bold;">
            <div id="intervalSlider"></div>
        </div>-->

</div>
@if ($message = Session::get('success'))
<div class="alert alert-success">
    <p>{{ $message }}</p>
</div>
@endif



<table class="table table-bordered">
    <thead>
        <tr>
            <th>No</th>
            <th>Service Name</th>
            <th>Host</th>
            <th>Port</th>
            <th>Status</th>
            <th>Desc</th>
        </tr>
    </thead>
    <tbody id="health-data">
        @foreach ($data as $row)
        <tr class="
            @if($row->ACTIVE == 1) bg-success
            @elseif ($row->ACTIVE == 2) bg-danger
            @elseif ($row->ACTIVE == 3) bg-danger
            @elseif ($row->ACTIVE == 4) bg-warning
            @elseif ($row->ACTIVE == 5) bg-danger
            @endif
            ">
            <td>{{ $row->ID }}</td>
            <td>{{ $row->SERVICE_NAME }}</td>
            <td>{{ $row->HOST }}</td>
            <td>{{ $row->PORT }}</td>
            <td>  @if($row->ACTIVE == 1) OK
                @elseif ($row->ACTIVE == 2) INACTIVE SERVICE
                @elseif ($row->ACTIVE == 3) INACTIVE PORT 
                @elseif ($row->ACTIVE == 4) Out Exception
                @elseif ($row->ACTIVE == 5) Timeout
                @endif</td>
            <td><button class="btn btn-secondary"  onclick="detail('{{ $row->ID }}')">Detail</button>
                <input type="hidden" id="{{ $row->ID }}" value='{{ $row->ACTIVE_DESC }}'/>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="modalContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection