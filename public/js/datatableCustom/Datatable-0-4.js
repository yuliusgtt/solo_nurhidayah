function debounce(func, delay) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
    };
}

function reformatNumber(data, row, column, node) {
    // replace spaces with nothing; replace commas with points.
    if (column === 1 ) {
        return data.replace(',', '.').replaceAll(' ', '');
    } else {
        return data;
    }
}

// function addCustomNumberFormat(xlsx, numberFormat) {
//     let numFmtsElement = xlsx.xl['styles.xml'].getElementsByTagName('numFmts')[0];
//     let numFmtElement = '<numFmt numFmtId="176" formatCode="' + numberFormat + '"/>';
//     $( numFmtsElement ).append( numFmtElement );
//     $( numFmtsElement ).attr("count", "7");
//
//     let celXfsElement = xlsx.xl['styles.xml'].getElementsByTagName('cellXfs');
//     let cellStyle = '<xf numFmtId="176" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"'
//         + ' applyFont="1" applyFill="1" applyBorder="1"/>';
//     $( celXfsElement ).append( cellStyle );
//     $( celXfsElement ).attr("count", "69");
// }

function addCustomNumberFormat(xlsx, numberFormat) {

    //kodingan seko stackoverflow ramudeng njir
    let numFmtsElement = xlsx.xl['styles.xml'].getElementsByTagName('numFmts')[0];
    let celXfsElement = xlsx.xl['styles.xml'].getElementsByTagName('cellXfs')[0];

    // Define the Rupiah custom format
    const rupiahFormat = 'Rp.\\ #,##0;[Red]Rp.\\ -#,##0';

    // Check if `numFmts` already exists, otherwise create it
    if (!numFmtsElement) {
        const stylesXml = xlsx.xl['styles.xml'];
        const newNumFmtsElement = stylesXml.createElement('numFmts');
        newNumFmtsElement.setAttribute('count', '1');
        stylesXml.documentElement.getElementsByTagName('styleSheet')[0].appendChild(newNumFmtsElement);
        numFmtsElement = newNumFmtsElement;
    }

    // Add the custom number format
    const numFmtElement = xlsx.xl['styles.xml'].createElement('numFmt');
    numFmtElement.setAttribute('numFmtId', '176'); // Ensure this ID is not already used
    numFmtElement.setAttribute('formatCode', rupiahFormat);
    numFmtsElement.appendChild(numFmtElement);

    // Update the count attribute
    const currentNumFmtsCount = parseInt(numFmtsElement.getAttribute('count') || '0', 10);
    numFmtsElement.setAttribute('count', currentNumFmtsCount + 1);

    // Add a new cell style using the custom format
    const cellStyle = '<xf numFmtId="176" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>';
    celXfsElement.innerHTML += cellStyle;

    // Update the count attribute for `cellXfs`
    const currentCellXfsCount = parseInt(celXfsElement.getAttribute('count') || '0', 10);
    celXfsElement.setAttribute('count', currentCellXfsCount + 1);
}


function formatTargetColumn(xlsx, col) {
    let sheet = xlsx.xl.worksheets['sheet1.xml'];
    $( 'row c[r^="' + col + '"]', sheet ).attr( 's', '68' );
}


function newexportaction(e, dt, button, config) {
    let self = this;
    let oldStart = dt.settings()[0]._iDisplayStart;
    let maxLength = dt.settings()[0]._iRecordsDisplay;
    dt.one('preXhr', function (e, s, data) {
        data.start = 0;
        data.length = maxLength;
        dt.one('preDraw', function (e, settings) {
            if (button[0].className.indexOf('buttons-copy') >= 0) {
                $.fn.dataTable.ext.buttons.copyHtml5.action.call(self, e, dt, button, config);
            } else if (button[0].className.indexOf('buttons-excel') >= 0) {
                $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config) ?
                    $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config) :
                    $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
            } else if (button[0].className.indexOf('buttons-csv') >= 0) {
                $.fn.dataTable.ext.buttons.csvHtml5.available(dt, config) ?
                    $.fn.dataTable.ext.buttons.csvHtml5.action.call(self, e, dt, button, config) :
                    $.fn.dataTable.ext.buttons.csvFlash.action.call(self, e, dt, button, config);
            } else if (button[0].className.indexOf('buttons-pdf') >= 0) {
                $.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config) ?
                    $.fn.dataTable.ext.buttons.pdfHtml5.action.call(self, e, dt, button, config) :
                    $.fn.dataTable.ext.buttons.pdfFlash.action.call(self, e, dt, button, config);
            } else if (button[0].className.indexOf('buttons-print') >= 0) {
                $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
            }
            dt.one('preXhr', function (e, s, data) {
                settings._iDisplayStart = oldStart;
                data.start = oldStart;
            });
            setTimeout(dt.ajax.reload, 0);
            return false;
        });
    });
    dt.ajax.reload();
}

