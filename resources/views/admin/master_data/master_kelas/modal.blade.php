<meta name="csrf-token" content="{{ csrf_token() }}" xmlns="http://www.w3.org/1999/html">

<form id="deleteForm" class="mainForm">
    <div class="modal modal-blur fade" id="modal-delete" tabindex="-1" role="dialog" aria-hidden="true"
         data-bs-backdrop="static">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-status bg-danger"></div>
                <div class="modal-header ">
                    <div class="modal-title">
                        Hapus Data
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-capitalize text-center py-4">
                    <i class="ti ti-trash-x ti-3xl text-danger"></i>
                    <h3>Hapus Master Kelas?</h3>
                    <div class="text-secondary">
                        anda yakin akan menghapus data Master Kelas?
                    </div>

                    <input type="hidden" id="delete_id" name="delete_id" value="12">
                </div>
                <div class="modal-footer ">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <input type="reset" class="btn btn-outline-secondary w-100" value="Batal"
                                       data-bs-dismiss="modal">
                            </div>
                            <div class="col">
                                <input type="submit" value="Hapus Data" class="btn btn-danger w-100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<form id="editForm" class="mainForm">
    <div class="modal modal-blur fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true"
         data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-status bg-warning"></div>
                <div class="modal-header">
                    <div class="modal-title">
                        Edit Data Master Kelas
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-capitalize py-2">
                    <fieldset class="form-fieldset">
                        <div class="mb-3">
                            <label class="form-label required" for="edit-unit">Unit</label>
                            <input type="text" class="form-control" id="edit-unit" name="unit" autocomplete="off"
                                   placeholder="Unit" required>
                            <div class="invalid-feedback" role="alert">
                                <strong></strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required" for="edit-kelas">Kelas</label>
                            <input type="text" class="form-control" name="kelas" id="edit-kelas" autocomplete="off"
                                   placeholder="Kelas" required>
                            <div class="invalid-feedback" role="alert">
                                <strong></strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required" for="edit-kelompok">Kelompok</label>
                            <input type="text" class="form-control" id="edit-kelompok" name="kelompok"
                                   autocomplete="off"
                                   placeholder="Kelompok" required>
                            <div class="invalid-feedback" role="alert">
                                <strong></strong>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <input type="reset" value="Batal" class="btn btn-outline-secondary w-100"
                                       data-bs-dismiss="modal">
                            </div>
                            <div class="col">
                                <input type="submit" value="Simpan Data" class="btn btn-warning w-100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<form id="addForm" class="mainForm">
    <div class="modal modal-blur fade" id="modal-create" tabindex="-1" role="dialog" aria-hidden="true"
         data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Tambah Data Master Kelas
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <fieldset class="form-fieldset">
                        <div class="mb-3">
                            <label class="form-label required" for="unit">Unit</label>
                            <input type="text" class="form-control" id="unit" name="unit" autocomplete="off"
                                   placeholder="Unit" required>
                            <div class="invalid-feedback" role="alert">
                                <strong></strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required" for="kelas">Kelas</label>
                            <input type="text" class="form-control" name="kelas" id="kelas" autocomplete="off"
                                   placeholder="Kelas" required>
                            <div class="invalid-feedback" role="alert">
                                <strong></strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required" for="kelompok">Kelompok</label>
                            <input text class="form-control" id="kelompok" name="kelompok" autocomplete="off"
                                   placeholder="Kelompok" required>
                            <div class="invalid-feedback" role="alert">
                                <strong></strong>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <input type="reset" value="Batal" class="btn btn-outline-secondary w-100"
                                       data-bs-dismiss="modal">
                            </div>
                            <div class="col">
                                <input type="submit" value="Simpan Data" class="btn btn-primary w-100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<form id="formImport" enctype="multipart/form-data" class="mainForm"
      method="POST">
    <div class="modal modal-blur fade" id="modal-import" tabindex="-1" role="dialog" aria-hidden="true"
         data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Data Master Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            title="tutup"></button>
                </div>
                <div class="modal-body">
                    <ol class="mb-3">
                        <li>File harus berformat XLS/XLSX</li>
                        <li>Ukuran file tidak boleh lebih dari 2048KB/2MB</li>
                        <li>Contoh file yang dapat diproses untuk import:
                            @if(isset($templateImportExcel))
                                <a href="{!! $templateImportExcel !!}">
                                    <i class="ti ti-file-spreadsheet"></i>&nbsp;Contoh File
                                </a>
                            @endif
                        </li>
                    </ol>
                    <fieldset class="form-fieldset">

                        <div class="mb-3">
                            <label class="form-label required" for="file">File (.XLS, .XLSX)</label>
                            <input type="file" id="file" class=""
                                   accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                                   name="fileImport"
                                   placeholder="file" required>
                        </div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <input type="reset" value="Batal" class="btn btn-outline-secondary w-100"
                                       data-bs-dismiss="modal">
                            </div>
                            <div class="col">
                                <input type="submit" value="Import Data" class="btn btn-primary w-100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>


<link rel="stylesheet" href="{{asset('libs/filepond/dist/filepond.min.css')}}">
<link rel="stylesheet" href="{{asset('libs/filepond/dist/custom.css')}}">
<script
    src="{{asset('libs/filepond/plugin/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js')}}"></script>
<script
    src="{{asset('libs/filepond/plugin/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js')}}"></script>
