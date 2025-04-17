@extends('layouts.admin_new')
@section('title',$dataTitle??$mainTitle??$title??'')
@section('style')
    <link rel="stylesheet" href="{{asset('main/libs/datatables-bs5/datatables.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('main/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('main/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('main/libs/select2/select2.min.css')}}">
@endsection
@section('content')
    <h3 class="page-heading d-flex text-gray-900 fw-bold flex-column justify-content-center my-0">
        @if(isset($dataTitle) && isset($mainTitle) && $mainTitle != $dataTitle)
            {{$mainTitle .' - '.$dataTitle}}
        @else
            {{$mainTitle??$title??''}}
        @endif
    </h3>
    <ul class="breadcrumb breadcrumb-style2">
        <li class="breadcrumb-item">
            <a href="{{route('admin.index')}}" class="text-hover-primary">Beranda</a>
        </li>
        @if(isset($title))
            <li class="breadcrumb-item">
                {{$title}}
            </li>
        @endif
        @if(isset($mainTitle))
            <li class="breadcrumb-item">
                {{$mainTitle}}
            </li>
        @endif
        @if(isset($dataTitle) && isset($mainTitle) && $mainTitle != $dataTitle)
            <li class="breadcrumb-item active">
                {{$dataTitle}}
            </li>
        @endif
    </ul>

    <div class="card">
        <div class="card-header header-elements">
            <div class="card-title">
                <h5 class="mb-0 me-2">{{($dataTitle??$mainTitle)}}</h5>
            </div>
        </div>

        <div class="card-body">
            <fieldset class="form-fieldset">
                <div class="col-12 mb-3">
                    <label class="form-label" for="nis">Nis/No Daftar Siswa</label>
                    <div class="input-group input-group-merge">
                        <input type="text" placeholder="Masukkan Nis/No Daftar siswa" name="nis"
                               id="nis"
                               autocomplete="off"
                               class="form-control @error('password')is-invalid @enderror" required/>
                        <span class="input-group-text cursor-pointer bg-primary text-white cari-siswa"
                              data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss-="click"
                              data-bs-placement="bottom"
                              title="Cari Siswa">
                                <i class="ri ri-search-line me-2"></i>
                                Cari
                            </span>
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="card-datatable table-responsive text-nowrap px-5 card-siswa">
            <table class="table table-sm table-bordered table-hover"
                   id="table-siswa">
                <thead class="table-light">
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div class="card-body">
            <div class="row px-5">
                <h6>
                    TAGIHAN YANG TAMPIL DI BANK
                </h6>
            </div>
            <div class="row">
                <div class="col-7">
                    <div class="card-datatable table-responsive text-nowrap px-5">
                        <div class="col-12">
                            <table class="table table-sm table-bordered table-hover"
                                   id="table-tagihan">
                                <thead class="table-light">
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-datatable table-responsive text-nowrap px-5">
                        <table class="table table-sm table-bordered table-hover"
                               id="table-tagihan-dibayar">
                            <thead class="table-light">
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-5">
                    <div class="card-datatable table-responsive text-nowrap px-5">
                        <table class="table table-sm table-bordered table-hover">
                            <tbody>
                            <tr>
                                <th class="table-light">Akun</th>
                                <td>
                                    <select class="form-select" name="pilih-akun" id="pilih-akun">
                                        <option disabled selected>Pilih Akun</option>
                                        @foreach($v_dt_daftar_harga as $item)
                                            <option data-val="{{json_encode($item)}}"
                                                    value="{{$item->KodeAkun}}">{{$item->NamaAkun}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th class="table-light">Nominal</th>
                                <td>
                                    <input type="text"
                                           class="form-control bg-body rounded-end formattedNumber"
                                           id="nominal-pilih-akun" autocomplete="off"
                                           placeholder="" value="" readonly>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-center">
                                    <button type="button" class="btn btn-primary btn-buat-detail">Buat Detail</button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-datatable table-responsive text-nowrap px-5">
                        <table class="table table-sm table-bordered table-hover" id="table-post-baru">
                            <thead class="table-light">
                            <tr>
                                <th>Nama Post</th>
                                <th>Nominal</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="w-100">
                <div class="row">
                    <div class="col-auto ms-auto d-print-none">
                        <div class="d-flex justify-content-end gap-4">
                            {{--                            <button class="btn btn-danger" data-bs-toggle="modal"--}}
                            {{--                                    data-bs-target="#modal-delete">--}}
                            {{--                                <span class="ri-delete-bin-2-line me-2"></span>--}}
                            {{--                                Hapus Data--}}
                            {{--                            </button>--}}
                            <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#modal-validate">
                                <span class="ri-save-line me-2"></span>
                                Simpan Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{asset('main/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
    <script src="{{asset('js/datatableCustom/Datatable-0-4.min.js')}}"></script>
    <script src="{{asset('main/libs/select2/select2.min.js')}}"></script>
    <script src="{{asset('js/helper/formattedNumber.min.js')}}"></script>

    <script type="text/javascript" defer>
        let tableSiswa;
        let tableTagihan;
        let tableTagihanDibayar;
        let tablePostBaru;

        function inputSiswa() {
            const inputValue = document.getElementById('nis').value;
            if (!inputValue) {
                warningAlert('NIS/No Daftar siswa tidak boleh kosong');
            } else {
                getSiswa(inputValue);
            }
        }

        document.getElementById('nis').addEventListener('keydown', function (e) {
            if (e.key === "Enter") {
                inputSiswa();
            }
        });

        document.querySelector('.cari-siswa').addEventListener('click', function () {
            inputSiswa();
        });

        document.getElementById('table-siswa').addEventListener('click', function (e) {
            if (!e.target.classList.contains('checkbox-siswa')) {
                const row = e.target.closest('tr');
                if (row) {
                    const checkbox = row.querySelector('.checkbox-siswa');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        tableTagihan.clear();
                        tableTagihan.draw();
                        tableTagihanDibayar.clear();
                        tableTagihanDibayar.draw();
                        tablePostBaru.clear();
                        tablePostBaru.draw();
                        checkbox.dispatchEvent(new Event('change', {bubbles: true}));
                    }
                }
            }
        });

        // Existing checkbox change listener
        document.getElementById('table-siswa').addEventListener('change', function (e) {
            if (e.target.classList.contains('checkbox-siswa')) {
                const checkbox = e.target;
                const isChecked = checkbox.checked;
                if (isChecked) {
                    const value = checkbox.value;
                    getTagihan(value);
                }
            }
        });

        document.querySelector('#table-tagihan tbody').addEventListener('dblclick', function (e) {
            const rowEl = e.target.closest('tr');

            if (rowEl) {
                const rowData = tableTagihan.row(rowEl).data();
                if (rowData) {
                    tablePostBaru.row.add(rowData);
                    tablePostBaru.draw();
                }
            }
        });

        document.querySelector('#table-tagihan-dibayar tbody').addEventListener('dblclick', function (e) {
            const rowEl = e.target.closest('tr');

            if (rowEl) {
                const rowData = tableTagihanDibayar.row(rowEl).data();
                if (rowData) {
                    tablePostBaru.row.add(rowData);
                    tablePostBaru.draw();
                }
            }
        });

        document.querySelector('#table-post-baru tbody').addEventListener('dblclick', function (e) {
            if (e.target.tagName.toLowerCase() === 'input' || e.target.closest('input')) {
                return;
            }

            const rowEl = e.target.closest('tr');
            if (rowEl) {
                tablePostBaru.row(rowEl).remove();
                tablePostBaru.draw();
            }
        });

        document.querySelector('#pilih-akun').addEventListener('change', function (e) {
            const selectedOption = this.options[this.selectedIndex];
            const value = selectedOption.value;
            const dataVal = selectedOption.getAttribute("data-val");

            const obj = JSON.parse(dataVal);
            let val = parseInt(obj.nominal);
            val = val.toLocaleString('id-ID');

            document.getElementById('nominal-pilih-akun').value = val;
            console.log(obj.nominal);
            console.log("Selected value:", value);
            console.log("Data-val:", dataVal);
        });

        async function getSiswa(siswa) {
            let url = '{{route('admin.keuangan.tagihan-siswa.buat-tagihan.get-siswa')}}';
            let ajaxOptions = {
                url: url,
                type: 'get',
                datatype: 'json',
                data: {
                    'cari_siswa': siswa,
                    'siswa_only': true
                },
            }

            $.ajax(ajaxOptions).done(function (response) {
                refreshDataTable(response.data);
            }).fail(function (xhr) {
                if (xhr.status === 422) {
                    errorAlert('Gagal mendapat data siswa')
                } else if (xhr.status === 419) {
                    errorAlert('Sesi anda telah habis, Silahkan Login Kembali')
                } else if (xhr.status === 500) {
                    errorAlert('Tidak dapat terhubung ke server, Silahkan periksa koneksi internet anda')
                } else if (xhr.status === 403) {
                    errorAlert('Anda tidak memiliki izin untuk mengakses halaman ini')
                } else if (xhr.status === 404) {
                    errorAlert('Halaman tidak ditemukan')
                } else {
                    errorAlert('Terjadi kesalahan, silahkan coba memuat ulang halaman')
                }
            })
        }

        async function getTagihan(siswa) {
            loadingAlert('Memuat data...');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const request = new Request(
                `{{route('admin.manual-input.edit-manual.get-tagihan')}}?siswa=${encodeURIComponent(siswa)}`,
                {
                    method: "get",
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });

            fetch(request)
                .then(async response => {
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw {status: response.status, message: data.message || response.statusText};
                    }
                    return data;
                })
                .then(data => {
                    refreshTableTagihan(data);
                    console.log("Success:", data);
                    Swal.close();
                })
                .catch(error => {
                    if (error.status === 422) {
                        const errors = error.error.error || error.error.errors;
                        errorAlert(error.error.message);
                        if (errors) {
                            processErrors(errors)
                        }
                    } else {
                        const errorMessages = {
                            401: 'Sesi anda sudah habis 🙏 <br>Silahkan muat ulang halaman untuk melanjutkan! <br> jika masalah masih terjadi silahkan login kembali!',
                            403: 'Anda tidak memiliki izin untuk mengakses halaman ini 😖',
                            404: 'Halaman yang dituju tidak ditemukan 🧐',
                            405: 'Metode tidak valid 🧐 <br>silahkan muat ulang halaman dan coba lagi!',
                            419: 'Sesi anda sudah habis 🙏 <br>Silahkan muat ulang halaman untuk melanjutkan! <br> jika masalah masih terjadi silahkan login kembali!',
                            429: 'Terlalu banyak permintaan akses <br>silahkan tunggu beberapa saat 🙏',
                        };
                        errorAlert(errorMessages[error.status] || "Terjadi kesalahan, silahkan coba memuat ulang halaman");
                        console.log(error)
                    }
                });
        }

        function refreshDataTable(newData = []) {
            tableSiswa.rows().deselect();
            tableSiswa.clear();
            tableSiswa.rows.add(newData);
            tableSiswa.draw();
        }

        function refreshTableTagihan(newData = []) {
            const splitByPaidStatus = newData.reduce((acc, item) => {
                if (item.PAIDST === 0 || item.PAIDST === "0") {
                    acc.unpaid.push(item);
                } else if (item.PAIDST === 1 || item.PAIDST === "1") {
                    acc.paid.push(item);
                }
                return acc;
            }, {paid: [], unpaid: []});

            tableTagihan.rows().deselect();
            tableTagihan.clear();
            tableTagihan.rows.add(splitByPaidStatus.unpaid);
            tableTagihan.draw();

            tableTagihanDibayar.rows().deselect();
            tableTagihanDibayar.clear();
            tableTagihanDibayar.rows.add(splitByPaidStatus.paid);
            tableTagihanDibayar.draw();
        }

        document.addEventListener("DOMContentLoaded", function () {
            const createForm = $('#create-form');
            const languageKey = 'datatables_id_language';
            const languageUrl = '/js/datatableCustom/id.json';

            async function fetchLanguageFile() {
                try {
                    const response = await fetch(languageUrl);
                    if (!response.ok) throw new Error('Network response was not ok');
                    const data = await response.json();
                    localStorage.setItem(languageKey, JSON.stringify(data)); // Save to localStorage
                    return data;
                } catch (error) {
                    console.error('Error fetching language file:', error);
                    return null;
                }
            }

            let languageData = localStorage.getItem(languageKey);

            async function getDTLang() {
                if (!languageData) {
                    languageData = await fetchLanguageFile();
                } else {
                    languageData = JSON.parse(languageData);
                }
            }

            getDTLang();

            tableSiswa = $('#table-siswa').DataTable({
                columns: [
                    {data: 'CUSTID'},
                    {data: 'nis', title: 'NIS'},
                    {data: 'nama', title: 'NAMA'},
                    {data: 'kelas', title: 'Kelas'},
                    {data: 'jenjang', title: 'Jenjang'},
                    {data: 'angkatan', title: 'Angkatan'},
                ],
                columnDefs: [
                    {
                        targets: 0,
                        searchable: false,
                        orderable: false,
                        render: function (data) {
                            return `<input type="checkbox" id="siswa-checkbox-${data}" class="dt-checkboxes form-check-input checkbox-siswa" value="${data}">`;
                        },
                        checkboxes: {
                            selectRow: true,
                            selectAll: false,
                        },
                        className: 'text-center',
                    },
                ],
                language: {
                    ...languageData,
                    emptyTable: "Tidak ada siswa yang sesuai kriteria pencarian"
                },

                paging: true,
                serverSide: false,
                searching: false,
                lengthChange: false,
                pageLength: 10,
                order: [[1, 'desc']],
                select: true,
                scrollX: true,
            });

            tableTagihan = $('#table-tagihan').DataTable({
                columns: [
                    {data: 'BILLNM', title: 'NAMA TAGIHAN'},
                    {data: 'BILLAM', title: 'JUMLAH'},
                    {data: 'BTA', title: 'TAHUN PELAJARAN'},
                    {data: 'PAIDST', title: 'BAYAR'},
                ],
                columnDefs: [
                    {
                        targets: 1,
                        searchable: false,
                        orderable: false,
                        render: function (data, type, row) {
                            let val = parseInt(data);
                            val = val.toLocaleString('id-ID');
                            return val;
                        },
                        className: 'text-center',
                    }
                ],
                language: {
                    ...languageData,
                    emptyTable: "Tidak ada siswa yang sesuai kriteria pencarian"
                },

                paging: true,
                serverSide: false,
                searching: false,
                lengthChange: false,
                pageLength: 10,
                order: [[1, 'desc']],
                scrollX: true,
            });

            tableTagihanDibayar = $('#table-tagihan-dibayar').DataTable({
                columns: [
                    {data: 'BILLNM', title: 'NAMA TAGIHAN'},
                    {data: 'BILLAM', title: 'JUMLAH'},
                    {data: 'BTA', title: 'TAHUN PELAJARAN'},
                    {data: 'PAIDST', title: 'BAYAR'},
                ],
                columnDefs: [
                    {
                        targets: 1,
                        searchable: false,
                        orderable: false,
                        render: function (data, type, row) {
                            let val = parseInt(data);
                            val = val.toLocaleString('id-ID');
                            return val;
                        },
                        className: 'text-center',
                    },
                ],
                language: {
                    ...languageData,
                    emptyTable: "Tidak ada siswa yang sesuai kriteria pencarian"
                },

                paging: true,
                serverSide: false,
                searching: false,
                lengthChange: false,
                pageLength: 10,
                order: [[1, 'desc']],
                scrollX: true,
            });

            tablePostBaru = $('#table-post-baru').DataTable({
                columns: [
                    {data: 'NamaAkun', title: 'NAMA POST'},
                    {data: 'BILLAM', title: 'NOMINAL'},
                ],
                columnDefs: [
                    {
                        targets: 1,
                        searchable: false,
                        orderable: false,
                        render: function (data, type, row) {
                            let val = parseInt(data);
                            val = val.toLocaleString('id-ID');
                            return `
                            <input type="text" class="form-control bg-body rounded-end nominal-input formattedNumber"
                                    id="tagihan[${row.BILLCD}][nominal]" autocomplete="off" placeholder="Nominal Tagihan" value="${val}">
                                <div class="invalid-feedback" role="alert"></div>
                            `;
                        },
                        className: 'text-center',
                    },
                ],
                language: {
                    ...languageData,
                    emptyTable: "silahkan pilih tagihan"
                },

                paging: false,
                serverSide: false,
                searching: false,
                lengthChange: false,
                pageLength: 10,
                order: [[1, 'desc']],
                scrollX: true,
            });
        });

    </script>
@endsection