function dtButtons(options, buttons) {
    // Button configurations
    const buttonConfigMap = {
        copy: {
            extend: 'copy',
            title: '',
            text: '<i class="ri ri-file-copy-2-line me-2"></i>Copy',
        },
        excel: {
            extend: 'excel',
            title: '',
            text: '<i class="ri ri-file-excel-line me-2"></i>Excel',
            customize: function (xlsx) {
                const sheet = xlsx.xl.worksheets['sheet1.xml'];

                const currencyColumns = [];
                DT[`${options.tableId}`].settings().init().columns.forEach((col, index) => {
                    if (col.columnType === 'currency' || col.columnType === 'money' || col.columnType === 'rupiah') {
                        currencyColumns.push(index);
                    }
                });

                $('row c[r]', sheet).each(function () {
                    const cell = $(this);
                    const cellRef = cell.attr('r');
                    const columnIndex = cellRef.replace(/[0-9]/g, '').charCodeAt(0) - 65; // Get column index

                    if (currencyColumns.includes(columnIndex)) {
                        // console.log(columnIndex, cellRef)
                        addCustomNumberFormat(xlsx, '#,##0.##');
                        formatTargetColumn(xlsx, cellRef);
                        // // Apply a custom number format for currency: $#,##0.00
                        // const numFmtId = '164';  // Custom format ID for currency ($#,##0.00)
                        // cell.attr('s', '56'); // Apply a predefined style ID for currency (if necessary)
                        // cell.attr('t', 'n'); // Set the type to number for correct formatting
                    }
                });

                $('row:first c', sheet).attr('s', '22');
                const styles = xlsx.xl['styles.xml'];
                $(styles).find('cellXfs xf').eq(21).attr('fillId', '3');
                $(styles).find('fills fill').eq(3).html('<patternFill patternType="solid"><fgColor rgb="FFFFF00"/></patternFill>');
            },
        },
        pdf: {
            extend: 'pdf',
            title: '',
            text: '<i class="ri ri-file-pdf-2-line me-2"></i>Pdf',
            modifier: {page: 'all'}
        },
        csv: {
            extend: 'csv',
            title: '',
            text: '<i class="ri ri-file-list-2-line me-2"></i>Csv',
        },
        print: {
            extend: 'print',
            title: '',
            text: '<i class="ri ri-printer-line me-2"></i>Print',
        },
    };

    const buttonConfigs = buttons.map(button => buttonConfigMap[button]).filter(Boolean);

    return buttonConfigs.map(config => ({
        ...config,
        className: 'dropdown-item',
        exportOptions: {
            columns: function (index, data, node) {
                let exportableColumn = options.dataColumns[index]?.exportable;
                return exportableColumn === true;
            }, format: {
                header: function (data, columnIdx) {
                    return data.toUpperCase();
                },
                body: function (data, row, column, node) {
                    if (data === null) return '';
                    const exportableColumns = options.dataColumns.filter(col => col.exportable === true);
                    const columnInfo = exportableColumns[column];
                    let columnType = columnInfo.columnType;

                    const numberColumn = columnInfo.numberColumn;
                    // let rawData = table.row(row).data();
                    // console.log(rawData)
                    // console.log(exportableColumns)

                    if (columnType !== null) {
                        switch (columnType.toLowerCase()) {
                            case 'row':
                            case 'number':
                            case 'no':
                                return row + 1;
                            case 'basicdate':
                                let basicDate = new Date(data);
                                let basicDateOptions = {
                                    day: 'numeric',
                                    month: 'long',
                                    year: 'numeric'
                                };
                                return basicDate.toLocaleDateString('id-ID', basicDateOptions);
                            case 'date':
                            case 'dateformat':
                                let date = new Date(data);
                                let options = {
                                    weekday: 'long',
                                    day: 'numeric',
                                    month: 'long',
                                    year: 'numeric'
                                };
                                return date.toLocaleDateString('id-ID', options);
                            case 'boolean':
                                let columnData = columnInfo.data;
                                columnData = columnData.toLowerCase();
                                if (columnData === 'cicil') {
                                    return data === 1 ? 'CICILAN' : 'BUKAN CICILAN';
                                } else if (columnData === 'paidst') {
                                    return data === 1 ? 'LUNAS' : 'BELUM LUNAS';
                                }
                                return data;
                            case 'currency':
                                if (config.extend !== 'excel'){
                                    return new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR',
                                    }).format(data);
                                }
                                return data;
                        }

                    }
                    if (config.extend === "excel" && !numberColumn) {
                        return "\0" + data;
                    }
                    if (data.length <= 0) return data
                    // if (config.extend === "excel" && data.length >= 10 && !isNaN(parseFloat(data)) && isFinite(data)) {
                    //     return "\0" + data;
                    // }
                    let el = $.parseHTML(data);
                    let result = '';
                    $.each(el, function (index, item) {
                        if (item.classList && item.classList.contains('user-name')) {
                            result += item.lastChild.firstChild.textContent;
                        } else {
                            result += item.innerText || item.textContent;
                        }
                    });
                    return result;
                }
            },
            modifier: config.modifier,
            orthogonal: 'export'
        },
        action: newexportaction
    }));
}

