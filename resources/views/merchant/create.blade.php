@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content')
    <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" /> -->

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

    <form action="{{ route('merchant.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-12">
                <div class="card card-default">
                    <div class="card-body">
                        <div class="row">

                            <input type="text" class="form-control" name="roles" id="roles" value="Merchant" hidden
                                readonly>

                            <div class="form-group col-6">
                                <label>Email</label>
                                <input type="text" class="form-control" name="email" id="email"
                                    value="{{ old('email') }}" required>
                            </div>

                            <div class="form-group col-6">
                                <label>Phone</label>
                                <input type="text" class="form-control" name="phone" id="phone"
                                    value="{{ old('phone') }}" required>
                            </div>

                            <div class="form-group col-6">
                                <label for="idMobile">UserID (mobile) <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control" name="idMobile" id="idMobile"
                                    value="{{ old('idMobile') }}">
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">

                <div class="card card-default">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-6">
                                <label>Nomor Rekening</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="norek" id="norek"
                                        value="{{ old('norek') }}" required>
                                    <div class="input-group-append">
                                        <span id="norekStatus" class="input-group-text"></span>
                                        <button type="button" onclick="cekNorek()" class="btn btn-info">Cek No
                                            Rek</button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-6">
                                <label>Cabang</label>
                                <select class="form-control" name="cabang" id="cabang" required>
                                    <!-- Opsi default -->
                                    <option value="">- Pilih Cabang -</option>

                                    @foreach ($cabangs as $cabang)
                                        <option value="{{ $cabang->CPC_MC_KODE_CABANG }}"
                                            data-lokasi="{{ $cabang->CPC_MC_KODE_LOKASI }}">
                                            {{ $cabang->CPC_MC_NAMA }}
                                        </option>
                                    @endforeach
                                </select>
                                {{-- Hidden field --}}
                                <input type="hidden" name="kode_lokasi" id="kode_lokasi">
                            </div>


                            <div class="form-group col-6">
                                <label>MID</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="mid" id="mid"
                                        value="{{ old('mid') }}" readonly required>

                                    <div class="input-group-append">
                                        <span id="midStatus" class="input-group-text"></span>
                                        <button type="button" onclick="generateMid()" class="btn btn-info">Generate
                                            MID</button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-6">
                                <label>Nama Merchant</label>
                                <input type="text" class="form-control" name="merchant" id="merchant"
                                    value="{{ old('merchant') }}" required>
                            </div>

                            <div class="form-group col-6">
                                <label>MPAN</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="mpan" id="mpan"
                                        value="{{ old('mpan') }}" readonly>
                                    <div class="input-group-append">
                                        <button type="button" onclick="generateMpan()" class="btn btn-info">Generate
                                            MPAN</button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-6">
                                <label>NMID</label>
                                <input type="text" class="form-control" name="nmid" id="nmid"
                                    value="{{ old('nmid') }}" readonly>
                            </div>

                            <div class="form-group col-6">
                                <label>Tipe Merchant</label>
                                <select class="form-control" name="merchantTipe" id="merchantTipe"
                                    onchange="toggleFields()" required>
                                    <option value="1">Individu</option>
                                    <option value="2">Badan Usaha</option>
                                </select>
                            </div>

                            <div class="form-group col-6" id="ktpField" style="display:none;">
                                <label>KTP</label>
                                <input type="text" class="form-control" name="ktp" id="ktp">
                            </div>

                            <div class="form-group col-6" id="npwpField" style="display:none;">
                                <label>NPWP</label>
                                <input type="text" class="form-control" name="npwp" id="npwp">
                            </div>

                            <div class="form-group col-6">
                                <label>Tipe QR</label>
                                <select class="form-control" name="qrType" id="qrType" required>
                                    <option value="S">Statis</option>
                                    <option value="D">Dinamis</option>
                                    <option value="B">Statis & Dinamis</option>
                                </select>
                            </div>

                            <div class="form-group col-6">
                                <label>Kategori Merchant (MCC)</label>
                                <select class="form-control" name="mcc" id="mcc" required>
                                    @foreach ($mcc as $dropdown)
                                        <option value="{{ $dropdown['CODE_MCC'] }}">{{ $dropdown['CODE_MCC'] }} -
                                            {{ $dropdown['DESC_MCC'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-6">
                                <label>Kriteria Merchant (Criteria)</label>
                                <select class="form-control" name="criteria" id="criteria" required>
                                    @foreach ($criteria as $dropdown)
                                        <option value="{{ $dropdown['ID'] }}">{{ $dropdown['ID'] }} -
                                            {{ $dropdown['DESC'] }}</option>
                                    @endforeach
                                </select>
                            </div>


                            <div hidden class="form-group  col-6">
                                <label>Fee Merchant</label>
                                <input type="number" class="form-control" name="fee" id="fee" hidden
                                    value="0" min="0" max="100" value="{{ old('fee') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-default">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-6">
                                <label>Kota/Kabupaten</label>
                                <select class="form-control select2" name="city" id="city" required>
                                    <option value="">- Pilih Kota/Kabupaten -</option>
                                    @foreach ($kabKota as $item)
                                        <option value="{{ $item->KOTA_KABUPATEN }}">{{ $item->KOTA_KABUPATEN }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-6">
                                <label>Kecamatan</label>
                                <select class="form-control select2" name="kecamatan" id="kecamatan" required>
                                    <option value="">- Pilih Kecamatan -</option>
                                </select>
                            </div>

                            <div class="form-group col-6">
                                <label>Kode Pos</label>
                                <select class="form-control select2" name="postalcode" id="postalcode" required>
                                    <option value="">- Pilih Kode Pos -</option>
                                </select>
                            </div>

                            <div class="form-group col-6">
                                <label>Alamat</label>
                                <textarea class="form-control" name="address" id="address" rows="3">{{ old('address') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <a class="btn btn-info" href="{{ route('merchant.index') }}">Back</a>
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>

            </div>
        </div>
    </form>
@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi Select2 pada setiap dropdown
            $('#city, #kecamatan, #postalcode').select2({
                placeholder: "Pilih",
                allowClear: true
            });

            // Event listener untuk dropdown kota/kabupaten
            $('#city').on('change', function() {
                var kotaKabupaten = $(this).val();
                if (kotaKabupaten) {
                    $.ajax({
                        url: '{{ route('get.kecamatan') }}',
                        type: 'GET',
                        data: {
                            city: kotaKabupaten
                        },
                        success: function(data) {
                            $('#kecamatan').empty().append(
                                '<option value="">- Pilih Kecamatan -</option>');
                            $.each(data, function(key, value) {
                                $('#kecamatan').append('<option value="' + value
                                    .KECAMATAN + '">' + value.KECAMATAN +
                                    '</option>');
                            });
                            // Refresh Select2 setelah mengubah konten dropdown
                            $('#kecamatan').trigger('change');
                            $('#postalcode').empty().append(
                                '<option value="">- Pilih Kode Pos -</option>');
                        }
                    });
                } else {
                    $('#kecamatan').empty().append('<option value="">- Pilih Kecamatan -</option>').trigger(
                        'change');
                    $('#postalcode').empty().append('<option value="">- Pilih Kode Pos -</option>').trigger(
                        'change');
                }
            });

            // Event listener untuk dropdown kecamatan
            $('#kecamatan').on('change', function() {
                var kecamatan = $(this).val();
                if (kecamatan) {
                    $.ajax({
                        url: '{{ route('get.kodepos') }}',
                        type: 'GET',
                        data: {
                            kecamatan: kecamatan
                        },
                        success: function(data) {
                            $('#postalcode').empty().append(
                                '<option value="">- Pilih Kode Pos -</option>');
                            $.each(data, function(key, value) {
                                $('#postalcode').append('<option value="' + value
                                    .KODEPOS + '">' + value.KODEPOS + '</option>');
                            });
                            // Refresh Select2 setelah mengubah konten dropdown
                            $('#postalcode').trigger('change');
                        }
                    });
                } else {
                    $('#postalcode').empty().append('<option value="">- Pilih Kode Pos -</option>').trigger(
                        'change');
                }
            });

            $('#cabang').on('change', function() {
                var kodeCabang = $(this).val();
                var kodeLokasi = $('#cabang option:selected').data('lokasi');

                $('#kode_cabang').val(kodeCabang); // kalau pakai hidden cabang
                $('#kode_lokasi').val(kodeLokasi);
            });
        });




        // document.addEventListener('DOMContentLoaded', function() {
        //     const provSelect = document.getElementById('prov');
        //     const citySelect = document.getElementById('city');

        //     provSelect.addEventListener('change', function() {
        //         const provinsi = this.value;

        //         if (provinsi) {
        //             fetch(`{{ url('/get-kota') }}/${provinsi}`)
        //                 .then(response => response.json())
        //                 .then(data => {
        //                     citySelect.innerHTML = '<option value="">-</option>'; // Reset kota
        //                     data.forEach(item => {
        //                         const option = document.createElement('option');
        //                         option.value = item.DAERAH_TINGKAT;
        //                         option.text = item.DAERAH_TINGKAT;
        //                         citySelect.appendChild(option);
        //                     });
        //                 });
        //         } else {
        //             citySelect.innerHTML = '<option value="">-</option>';
        //         }
        //     });
        // });

        function toggleFields() {
            var type = $('#merchantTipe').val();
            if (type == '1') {
                $('#ktpField').show();
                $('#npwpField').hide();
            } else if (type == '2') {
                $('#ktpField').hide();
                $('#npwpField').show();
            }
        }

        $(document).ready(function() {
            toggleFields(); // Call on document ready to set the correct field
            // prov(); // Initialize your existing province-city logic
        });

        function cekNorek() {
            var norek = document.getElementById('norek').value;
            var statusIndicator = document.getElementById('norekStatus');
            var inputNorek = document.getElementById('norek');

            // Tampilkan animasi loading
            statusIndicator.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size: 1.5em;"></i>';

            $.ajax({
                url: '{{ route('merchant.rekening') }}',
                type: 'POST',
                data: {
                    norek: norek,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.rc != '0000') {
                        statusIndicator.innerHTML =
                            '<i class="fas fa-times" style="font-size: 1.5em; color: red;"></i>';

                        alert('Nomor Rekening tidak valid: ' + response.msg);
                    } else {
                        statusIndicator.innerHTML =
                            '<i class="fas fa-check" style="font-size: 1.5em; color: green;"></i>';

                        // alert('Nomor Rekening valid');
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.statusText);
                    statusIndicator.innerHTML =
                        '<i class="fas fa-times" style="font-size: 1.5em; color: red;"></i>';
                }
            });
        }


        function generateMid() {
            var selectedCabang = document.getElementById('cabang');
            var kodeCabang = selectedCabang.options[selectedCabang.selectedIndex].value;
            var kodeLokasi = selectedCabang.options[selectedCabang.selectedIndex].getAttribute('data-lokasi');
            var statusIndicator = document.getElementById('midStatus'); // Pastikan elemen ini ada di HTML

            // Periksa apakah cabang sudah terpilih
            if (!kodeCabang) {
                alert('Silakan pilih cabang terlebih dahulu.');
                return; // Hentikan eksekusi fungsi lebih lanjut
            }

            statusIndicator.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size: 1.5em;"></i>';

            $.ajax({
                url: '{{ route('merchant.generateMid') }}',
                type: 'POST',
                data: {
                    kodeCabang: kodeCabang,
                    kodeLokasi: kodeLokasi,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        document.getElementById('mid').value = response.mid;
                        statusIndicator.innerHTML =
                            '<i class="fas fa-check" style="font-size: 1.5em; color: green;"></i>';
                    } else {
                        alert('Gagal menghasilkan MID: ' + response.message);
                        statusIndicator.innerHTML =
                            '<i class="fas fa-times" style="font-size: 1.5em; color: red;"></i>';
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.statusText);
                    statusIndicator.innerHTML =
                        '<i class="fas fa-times" style="font-size: 1.5em; color: red;"></i>';
                }
            });
        }


        function calculateLuhn(input) {
            let sum = 0;
            let shouldDouble = (input.length % 2 === 0); // Menyesuaikan dengan panjang input

            for (let i = 0; i < input.length; i++) {
                let digit = parseInt(input.charAt(i), 10);

                if (shouldDouble) {
                    digit *= 2;
                    if (digit > 9) {
                        digit -= 9;
                    }
                }

                sum += digit;
                shouldDouble = !shouldDouble;
            }

            let checkDigit = (10 - (sum % 10)) % 10;
            return checkDigit;
        }

        function calculateLuhn(digits) {
            let sum = 0;
            let shouldDouble = (digits.length % 2 === 0);

            console.log("Starting Luhn calculation for digits:", digits);
            for (let i = digits.length - 1; i >= 0; i--) {
                let digit = parseInt(digits.charAt(i), 10);
                console.log("Original digit:", digit, "Position:", i);

                if (shouldDouble) {
                    digit *= 2;
                    console.log("Doubled digit:", digit);
                    if (digit > 9) {
                        digit -= 9;
                        console.log("Reduced doubled digit:", digit);
                    }
                }
                sum += digit;
                console.log("Running sum:", sum);
                shouldDouble = !shouldDouble;
            }

            let checkDigit = (10 - (sum % 10)) % 10;
            console.log("Final sum:", sum, "Check digit:", checkDigit);
            return checkDigit;
        }

        // Kode untuk menguji fungsi generateMpan
        function generateMpan() {
            var mid = document.getElementById('mid').value;
            if (mid) {
                var nns = '93600521';
                var baseMpan = nns + "0" + mid;
                console.log(baseMpan);
                // var baseMpan = '936005210040100006';

                var luhnDigit = calculateLuhn(baseMpan);
                console.log(luhnDigit);
                var mpan = baseMpan + luhnDigit;
                console.log(mpan);

                document.getElementById('mpan').value = mpan;
            } else {
                alert('MID belum dihasilkan. Silakan generate MID terlebih dahulu.');
            }
        }
    </script>
@endsection
