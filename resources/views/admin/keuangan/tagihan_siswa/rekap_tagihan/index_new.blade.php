@extends('layouts.admin_new')
@section('style')
    <link rel="stylesheet" href="{{asset('main/libs/select2/select2.css')}}">

    <style>
        .input-tagihan {
            min-width: 200px;
        }
    </style>
    <style>
        table.dataTable tr.selected {
            border-top: 2px solid var(--bs-primary);
            border-bottom: 2px solid var(--bs-primary);
            border-left: none;
            border-right: none;
        }
    </style>

    <link rel="stylesheet" href="{{asset('main/libs/datatables-bs5/datatables.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('main/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('main/libs/datatables-select-bs5/select.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('main/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
@endsection
@section('content')
    <h3 class="page-heading d-flex text-gray-900 fw-bold flex-column justify-content-center my-0">
        {{($dataTitle??($mainTitle??($title??'')))}}
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

    <form id="create-form" class="mainForm">
        @csrf
        <div class="card mb-6">
            <div class="card-header header-elements">
                <h5 class="mb-0 me-2">{{($dataTitle??$mainTitle)}}</h5>
                <div class="card-header-elements ms-auto">
                    <div class="w-100">
                        <div class="row">

                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body py-0">
                <form id="filterForm">
                    <h5>Filter</h5>
                    <div class="row row-cols-lg-2 row-cols-1 row-gap-5">
                        <div class="col order-0">
                            <div class="row d-flex align-items-center">
                                <div class="col-3">
                                    <label class="required form-label" for="tahun_pelajaran">
                                        Tahun Pelajaran
                                    </label>
                                </div>
                                <div class="col">
                                    <select class="form-select form-select-sm" id="tahun_pelajaran"
                                            name="tahun_pelajaran" data-width="100%"
                                            data-control="select2"
                                            data-placeholder="Pilih Tahun Pelajaran">
                                        @isset($thn_aka)
                                            @foreach($thn_aka as $item)
                                                <option
                                                    value="{{$item->thn_aka}}" {{$item->thn_aka == "2020/2021 - GANJIL" ? 'selected':''}}>{{$item->thn_aka}}</option>
                                            @endforeach
                                        @else
                                            <option>data kosong</option>
                                        @endisset
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col order-2">
                            <div class="row d-flex align-items-center">
                                <div class="col-3">
                                    <label class="form-label" for="nis">
                                        Nis
                                    </label>
                                </div>
                                <div class="col">
                                    <input class="form-control" id="nis" name="nis"
                                           placeholder="Nis">
                                </div>
                            </div>
                        </div>
                        <div class="col order-1">
                            <div class="row d-flex align-items-center">
                                <div class="col-3">
                                    <label class="required form-label" for="tahun_angkatan">
                                        Tahun Angkatan
                                    </label>
                                </div>
                                <div class="col">
                                    <select class="form-select form-select-sm" id="tahun_angkatan"
                                            name="tahun_angkatan" data-width="100%"
                                            data-control="select2"
                                            data-placeholder="Pilih Tahun Akademik">
                                        @isset($thn_aka)
                                            @foreach($thn_aka as $item)
                                                <option
                                                    value="{{$item->thn_aka}}" {{$item->thn_aka == "2024/2025" ? 'selected':''}}>{{$item->thn_aka}}</option>
                                            @endforeach
                                        @else
                                            <option>data kosong</option>
                                        @endisset
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col order-3">
                            <div class="row d-flex align-items-center">
                                <div class="col-3">
                                    <label class="form-label" for="kelas">
                                        Kelas
                                    </label>
                                </div>
                                <div class="col">
                                    <select class="form-select" id="kelas" name="kelas"
                                            data-control="select2" data-placeholder="">
                                        <option></option>
                                        @isset($kelas)
                                            @foreach($kelas as $item)
                                                <option value="{{$item->id}}" {{$item->id == 210 ? 'selected' : ''}}>
                                                    {{$item->unit}} - {{$item->jenjang}} {{$item->kelas}} </option>
                                            @endforeach
                                        @else
                                            <option>data kosong</option>
                                        @endisset
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col order-4">
                            <div class="row d-flex align-items-center">
                                <div class="col-3">
                                    <label class="form-label" for="nama">
                                        Nama
                                    </label>
                                </div>
                                <div class="col">
                                    <input class="form-control" id="nama" name="nama"
                                           placeholder="Nama">
                                </div>
                            </div>
                        </div>
                        <div class="col order-5">
                            <div class="row d-flex align-items-center">
                                <div class="col-3">
                                    <label class="form-label" for="tagihan">
                                        tagihan
                                    </label>
                                </div>
                                <div class="col">
                                    <select class="form-select" id="tagihan" name="tagihan"
                                            data-control="select2" data-placeholder="Pilih tagihan">
                                        @isset($tagihan)
                                            @foreach($tagihan as $item)
                                                <option
                                                    value="{{$item->kode}}">{{$item->tagihan}}</option>
                                            @endforeach
                                        @else
                                            <option>data kosong</option>
                                        @endisset
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="w-100 py-5">
                    <div class="row">
                        <div class="d-flex justify-content-center justify-content-md-end gap-4">
                            <button type="button" class="btn btn-whatsapp">
                                <span class="ri-file-excel-line me-2"></span>
                                Cetak Rekap
                            </button>
                            <button type="button" class="btn btn-whatsapp">
                                <span class="ri-user-line me-2"></span>
                                Cetak Per Nis
                            </button>
                            <button type="button" class="btn btn-whatsapp">
                                <span class="ri-profile-line me-2"></span>
                                Cetak Kartu Siswa
                            </button>
                            <button type="reset" class="btn btn-outline-secondary button_reset_cari">
                                <span class="ri-reset-left-line me-2"></span>
                                Reset
                            </button>
                            <button type="button" class="btn btn-outline-primary button_cari_cari">
                                <span class="ri-search-line me-2"></span>
                                Cari
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-datatable table-responsive text-nowrap px-5card-siswa">
                <table class="table table-sm table-bordered table-hover"
                       id="table-siswa">
                    <thead class="table-light">
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
@endsection