function formatColumnName(columnName) {
    return columnName.replace(/_/g, ' ').replace(/\b\w/g, match => match.toUpperCase());
}

function createColumnsHtml(columns) {
    return columns.map(column => `<th>${formatColumnName(column.name)}</th>`).join('');
}

function createColumns(id, columns, location) {
    const table = document.getElementById(id);
    let headerOrFooter = table.querySelector(location);
    if (!headerOrFooter) {
        headerOrFooter = document.createElement(location);
        headerOrFooter.classList.add('table-light');
        table.appendChild(headerOrFooter);
    }
    headerOrFooter.innerHTML = '';
    const row = document.createElement('tr');
    row.classList.add('text-start', 'fw-bold', 'fs-7', 'text-uppercase', 'gs-0');
    row.innerHTML = createColumnsHtml(columns);
    headerOrFooter.appendChild(row);
}

let DT = {};
const languageKey = 'datatables_id_language';
const languageUrl = 'https://cdn.datatables.net/plug-ins/2.0.6/i18n/id.json';
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
async function dataTableCreate(options) {
    let idTable = $(`#${options.tableId}`);
    let searchPanel = [];
    let buttonsConfig = Array.isArray(options.buttons) && options.buttons.length > 0 ? options.buttons : false;
    const buttonDom = `${(buttonsConfig ? '<"row pb-3"<"dt-action-buttons d-flex justify-content-center justify-content-md-end px-5 px-md-3"B>">' : '')}`;
    const dom = `${buttonDom}<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>><"row dt-row"<"table-responsive"t>r><"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>`;

    let languageData = localStorage.getItem(languageKey);

    if (!languageData) {
        languageData = await fetchLanguageFile();
    } else {
        languageData = JSON.parse(languageData);
    }

    DT[`${options.tableId}`] = idTable.DataTable({
        autoWidth: false,
        responsive: false,
        columns: options.dataColumns,
        fixedHeader: options.fixedHeader ?? false,
        scrollX: options.scrollX ?? false,
        searching: options.searching || false,
        processing: true,
        serverSide: true,
        paging: options.paging ?? true,
        pageLength: options.pageLength ?? 10,
        lengthMenu: options.lengthMenu ?? [10, 25, 50, 75, 100],
        retrieve: options.retrieve ?? false,
        select: options.select
            ? options.select === 'multi'
                ? {
                    style: 'multi',
                    selector: 'td:not(.exclude-selection)'
                }
                : {
                    style: 'os',
                    selector: 'td:not(.exclude-selection)',
                }
            : false,
        columnDefs: [
            {
                targets: 0,
                searchable: false,
                orderable: false,
                className: options.select ? '' : ' table_dt_no',
                checkboxes: options.select ? {
                    selectRow: true,
                    selectAllRender: '<input type="checkbox" class="form-check-input select-all">'
                } : false,
            }
        ],
        buttons: buttonsConfig && [
            {
                extend: 'collection',
                className: 'btn btn-vimeo dropdown-toggle me-2',
                text: '<i class="ri ri-export-line me-2"></i>Export',
                buttons: [
                    dtButtons(options, buttonsConfig)
                ],
            }
        ],
        language: {
            ...languageData,
            processing: 'Memuat Data...'
        },
        dom: dom,
        ajax: {
            url: options.dataUrl,
            type: "GET",
            data: function (d) {
                if (options.formId) {
                    let transformedData = options.formId ? $(`#${options.formId}`).serializeArray().reduce((acc, {
                        name,
                        value
                    }) => {
                        if (acc[name]) {
                            if (!Array.isArray(acc[name])) {
                                acc[name] = [acc[name]];
                            }
                            if (Array.isArray(value)) {
                                acc[name] = acc[name].concat(value);
                            } else {
                                acc[name].push(value);
                            }
                        } else {
                            acc[name] = value;
                        }
                        return acc;
                    }, {}) : {};
                    return $.extend({}, d, transformedData);
                }
            }, error: function (xhr, error, code) {
                const descriptions = {
                    '401': 'Sesi anda telah habis, silahkan login kembali!',
                    '404': 'Data tidak ditemukan!',
                    '500': 'Internal Server Error',
                };
                errorAlert(descriptions[xhr.status] || 'Ada masalah saat mengambil data dari server, Silahkan muat ulang halaman');
            }
        },
        preDrawCallback: function (settings) {
            if (options.formId) {
                let submitButton = $(`#${options.formId} input[type="submit"], #${options.formId} button[type="submit"]`);
                let resetButton = $(`#${options.formId} input[type="reset"], #${options.formId} button[type="reset"]`);

                if (submitButton.length !== 0) {
                    const buttonHtml = submitButton.hasClass('btn-bayar')
                        ? `<span class="spinner-border me-2" role="status" aria-hidden="true">Bayar`
                        : `<span class="spinner-border me-2" role="status" aria-hidden="true"></span>Cari`;
                    submitButton.html(buttonHtml).prop('disabled', true);
                }
                if (resetButton.length !== 0) {
                    resetButton.prop('disabled', true);
                    resetButton.html(`<span class="spinner-border me-2" role="status" aria-hidden="true"></span>Reset`);
                }
            }
        },
        createdRow: function (row, data, dataIndex) {
            $(row).find('td').each(function (cellIndex) {
                const columnConfig = options.dataColumns[cellIndex];
                if (columnConfig.excludeFromSelection) {
                    $(this).addClass('exclude-selection'); // Add a class to exclude
                }
            });
        },
        drawCallback: function (settings) {
            let labelNo = $(idTable.DataTable().table().header()).find('th').eq(0);
            labelNo && labelNo.removeClass('sorting_asc');

            if (options.formId) {
                let submitButton = $(`#${options.formId} input[type="submit"], #${options.formId} button[type="submit"]`);
                let resetButton = $(`#${options.formId} input[type="reset"], #${options.formId} button[type="reset"]`);
                if (submitButton.length !== 0) {
                    const buttonHtml = submitButton.hasClass('btn-bayar')
                        ? `<span class="ri-cash-line me-2"></span>Bayar`
                        : `<span class="ri-search-line me-2"></span>Cari`;
                    submitButton.html(buttonHtml).prop('disabled', false);
                }
                if (resetButton.length !== 0) {
                    resetButton.html(`<span class="ri-reset-left-line me-2"></span>Reset`);
                    resetButton.prop('disabled', false);
                }
            }
        },
        initComplete: function (data) {
            //// for fixed header only
            if (options.fixedHeader) {
                if (window.Helpers.isNavbarFixed()) {
                    let navHeight = $('#layout-navbar').outerHeight();
                    new $.fn.dataTable.FixedHeader($(idTable).dataTable()).headerOffset(navHeight);
                } else {
                    new $.fn.dataTable.FixedHeader($(idTable).dataTable());
                }

                if (options.scrollX) {
                    let isHeaderRestored = false;
                    $(window).on('scroll', function () {
                        let fixedHeaderElement = $('.fixedHeader-floating');
                        let tableHeaderOffset = $(`#${options.tableId} thead`).offset().top;
                        let scrollPosition = $(window).scrollTop();

                        if (scrollPosition <= tableHeaderOffset) {
                            if (!fixedHeaderElement.is(':visible') && !isHeaderRestored) {
                                setTimeout(function () {
                                    idTable.DataTable().columns.adjust()
                                }, 250)
                                isHeaderRestored = true;
                            }
                        } else {
                            if (isHeaderRestored) {
                                isHeaderRestored = false;
                            }
                        }
                    });
                }
            }

            setTimeout(function () {
                let labelNo = $(idTable.DataTable().table().header()).find('th').eq(0);
                labelNo && labelNo.removeClass('sorting_asc');

                searchPanel[options.tableId] = $(`#${options.tableId}_filter input`);
                if (searchPanel[options.tableId]) {
                    searchPanel[options.tableId].unbind();
                    searchPanel[options.tableId].bind().on('keyup', debounce(function () {
                        idTable.DataTable().search(this.value).draw();
                    }, 500));
                }
            }, 0)
        },
        error: function (xhr, error, code) {
            errorAlert('Data tidak dapat dimuat')
        }
    })
}

