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
            <div class="card-header-elements ms-auto">
                <div class="d-flex justify-content-center justify-content-md-end gap-4">

                </div>
            </div>
        </div>

        <div class="card-body">
            <form id="filterForm">
                <fieldset class="form-fieldset">
                    <h5>Filter</h5>
                    <div class="row row-cols-lg-2 row-cols-1">
                        <div class="col mb-5">
                            <label class="form-label" for="filter[tahun_akademik]">Tahun Angkatan</label>
                            <select class="form-select" id="filter[tahun_akademik]" name="filter[tahun_akademik]"
                                    data-control="select2" data-placeholder="Pilih Tahun Akademik">
                                <option value="all" {{ request('filter.tahun_akademik') == 'all' ? 'selected' : '' }}>Semua</option>
                                @isset($thn_aka)
                                    @foreach($thn_aka as $item)
                                        <option value="{{$item->urut}}"
                                            {{ request('filter.tahun_akademik') == $item->urut ? 'selected' : '' }}>
                                            {{$item->thn_aka}}
                                        </option>
                                    @endforeach
                                @else
                                    <option>data kosong</option>
                                @endisset
                            </select>
                        </div>

                        <div class="col mb-5">
                            <label class="form-label" for="filter[kelas]">Kelas</label>
                            <div class="row d-flex align-items-center">
                                <div class="col-6">
                                    <select class="form-select" id="jenjang" name="jenjang"
                                            data-control="select2" data-placeholder="Pilih Jenjang">
                                        @isset($jenjang)
                                            @foreach($jenjang as $item)
                                                <option
                                                    value="{{$item->jenjang}}" {{$item->jenjang == "XII" ? 'selected':''}}>{{$item->jenjang}}</option>
                                            @endforeach
                                        @else
                                            <option>data kosong</option>
                                        @endisset
                                    </select>
                                </div>
                                <div class="col-6">
                                    <select class="form-select" id="kelas" name="kelas"
                                            data-control="select2" data-placeholder="Pilih Kelas">
                                        @isset($kelas)
                                            @foreach($kelas as $item)
                                                <option
                                                    value="{{$item->kelas}}" {{$item->kelas == "MIPA 2" ? 'selected':''}}> {{$item->kelas}}</option>
                                            @endforeach
                                        @else
                                            <option>data kosong</option>
                                        @endisset
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col mb-5">
                            <label class="form-label" for="filter[kode_akun]">Kode Akun</label>
                            <select class="form-select" id="filter[kode_akun]" name="filter[kode_akun]" data-control="select2" data-placeholder="Pilih Kode Akun">
                                <option value="all" {{ request('filter.kode_akun') == 'all' ? 'selected' : '' }}>Semua</option>
                                @isset($tagihan)
                                    @foreach($tagihan as $item)
                                        <option value="{{$item->KodeAkun}}"
                                            {{ request('filter.kode_akun') == $item->KodeAkun ? 'selected' : '' }}>
                                            {{$item->KodeAkun}} - {{$item->NamaAkun}}
                                        </option>
                                    @endforeach
                                @else
                                    <option>data kosong</option>
                                @endisset
                            </select>
                        </div>

                        <div class="col mb-5">
                            <label class="form-label" for="filter[nominal]">Nominal</label>
                            <input type="text" class="form-control" name="filter[nominal]">
                        </div>
                    </div>
                    <div class="row">
                        <div class="d-flex justify-content-center justify-content-md-end gap-4">
                            <button type="reset" class="btn btn-secondary">
                                <span class="ri-reset-left-line me-2"></span>
                                Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <span class="ri-search-line me-2"></span>
                                Cari
                            </button>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
        <div class="card-datatable table-responsive text-nowrap">
            <table class="table table-sm table-bordered table-hover"
                   id="main_table">
                <thead class="table-light">

                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{asset('main/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
    <script src="{{asset('js/datatableCustom/Datatable-0-4.min.js')}}"></script>
    <script src="{{asset('main/libs/select2/select2.min.js')}}"></script>

    <script type="text/javascript">
        const select2 = $(`[data-control='select2']`);

        let dtOptions = {
            tableId: 'main_table',
            formId: 'filterForm',
            columnUrl: '{{($columnsUrl??null)}}',
            dataUrl: '{{($datasUrl??null)}}',
            dataColumns: [],
            thead: true,
            tfoot: true,
            paging: true,
            searching: true,
            fixedHeader: false,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 75, 100],
            info: false,
            scrollX: false,
            serverSide: true,
            select: false,
            scrollY: false,
            buttons: ['copy', 'excel', 'pdf','print'],
            columnSearch: true,
        };

        function updateFilterWindowLocation(form){
            let baseUrl = window.location.origin + window.location.pathname;
            let queryParams = $.param($(`#${form}`).serializeArray().reduce(function (acc, curr) {
                if (curr.value !== '') {
                    acc[curr.name] = curr.value;
                }
                return acc;
            }, {}));
            let newUrl = baseUrl + '?' + queryParams;
            window.history.pushState(null, '', newUrl);
        }


        document.addEventListener("DOMContentLoaded", function () {
            if (dtOptions.dataUrl && dtOptions.columnUrl) {
                getDT(dtOptions);
                if (dtOptions.formId) {
                    let filterForm = $(`#${dtOptions.formId}`);
                    filterForm.on('submit', function (e) {
                        e.preventDefault();
                        dataReload(dtOptions.tableId);
                    });
                    filterForm.on('reset', function (e) {
                        setTimeout(function () {
                            dataReload(dtOptions.tableId);
                            const select2InForm = select2.filter(`#${dtOptions.formId} [data-control='select2']`);
                            if (select2InForm.length) {
                                select2InForm.each(function () {
                                    let $this = $(this);
                                    $this.trigger('change');
                                });
                            }
                            updateFilterWindowLocation(dtOptions.formId);
                            dataReFilter(dtOptions.tableId);
                        }, 0)
                    });
                }
            }
            if (select2.length) {
                select2.each(function () {
                    let $this = $(this);
                    // select2Focus($this);
                    $this.wrap('<div class="position-relative"></div>').select2({
                        placeholder: 'Select value',
                        language: 'id',
                        dropdownParent: $this.parent()
                    });
                });
            }
        });

    </script>

    {!! ($modalLink??'') !!}
@endsection
