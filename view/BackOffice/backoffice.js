// Modale de confirmation (suppressions Back-office)
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-delete').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var href = btn.getAttribute('data-href') || btn.getAttribute('href');
            var msg =
                btn.getAttribute('data-confirm') ||
                'Voulez-vous vraiment supprimer cet élément ? Cette action peut être irréversible.';
            showConfirmModal(msg, href);
        });
    });

    var modal = document.getElementById('confirmModal');
    var okBtn = document.getElementById('confirmOk');
    var cancelBtn = document.getElementById('confirmCancel');

    function closeModal() {
        if (!modal) return;
        modal.setAttribute('aria-hidden', 'true');
        modal.dataset.href = '';
    }

    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target.classList.contains('confirm-modal-backdrop')) closeModal();
        });
    }

    if (okBtn) {
        okBtn.addEventListener('click', function () {
            var target = modal && modal.dataset.href;
            if (target) window.location.href = target;
        });
    }

    window.showConfirmModal = function (message, href) {
        if (!modal) return window.confirm(message);
        var msgEl = modal.querySelector('#confirmMessage');
        if (msgEl) msgEl.textContent = message;
        modal.dataset.href = href || '';
        modal.setAttribute('aria-hidden', 'false');
    };
});