function dataReload(id = null) {
    id && DT[`${id}`].ajax.reload(null, false);
}

function dataReFilter(id = null, formId = null) {
    id && $(`#${id}`).DataTable().draw();
    // if (id) {
    //     const tableId = $(`#${id}`);
    //     tableId.DataTable().draw();
    // }
}

async function getDT(options) {
    if (options.columnUrl && options.dataColumns) {
        $.ajax({
            url: options.columnUrl,
            success: function (data) {
                $.each(data, function (index, column) {
                    let columnType;
                    let renderFunc = '';
                    if (column.columnType || column.columntype) {
                        columnType = column.columnType || column.columntype;
                        switch (columnType.toLowerCase()) {
                            case 'row':
                            case 'number':
                            case 'no':
                                renderFunc = function (data, type, row, meta) {
                                    if (type === 'display' || type === 'filter') {
                                        return meta.row + meta.settings._iDisplayStart + 1;
                                    } else if (type === 'export') {
                                        return meta.row;
                                    }
                                    return data;
                                }
                                break;
                            case 'suffix':
                                renderFunc = function (data, type, row) {
                                    if (type === 'display' || type === 'filter') {
                                        return data + ' ' + column.suffix;
                                    }
                                    return data;
                                };
                                break;
                            case 'prefix':
                                renderFunc = function (data, type, row) {
                                    if (type === 'display' || type === 'filter') {
                                        return column.prefix + ' ' + data;
                                    }
                                    return data;
                                };
                                break;
                            case 'money':
                            case 'currency':
                                renderFunc = function (data, type, row) {
                                    if (type === 'display' || type === 'filter') {
                                        if (data === null || data === 0) {
                                            return 'Rp. 0';
                                        }
                                        return $.fn.dataTable.render.number('.', ',', 0, 'Rp. ').display(data);
                                    }
                                    return data;
                                };
                                break;
                            case 'basicdate':
                                renderFunc = function (data, type, row) {
                                    if (type === 'display' || type === 'filter') {
                                        let date = new Date(data);
                                        let options = {
                                            day: 'numeric',
                                            month: 'long',
                                            year: 'numeric'
                                        };
                                        return date.toLocaleDateString('id-ID', options);
                                    }
                                    return data;
                                };
                                break;
                            case 'date':
                            case 'dateformat':
                                renderFunc = function (data, type, row) {
                                    if (type === 'display' || type === 'filter') {
                                        let date = new Date(data);
                                        let options = {
                                            weekday: 'long',
                                            day: 'numeric',
                                            month: 'long',
                                            year: 'numeric'
                                        };
                                        return date.toLocaleDateString('id-ID', options);
                                    }
                                    return data;
                                };
                                break;
                            case 'periode':
                            case 'yearmonth':
                                renderFunc = function (data, type, row) {
                                    if (!data || typeof data !== 'string' || data.length !== 6 || !/^\d{6}$/.test(data)) {
                                        return '';
                                    }
                                    const monthsIndonesian = [
                                        "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                                        "Juli", "Agustus", "September", "Oktober", "November", "Desember"
                                    ];
                                    const year = Math.floor(data / 100);
                                    const month = data % 100;
                                    return (month >= 1 && month <= 12) ? `${monthsIndonesian[month - 1]} ${year}` : '';
                                };
                                break;
                            case 'timestamp':
                                renderFunc = function (data, type, row) {
                                    if (type === 'display' || type === 'filter') {
                                        let date = new Date(data);
                                        let options = {
                                            weekday: 'long',
                                            day: 'numeric',
                                            month: 'long',
                                            year: 'numeric',
                                            hour: 'numeric',
                                            minute: 'numeric'
                                        };
                                        return date.toLocaleDateString('id-ID', options);
                                    }
                                    return data;
                                };
                                break;
                            case 'button':
                                renderFunc = function (data, type, row) {
                                    if (!data || !(type === 'display' || type === 'filter')) {
                                        return '';
                                    }

                                    const {
                                        buttonClass = 'btn',
                                        buttonIcon,
                                        buttonIconSVG,
                                        buttonText,
                                        noCaption,
                                        button,
                                        buttonLink,
                                    } = column;

                                    const iconStyle = buttonIcon ? `<i class="${buttonIcon}"></i>` : buttonIconSVG || '';
                                    const title = buttonText || '';
                                    const buttonTextContent = noCaption ? '' : buttonText;
                                    const rowDataJson = JSON.stringify(row).replace(/'/g, "&#39;").replace(/"/g, "&quot;");

                                    const createButton = (attributes, content) => `<button type="button" class="${buttonClass}" title="${title}" ${attributes}>${content}</button>`;

                                    switch (button) {
                                        case 'modal':
                                            return createButton(`data-bs-toggle="modal" data-bs-target="${buttonLink}" data-val='${rowDataJson}'`, `${iconStyle}${buttonTextContent}`);
                                        case 'link':
                                            const link = buttonLink ? buttonLink.replace(':id', row.item_id) : '#';
                                            return `<a class="${buttonClass}" href="${link}" title="${title}">${iconStyle}${buttonTextContent}</a>`;
                                        case 'action':
                                            return createButton(`data-val='${rowDataJson}'`, `${iconStyle}${buttonTextContent}`);
                                        default:
                                            return '';
                                    }
                                }
                                break;
                            case 'boolean':
                                renderFunc = function (data, type, row) {
                                    if (type === 'display' || type === 'filter') {
                                        let trueVal = column.trueVal ?? 'benar';
                                        let falseVal = column.falseVal ?? 'Salah';
                                        if (column.booleanCheck) {
                                            trueVal = '<i class="ri-check-line"></i>';
                                            falseVal = '<i class="ri-close-line"></i>';
                                        }
                                        if (data === "1" || data === 1 || data === true) {
                                            return `<span class="badge px-2 rounded-pill bg-label-success">${trueVal}</span>`
                                        } else {
                                            return `<span class="badge px-2 rounded-pill bg-label-danger">${falseVal}</span>`
                                        }
                                    }
                                    return data;
                                }
                                break;
                            case 'importstatus':
                                renderFunc = function (data, type, row) {
                                    if (type === 'display' || type === 'filter') {
                                        let saveVal = column.saveVal ?? 'Dapat Disimpan';
                                        let updateVal = column.updateVal ?? 'Update';
                                        let falseVal = column.falseVal ?? 'Tidak Dapat Disimpan';
                                        if (data === "1" || data === 1 || data === true) {
                                            return `<span class="badge px-2 rounded-pill bg-label-success">${saveVal}</span>`;
                                        } else if (data === "2" || data === 2) {
                                            return `<span class="badge px-2 rounded-pill bg-label-warning">${updateVal}</span>`;
                                        } else if (data === "0" || data === 0 || data === false) {
                                            return `<span class="badge px-2 rounded-pill bg-label-danger">${falseVal}</span>`;
                                        }
                                    }
                                    return data;
                                }
                                break;
                            case 'checkbox':
                                renderFunc = function (data, type, row) {
                                    if (type === 'display' || type === 'filter') {
                                        let name = column.selectName ? column.selectName : 'checkbox';
                                        return `<input type="checkbox" class="dt-checkboxes form-check-input" name="${column.selectName ? column.selectName : 'checkbox'}[]" value="${data}">`;
                                    }
                                    return data;
                                }
                                break;
                            case 'input':
                                //for text/number only
                                renderFunc = function (data, type, row) {
                                    if (type === 'display' || type === 'filter') {

                                        let attributes = [
                                            `type="${column.inputType ?? 'text'}"`,
                                            `placeholder="${column.inputPlaceholder ?? $column.name}"`,
                                            `name="${column.inputName ?? `input[${column.name}]`}"`,
                                            `class="${column.inputClass ?? 'form-control'}"`,
                                        ];

                                        const nameLength = column.inputPlaceholder ?? $column.name;

                                        if (nameLength.length > 0) {
                                            attributes.push(`style="width: 218.938px;"`)
                                        }

                                        if (column.inputReadonly === true) {
                                            attributes.push(`readonly`);
                                        }

                                        if (column.inputDisabled === true) {
                                            attributes.push(`disabled`);
                                        }

                                        if (Number.isInteger(column.inputMin)) {
                                            attributes.push(`min="${column.inputMin}"`);
                                        }

                                        if (Number.isInteger(column.inputMax)) {
                                            attributes.push(`max="${column.inputMax}"`);
                                        }

                                        return `<input ${attributes.join(' ')}>`;
                                    }
                                    return data;
                                }
                                break;
                            case 'custom_code_tagihan':
                                renderFunc = function (data, type, row) {
                                    const descriptions = {
                                        '1140000': 'Manual Cash',
                                        '1140001': 'Manual BMI',
                                        '1140002': 'Manual SALDO',
                                        '1140003': 'Transfer Bank Lain',
                                        '1140004': 'INFAQ',
                                        '1200001': 'Loket Manual - Beasiswa',
                                        '1200002': 'Loket Manual - Potongan',
                                        '1': 'Transfer Online',
                                        '4': 'Transfer Online',
                                        null: '',
                                        '': ''
                                    };
                                    return descriptions[data] || data;
                                }
                                break;
                        }
                    } else {
                        renderFunc = function (data, type, row) {
                            if (!data) {
                                return '';
                            }
                            return data;
                        }
                    }

                    options.dataColumns.push({
                        data: column.data,
                        name: column.name,
                        searchable: column.searchable ?? false,
                        orderable: column.orderable ?? false,
                        render: renderFunc ?? false,
                        className: column.className ?? false,
                        search: false,
                        exportable: column.exportable ?? false,
                        visible: column.visible ?? true,
                        excludeFromSelection: column.excludeFromSelection ?? false,
                        columnType: columnType ?? null,
                        numberColumn: column.numberColumn ?? false,
                    })
                })
                const locations = ['thead', 'tfoot'];

                locations.forEach(location => {
                    if (options[location]) {
                        createColumns(options.tableId, options.dataColumns, location);
                    }
                });
                dataTableCreate(options)
            }
        });
    } else {
        warningAlert('Data tidak dapat dimuat, silahkan muat ulang halaman')
    }
}


function mergeTableRows(tableSelector, columnIndex) {
    const table = $(tableSelector);
    let prevCell = null;
    let rowspan = 1;

    table.find(`tbody tr`).each(function () {
        const currentCell = $(this).find(`td:eq(${columnIndex})`);
        if (prevCell && currentCell.text() === prevCell.text()) {
            currentCell.hide();
            prevCell.attr('rowspan', ++rowspan);
        } else {
            prevCell = currentCell;
            rowspan = 1;
        }
    });
}
