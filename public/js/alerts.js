function errorAlert(Message) {
    const fillColor = '#ff4d49';
    const svgIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="${fillColor}"><path d="M12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22ZM12 20C16.4183 20 20 16.4183 20 12C20 7.58172 16.4183 4 12 4C7.58172 4 4 7.58172 4 12C4 16.4183 7.58172 20 12 20ZM12 10.5858L14.8284 7.75736L16.2426 9.17157L13.4142 12L16.2426 14.8284L14.8284 16.2426L12 13.4142L9.17157 16.2426L7.75736 14.8284L10.5858 12L7.75736 9.17157L9.17157 7.75736L12 10.5858Z"></path></svg>`;

    Swal.fire({
        html: Message,
        // icon: "error",
        imageUrl: 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgIcon),
        imageWidth: 100,
        imageHeight: 100,
        buttonsStyling: false,
        confirmButtonText: "Ok",
        // focusConfirm: true,
        didOpen: (popup) => {
            const okButton = popup.querySelector('.swal2-confirm');
            if (okButton) {
                okButton.focus();
            }
        },
        customClass: {
            confirmButton: "btn btn-danger"
        }
    });
}

function successAlert(Message) {
    const fillColor = '#72e128';
    const svgIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="${fillColor}"><path d="M4 12C4 7.58172 7.58172 4 12 4C16.4183 4 20 7.58172 20 12C20 16.4183 16.4183 20 12 20C7.58172 20 4 16.4183 4 12ZM12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM17.4571 9.45711L16.0429 8.04289L11 13.0858L8.20711 10.2929L6.79289 11.7071L11 15.9142L17.4571 9.45711Z"></path></svg>`;

    Swal.fire({
        html: Message,
        // icon: "success",
        imageUrl: 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgIcon),
        imageWidth: 100,
        imageHeight: 100,
        buttonsStyling: false,
        confirmButtonText: "Ok",
        // focusConfirm: true,
        customClass: {
            confirmButton: "btn btn-success"
        },
        didOpen: (popup) => {
            const okButton = popup.querySelector('.swal2-confirm');
            if (okButton) {
                okButton.focus();
            }
        },
    });
}

function warningAlert(Message) {
    const fillColor = '#fdb528';
    const svgIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="${fillColor}"><path d="M12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22ZM12 20C16.4183 20 20 16.4183 20 12C20 7.58172 16.4183 4 12 4C7.58172 4 4 7.58172 4 12C4 16.4183 7.58172 20 12 20ZM11 15H13V17H11V15ZM11 7H13V13H11V7Z"></path></svg>`;

    Swal.fire({
        html: Message,
        // icon: "warning",
        imageUrl: 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgIcon),
        imageWidth: 100,
        imageHeight: 100,
        buttonsStyling: false,
        confirmButtonText: "Ok",
        // focusConfirm: true,
        didOpen: (popup) => {
            const okButton = popup.querySelector('.swal2-confirm');
            if (okButton) {
                okButton.focus();
            }
        },
        customClass: {
            confirmButton: "btn btn-warning"
        }
    });
}

function infoAlert(Message = null) {
    Swal.fire({
        html: Message,
        icon: "info",
        buttonsStyling: false,
        confirmButtonText: "Ok",
        // focusConfirm: true,
        didOpen: (popup) => {
            const okButton = popup.querySelector('.swal2-confirm');
            if (okButton) {
                okButton.focus();
            }
        },
        customClass: {
            confirmButton: "btn btn-info"
        }
    });
}

function loadingAlert(Message = null) {
    // const htmlClass = document.documentElement.classList.contains('dark-style') ? 'dark' : 'light';
    // const fillColor = htmlClass === 'dark' ? '#fff' : '#000';
    const fillColor = '#fff';
    const svgIcon = `
        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><style>.spinner_OSmW{fill: ${fillColor}; transform-origin:center;animation:spinner_T6mA .75s step-end infinite}@keyframes spinner_T6mA{8.3%{transform:rotate(30deg)}16.6%{transform:rotate(60deg)}25%{transform:rotate(90deg)}33.3%{transform:rotate(120deg)}41.6%{transform:rotate(150deg)}50%{transform:rotate(180deg)}58.3%{transform:rotate(210deg)}66.6%{transform:rotate(240deg)}75%{transform:rotate(270deg)}83.3%{transform:rotate(300deg)}91.6%{transform:rotate(330deg)}100%{transform:rotate(360deg)}}</style><g class="spinner_OSmW"><rect x="11" y="1" width="2" height="5" opacity=".14"/><rect x="11" y="1" width="2" height="5" transform="rotate(30 12 12)" opacity=".29"/><rect x="11" y="1" width="2" height="5" transform="rotate(60 12 12)" opacity=".43"/><rect x="11" y="1" width="2" height="5" transform="rotate(90 12 12)" opacity=".57"/><rect x="11" y="1" width="2" height="5" transform="rotate(120 12 12)" opacity=".71"/><rect x="11" y="1" width="2" height="5" transform="rotate(150 12 12)" opacity=".86"/><rect x="11" y="1" width="2" height="5" transform="rotate(180 12 12)"/></g></svg>
    `;

    let options = {
        imageUrl: 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgIcon),
        imageWidth: 100,
        imageHeight: 100,
        // height: 100,
        // width: 400,
        imageAlt: 'Custom SVG Icon',
        showConfirmButton: false,
        allowOutsideClick: false,
        customClass: {
            container: 'transparent-swal2'
        },
    };

    Message ? options['html'] = '<span style="color: #fff;">'+Message+'</span>' : '';
    Swal.fire(options);
}

function toastSuccess(message, title) {
    toastr.success(message, title, {
        positionClass: 'toast-top-right',
        closeButton: true,
        timeOut: 5000,
        extendedTimeOut: 1000,
        progressBar: true,
        tapToDismiss: false
    });
}

function toastWarning(message, title) {
    toastr.warning(message, title, {
        positionClass: 'toast-top-right',
        closeButton: true,
        timeOut: 5000,
        extendedTimeOut: 1000,
        progressBar: true,
        tapToDismiss: false
    });
}

function toastError(message, title) {
    toastr.error(message, title, {
        positionClass: 'toast-top-right',
        closeButton: true,
        timeOut: 5000,
        extendedTimeOut: 1000,
        progressBar: true,
        tapToDismiss: false
    });
}

function toastInfo(message, title) {
    toastr.info(message, title, {
        positionClass: 'toast-top-right',
        closeButton: true,
        timeOut: 5000,
        extendedTimeOut: 1000,
        progressBar: true,
        tapToDismiss: false
    });
}


//blockui

function blockPage() {
    $.blockUI({
        message: '<div class="sk-circle mx-auto">\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                  </div>',
        css: {
            backgroundColor: 'transparent',
            border: '0'
        },
        overlayCSS: {
            backgroundColor: 'rgba(0, 0, 0, 0.6)',
            opacity: 1,
            backdropFilter: 'blur(4px)',
            '-webkit-backdrop-filter': 'blur(4px)'
        }
    });
}

function unblockPage() {
    $(document).ajaxStop($.unblockUI);
}
