@extends('layouts.admin_new')
@section('style')
    <link rel="stylesheet" href="{{asset('main/libs/datatables-bs5/datatables.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('main/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('main/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
@endsection
@section('content')
    <h3 class="page-heading d-flex text-gray-900 fw-bold flex-column justify-content-center my-0">
        {{($dataTitle??($mainTitle??($title??'')))}}
    </h3>
    <ul class="breadcrumb breadcrumb-style2">
        <li class="breadcrumb-item">
            <a href="{{ route('admin.index') }}" class="text-hover-primary">Beranda</a>
        </li>

        @isset($title)
            <li class="breadcrumb-item">{{ $title }}</li>
        @endisset

        @isset($mainTitle)
            <li class="breadcrumb-item">{{ $mainTitle }}</li>
        @endisset

        @if(isset($dataTitle) && isset($mainTitle) && $mainTitle !== $dataTitle)
            <li class="breadcrumb-item {{$showTitle??'active'}}">
                @if(isset($indexUrl))
                    <a href="{{ $indexUrl }}" class="text-hover-primary">{{ $dataTitle }}</a>
                @else
                    {{ $dataTitle }}
                @endif
            </li>

            @isset($showTitle)
                <li class="breadcrumb-item active">{{ $showTitle }}</li>
            @endisset
        @endif
    </ul>

    <div class="card">
        <div class="card-header header-elements">
            <h5 class="mb-0 me-2">{{($dataTitle??$mainTitle)}}</h5>
            <div class="card-header-elements ms-auto">
                <div class="w-100">
                    <div class="row">
                        <div class="d-flex justify-content-center justify-content-md-end gap-4">
                            @if(isset($btnModalImport))
                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                        data-bs-target="#modal-import" title="Import Data">
                                    <span class="ri-upload-2-line me-2"></span>Import Data
                                </button>
                            @endif
                            @if(isset($btnModalExport))
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#modal-download" title="Download Data">
                                    <span class="ri-download-2-line me-2"></span>Download Data
                                </button>
                            @endif
                            @if(isset($btnModalCreate))
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#modal-create" title="Buat Data">
                                    <span class="ri-add-line me-2"></span>
                                    Buat Data
                                </button>
                            @endif
                            @if(isset($btnLinkImport))
                                <button type="button" class="btn btn-success" href="{{$btnLinkImport}}"
                                        title="Import Data">
                                    <span class="ri-upload-2-line me-2"></span>Import Data
                                </button>
                            @endif
                            @if(isset($btnLinkExport))
                                <button type="button" class="btn btn-primary" href="{{$btnLinkExport}}"
                                        title="Download Data">
                                    <span class="ri-download-2-line me-2"></span>Download Data
                                </button>
                            @endif
                            @if(isset($btnLinkCreate))
                                <button type="button" class="btn btn-primary" href="{{$btnLinkCreate}}"
                                        title="Buat Data">
                                    <span class="ri-add-line me-2"></span>
                                    Buat Data
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
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

    <script type="text/javascript">
        const dtOptions = {
            tableId: 'main_table',
            formId: false,
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
        };

        document.addEventListener("DOMContentLoaded", function () {
            if (dtOptions.dataUrl && dtOptions.columnUrl) {
                getDT(dtOptions);
                if (dtOptions.formId) {
                    let filterForm = $(`#${dtOptions.formId}`);
                    filterForm.on('submit', function (e) {
                        e.preventDefault();
                        dataReFilter(dtOptions.tableId);
                    });
                    filterForm.on('reset', function (e) {
                        setTimeout(function () {
                            dataReFilter(dtOptions.tableId);
                            const select2InForm = select2.filter(`#${dtOptions.formId} [data-control='select2']`);
                            if (select2InForm.length) {
                                select2InForm.each(function () {
                                    let $this = $(this);
                                    $this.trigger('change');
                                });
                            }
                        }, 0)
                    });
                }
            }
        });

    </script>

    {!! ($modalLink??'') !!}
@endsection