<script src="{{asset('libs/filepond/dist/filepond.min.js')}}"></script>
<script src="{{asset('libs/filepond/dist/filepond.jquery.js')}}"></script>
<script>
    let id_action = '';

    function initializeFilePond(id) {
        let inputElement = document.querySelector('input#' + id);
        FilePond.create(inputElement, {
            credits: null,
            allowFileEncode: false,
            required: false,
            storeAsFile: true,
            labelIdle: 'Klik untuk membuka file manager, atau seret file ke dalam box ini.',
            allowFileTypeValidation: true,
            allowFileSizeValidation: true,
            labelMaxFileSizeExceeded: 'File terlalu besar',
            labelMaxFileSize: 'Ukuran maksimal file: {filesize}',
            labelFileTypeNotAllowed: 'Format file salah!',
            fileValidateTypeLabelExpectedTypes: 'file harus berformat .xls atau .xlsx',
            maxFileSize: 2048000,
        });
    }

    function clearErrorMessages(formId) {
        const form = document.querySelector(`#${formId}`);
        const errorElements = form.querySelectorAll('.invalid-feedback');
        const errorClass = form.querySelectorAll('.is-invalid');

        errorElements.forEach(element => element.textContent = '');
        errorClass.forEach(element => element.classList.remove('is-invalid'));
    }

    document.addEventListener("DOMContentLoaded", function () {

        FilePond.registerPlugin(
            FilePondPluginFileValidateType,
            FilePondPluginFileValidateSize,
        )

        initializeFilePond('file');

        $(document).on('keypress', '.formattedNumber', function (e) {
            const charCode = e.which ? e.which : e.keyCode;
            if (charCode < 48 || charCode > 57) {
                e.preventDefault();
            }
        })

        $(document).on('input', '.formattedNumber', function (e) {
            const formattedValue = $(this).val();
            const parsedNumber = parseInt(formattedValue.replace(/\./g, ''));

            if (!isNaN(parsedNumber)) {
                const formattedString = parsedNumber.toLocaleString('id-ID');
                $(this).val(formattedString);
            } else {

            }
        });

        $('.mainForm').on('submit', function (e) {
            e.preventDefault()

            loadingAlert()

            let url
            let tipe
            const formId = $(this).attr('id');
            let data

            if (formId === "deleteForm") {
                url = '{{route('admin.master-data.master-kelas.destroy',':id')}}'
                url = url.replace(':id', id_action)
                tipe = 'DELETE';
                data = $(this).serialize();
            } else if (formId === "editForm") {
                url = '{{route('admin.master-data.master-kelas.update',':id')}}'
                url = url.replace(':id', id_action)
                tipe = 'PUT';
                data = $(this).serialize();
            } else if (formId === "addForm") {
                url = '{{route('admin.master-data.master-kelas.store')}}'
                tipe = 'POST';
                data = $(this).serialize();
            } else if (formId === "formImport") {
                {{--                url = '{{route('admin.master-data.master-kelas.import')}}'--}}
                    tipe = 'POST';
                data = new FormData(this);
            }

            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            // console.log(url);
            let ajaxOptions = {
                url: url,
                type: tipe,
                data: data,
                datatype: 'json',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
            }

            if (formId === "formImport") {
                ajaxOptions.contentType = false;
                ajaxOptions.processData = false;
            }

            // console.log(ajaxOptions)
            clearErrorMessages(formId)
            $.ajax(ajaxOptions).done(function (responses) {
                document.getElementById(formId).reset();
                successAlert(responses.message);
                dataReload('main_table');
                $("#" + $(e.target).attr('id')).find('[data-bs-dismiss="modal"]').trigger('click')
            }).fail(function (xhr) {
                if (xhr.status === 422) {
                    const errors = JSON.parse(xhr.responseText).error
                    const errMessage = xhr.responseJSON.message
                    errorAlert(errMessage)
                    if (errors) {
                        for (const [key, value] of Object.entries(errors)) {
                            console.log(key + ': ', value[0]);
                            const field = $(`[name="${key}"]`);
                            field.addClass('is-invalid');
                            field.next('.invalid-feedback').html(value[0]);

                            if (key === 'password') {
                                const confirm = $(`[name="${key + '_confirmation'}"]`);
                                confirm.addClass('is-invalid');
                            }
                        }
                    }
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
        })

        $('#modal-create').on('show.bs.modal', function (e) {
            const formId = this.id
            $(this).parent().trigger('reset')
            // initTomSelect('tipe')
            clearErrorMessages(formId)
        })

        $('#modal-import').on('hidden.bs.modal', function () {
            const filename = 'Silahkan pilih file data untuk di import';
            $('#filename').text(filename);
        })

        $('#modal-delete').on('show.bs.modal', function (e) {
            let data = $(e.relatedTarget).data('val')
            id_action = data.item_id;
            $("#delete_id").val(id_action)
        })

        $('#modal-edit').on('show.bs.modal', function (e) {
            const formId = $(this).parent().attr('id');
            clearErrorMessages(formId)
            let data = $(e.relatedTarget).data('val')
            id_action = data.item_id
            $.each(data, function (key, value) {
                let input = $("#edit-" + key);
                input.val(value);
            });
        })

        $('#modal-detail').on('show.bs.modal', function (e) {
            let data = $(e.relatedTarget).data('val')

            $.each(data, function (key, value) {
                let input = $("#detail-" + key);
                input.val(value);
            });
        })
        // initTomSelect('tipe')
        // initTomSelect('edit-tipe')
    })
</script>