@section('script')
    <script src="{{asset('main/libs/select2/select2.js')}}"></script>
    <script src="{{asset('main/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>

    <script>
        // const date = new Date();
        // const year = date.getFullYear();
        // const month = String(date.getMonth() + 1).padStart(2, '0');
        // const formattedDate = `${year}${month}`;
        // const fungsiInput = document.getElementById('fungsi');
        // fungsiInput.value = formattedDate;


        let dataColumns = [];
        let dataTableInit;
        let formId = '';
        let tableId = 'main_table';
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        const select2 = $(`[data-control='select2']`);
        let htmlTable = '';
        let tableSiswa;
        let tablePost;
        let cardSiswa = $('.card-siswa');
        let mainElementId = 'tagihan[0][post]';

        let fileIdCOunt = 1;

        function clearErrorMessages(formId) {
            const form = document.querySelector(`#${formId}`);
            const errorElements = form.querySelectorAll('.invalid-feedback');
            const errorClass = form.querySelectorAll('.is-invalid');

            errorElements.forEach(element => element.textContent = '');
            errorClass.forEach(element => element.classList.remove('is-invalid'));
        }

        function resetSelect2(elementId) {
            $('#' + elementId).val(null).trigger('change');
        }

        function resetTextInput(elementId) {
            $('#' + elementId).val('');
        }

        function detailForm(data) {
            let input = [
                $('#id_thn_aka'),
                $('#kelas'),
                $('#cari_siswa'),
            ];
            input.forEach(function (item, key) {
                if (!data[key]) {
                    item.parent().parent().parent().parent().addClass('d-none');
                    item.prop('required', false);
                } else {
                    item.parent().parent().parent().parent().removeClass('d-none');
                    item.prop('required', true);
                }
                resetMainForm();
                // console.log(key, data[key], item);
            })
        }

        function resetMainForm() {
            resetSelect2('id_thn_aka');
            resetTextInput('id_thn_aka');
            resetSelect2('kelas');
            resetTextInput('kelas');
        }

        function refreshDataTable(newData = []) {
            tableSiswa.rows().deselect();
            tableSiswa.clear();
            tableSiswa.rows.add(newData);
            tableSiswa.draw();
            // newData.length === 0 ? '' : $('.select-all').prop('checked', true);
        }


        async function getSiswa(Angkatan, jenjang, Kelas, siswa = null) {
            let url = '{{route('admin.keuangan.tagihan-siswa.buat-tagihan.get-siswa')}}';
            let ajaxOptions = {
                url: url,
                type: 'get',
                datatype: 'json',
                data: {
                    'angkatan': Angkatan,
                    'jenjang': jenjang,
                    'kelas': Kelas,
                    'cari_siswa': siswa
                },
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
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

        document.addEventListener("DOMContentLoaded", function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

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
                            selectAllRender: '<input id="siswa-checkbox-all" name="siswa-checkbox" type="checkbox" class="form-check-input select-all">'
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
                select: {
                    style: 'multi'
                },
                scrollX: true,
            });

            createForm.on('reset', function (e) {
                setTimeout(function () {
                    refreshDataTable();
                    cardSiswa.addClass('d-none');
                    cardSiswa.prev().addClass('d-none');
                    const select2InForm = select2.filter(`#create-form [data-control='select2']`);
                    $('#main_table tbody tr:not(:first)').remove();
                    let element = $('#' + mainElementId.replace(/([[\]])/g, '\\$1'));
                    if (element) {
                        element.trigger('change');
                    }
                    if (select2InForm.length) {
                        select2InForm.each(function () {
                            let $this = $(this);
                            $this.trigger('change');
                        });
                    }
                }, 0)
            });

            let jenis = $('#jenis');
            jenis.on('change', function (e) {
                let isChecked = $(this).is(':checked');
                if (jenis.val() === 1) {
                    row.find('tagihan[*]cicilan').prop('readonly', isChecked);
                    row.find('tagihan[*]cicilan').prop('required', !isChecked);
                }
            })

            $(createForm).on('change', '#check-all', function () {
                let isChecked = $(this).is(':checked');
                $('.row-checkbox').prop('checked', isChecked).trigger('change');
            });

            $(createForm).on('click', '.button_cari_cari', function (e) {
                let angkatan = $('#tahun_angkatan').val();
                let thn_aka = $('#tahun_pelajaran').val();
                let kelas = $('#kelas').val();
                let jenjang = $('#jenjang').val();
                let cariSiswa = $('#cari_siswa').val()
                refreshDataTable();

                if (angkatan && kelas && thn_aka) {
                    getSiswa(angkatan, jenjang, kelas, cariSiswa)
                } else {
                    warningAlert(`Pastikan telah memilih Tahun Pelajaran, Angkatan dan kelas`)
                }
            });

            $(document).on('keypress', '.formattedNumber', function (e) {
                const charCode = e.which ? e.which : e.keyCode;
                if (charCode < 48 || charCode > 57) {
                    e.preventDefault();
                }
            });

            $(document).on('input', '.formattedNumber', function (e) {
                const formattedValue = $(this).val();
                const parsedNumber = parseInt(formattedValue.replace(/\./g, ''));

                if (!isNaN(parsedNumber)) {
                    const formattedString = parsedNumber.toLocaleString('id-ID');
                    $(this).val(formattedString);
                } else {

                }
            });

            if (select2.length) {
                select2.each(function () {
                    let $this = $(this);
                    // select2Focus($this);
                    $this.wrap('<div class="position-relative"></div>').select2({
                        placeholder: 'Select value',
                        dropdownParent: $this.parent()
                    });
                });
            }

            $('.btn-add-tagihan').on('click', function (e) {
                e.preventDefault();
                addTagihan();
            });

            $('#main_table').on('click', '.btn-remove-row', function () {
                $(this).closest('tr').remove();
            });

            $(document).on('change', '.select-post', function (e) {
                const selectedOption = $(this).find('option:selected');
                if (!selectedOption.val()) {
                    return;
                }
                const nominal = selectedOption.data('nominal');
                const nama = selectedOption.data('nama');
                const parentTd = this.closest('td');
                const siblingNominalInput = parentTd.nextElementSibling.nextElementSibling.querySelector('.nominal-input');
                const siblingNamaInput = parentTd.nextElementSibling.querySelector('.nama-tagihan-input');
                siblingNominalInput.value = nominal.toLocaleString('id-ID');
                siblingNamaInput.value = nama;
            });

            $('#' + mainElementId.replace(/([[\]])/g, '\\$1')).select2();

        });
    </script>
@endsection
