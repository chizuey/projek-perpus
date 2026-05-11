<?php if (!defined('ADMIN_POPUP_HELPER')): ?>
<?php define('ADMIN_POPUP_HELPER', true); ?>

<style>
.admin-popup,
.popup-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(15, 23, 42, 0.42);
}

.admin-popup.show,
.popup-overlay.active {
    display: flex;
}

.admin-popup .return-confirm-box,
.popup-box {
    width: 460px;
    max-width: calc(100vw - 32px);
    max-height: 90vh;
    overflow: hidden;
    border-radius: 10px;
    background: #ffffff;
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.22);
}

.popup-box {
    width: 760px;
    overflow-y: auto;
}

.admin-popup .return-confirm-header,
.popup-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
}

.admin-popup .return-confirm-header h3,
.popup-header span {
    margin: 0;
    color: #1f2937;
    font-size: 18px;
    font-weight: 700;
}

.admin-popup .return-confirm-close,
.popup-close {
    border: none;
    background: transparent;
    color: #64748b;
    font-size: 26px;
    line-height: 1;
    cursor: pointer;
}

.admin-popup .return-confirm-close:hover,
.popup-close:hover {
    color: #1f2937;
}

.admin-popup .return-confirm-body,
.popup-body {
    padding: 18px 20px;
}

.admin-popup .return-confirm-text {
    margin: 0 0 14px;
    color: #334155;
    font-size: 14px;
    line-height: 1.5;
}

.admin-popup .return-confirm-detail {
    display: grid;
    gap: 8px;
    padding: 12px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #f8fafc;
    color: #334155;
    font-size: 14px;
}

.admin-popup .return-confirm-actions,
.popup-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 0 20px 20px;
}

.popup-footer {
    padding: 0;
    margin-top: 16px;
}

.admin-popup .btn-return-batal,
.admin-popup .btn-return-submit,
.popup-footer .btn-batal,
.popup-footer .btn-simpan {
    min-width: 96px;
    height: 40px;
    padding: 0 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}

.admin-popup .btn-return-batal,
.popup-footer .btn-batal {
    border: 1px solid #d7dde5;
    background: #ffffff;
    color: #334155;
}

.admin-popup .btn-return-batal:hover,
.popup-footer .btn-batal:hover {
    background: #f8fafc;
}

.admin-popup .btn-return-submit,
.popup-footer .btn-simpan {
    border: 1px solid #0050ad;
    background: #0050ad;
    color: #ffffff;
}

.admin-popup .btn-return-submit:hover,
.popup-footer .btn-simpan:hover {
    background: #004493;
}

.admin-popup .btn-danger-submit {
    background: #dc2626;
    border-color: #dc2626;
}

.admin-popup .btn-danger-submit:hover {
    background: #b91c1c;
}

.admin-popup .btn-green-submit {
    background: #16a34a;
    border-color: #16a34a;
}

.admin-popup .btn-green-submit:hover {
    background: #15803d;
}

body.modal-open {
    overflow: hidden;
}
</style>

<script>
function bukaPopup(id) {
    var popup = document.getElementById(id);
    if (!popup) return;

    if (popup.classList.contains('popup-overlay')) {
        popup.classList.add('active');
    } else {
        popup.classList.add('show');
    }

    document.body.classList.add('modal-open');
}

function masihAdaPopupTerbuka() {
    return document.querySelector('.admin-popup.show, .popup-overlay.active, .return-confirm-overlay.show') !== null;
}

function tutupPopup(id) {
    var popup = document.getElementById(id);
    if (!popup) return;

    popup.classList.remove('show');
    popup.classList.remove('active');

    if (!masihAdaPopupTerbuka()) {
        document.body.classList.remove('modal-open');
    }
}

document.addEventListener('click', function (event) {
    var tombolTutup = event.target.closest('[data-popup-close]');
    if (tombolTutup) {
        tutupPopup(tombolTutup.getAttribute('data-popup-close'));
        return;
    }

    if (
        event.target.classList.contains('admin-popup') ||
        event.target.classList.contains('popup-overlay') ||
        event.target.classList.contains('return-confirm-overlay')
    ) {
        tutupPopup(event.target.id);
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;

    var popupTerbuka = document.querySelector('.admin-popup.show, .popup-overlay.active, .return-confirm-overlay.show');
    if (popupTerbuka) {
        tutupPopup(popupTerbuka.id);
    }
});

document.addEventListener('DOMContentLoaded', function () {
    if (masihAdaPopupTerbuka()) {
        document.body.classList.add('modal-open');
    }
});
</script>

<?php endif; ?>
