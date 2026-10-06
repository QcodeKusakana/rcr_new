/* Administration RCR — comportements communs (aucune donnée sensible ici). */
(function () {
    'use strict';
    var FR = {
        search: 'Rechercher :', lengthMenu: 'Afficher _MENU_ lignes', info: '_START_ à _END_ sur _TOTAL_',
        infoEmpty: 'Aucune donnée', infoFiltered: '(filtré sur _MAX_)', zeroRecords: 'Aucun résultat trouvé',
        paginate: { first: 'Premier', last: 'Dernier', previous: 'Précédent', next: 'Suivant' }
    };

    // Tableaux enrichis (recherche instantanée, tri, pagination) sur les tableaux marqués .datatable-auto
    if (window.jQuery && jQuery.fn.DataTable) {
        jQuery('.datatable-auto').each(function () {
            jQuery(this).DataTable({ language: FR, pageLength: 25, responsive: false, order: [] });
        });
    }

    // Confirmation moderne avant une action sensible (liens) : onclick="return confirmAction(event, 'Message')"
    window.confirmAction = function (e, message, confirmLabel) {
        e.preventDefault();
        var url = e.currentTarget.getAttribute('href');
        if (!window.Swal) { if (window.confirm(message || 'Confirmer ?')) { window.location.href = url; } return false; }
        Swal.fire({
            title: message || 'Confirmer cette action ?', icon: 'warning', showCancelButton: true,
            confirmButtonText: confirmLabel || 'Oui, confirmer', cancelButtonText: 'Annuler',
            confirmButtonColor: '#B42318', cancelButtonColor: '#6B7280', reverseButtons: true
        }).then(function (r) { if (r.isConfirmed) { window.location.href = url; } });
        return false;
    };

    // Confirmation avant envoi d'un formulaire : <form class="js-confirm" data-confirm="Message">
    document.addEventListener('submit', function (e) {
        var f = e.target;
        if (!f.classList || !f.classList.contains('js-confirm') || f.dataset.confirmed === '1') { return; }
        e.preventDefault();
        var go = function () { f.dataset.confirmed = '1'; f.submit(); };
        if (!window.Swal) { if (window.confirm(f.dataset.confirm || 'Confirmer ?')) { go(); } return; }
        Swal.fire({
            title: f.dataset.confirm || 'Confirmer cette action ?', icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Oui, confirmer', cancelButtonText: 'Annuler', confirmButtonColor: '#B42318', reverseButtons: true
        }).then(function (r) { if (r.isConfirmed) { go(); } });
    }, true);

    // Anti double-clic : un formulaire envoyé désactive son bouton (évite les doublons d'enregistrement)
    document.addEventListener('submit', function (e) {
        var f = e.target;
        if (e.defaultPrevented || !f.querySelector) { return; }
        var b = f.querySelector('button[type=submit], button:not([type])');
        if (b && f.method && f.method.toLowerCase() === 'post') {
            setTimeout(function () { b.disabled = true; b.dataset.label = b.innerHTML; b.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Patientez…'; }, 0);
        }
    });

    // Messages flash Bootstrap -> notifications discrètes (sauf data-no-toast)
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.Swal) { return; }
        document.querySelectorAll('.adm-content > .alert-success, .adm-content > .alert-danger, .adm-content > .alert-warning, .adm-content > .alert-info').forEach(function (el) {
            if (el.hasAttribute('data-no-toast')) { return; }
            var icon = el.classList.contains('alert-success') ? 'success' : el.classList.contains('alert-danger') ? 'error' : el.classList.contains('alert-info') ? 'info' : 'warning';
            var text = el.textContent.trim();
            if (text) {
                Swal.fire({ toast: true, position: 'top-end', icon: icon, title: text, showConfirmButton: false, timer: 4500, timerProgressBar: true });
                el.style.display = 'none';
            }
        });
    });
})();
