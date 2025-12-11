jQuery(document).ready(function($) {
    console.log('Peppol JS: Initialisation...');
    
    var getInvoiceId = function() {
        var urlParams = new URLSearchParams(window.location.search);
        var id = urlParams.get('id') || urlParams.get('facid');
        console.log('Peppol JS: Invoice ID =', id);
        return id;
    };
    
    var invoiceId = getInvoiceId();
    if (!invoiceId) {
        console.warn('Peppol JS: Aucun ID de facture trouvé');
        return;
    }
    
    // Détecter le chemin de base de Dolibarr
    var basePath = window.location.pathname;
    var dolibarrRoot = '';
    
    // Extraire la racine (ex: /dolibarr-19.0.0/htdocs/ ou /htdocs/)
   if (basePath.indexOf('/htdocs/') !== -1) {
    dolibarrRoot = basePath.substring(0, basePath.indexOf('/htdocs/') + 8); // +8 au lieu de +7 pour inclure le /
} else {
    dolibarrRoot = '/';
}

var peppolSendUrl = dolibarrRoot + 'custom/peppolexport/peppol_send.php';

console.log('Peppol JS: URL de base =', peppolSendUrl);
    
    // Bouton: Envoyer vers Peppol
    $('#peppol_send_btn').on('click', function(e) {
        e.preventDefault();
        console.log('Peppol JS: Clic sur Envoyer vers Peppol');
        
        if (!confirm('Êtes-vous sûr de vouloir envoyer cette facture vers Peppol ?')) {
            return;
        }
        
        var btn = $(this);
        var originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Envoi en cours...');
        
        $.ajax({
            url: peppolSendUrl,
            type: 'POST',
            data: {
                action: 'send',
                id: invoiceId,
                token: $('input[name="token"]').val()
            },
            dataType: 'json',
            success: function(response) {
                console.log('Peppol JS: Réponse envoi =', response);
                if (response.success) {
                    alert('✓ Facture envoyée avec succès vers Peppol !');
                    location.reload();
                } else {
                    alert('✗ Erreur lors de l\'envoi:\n' + (response.message || 'Erreur inconnue'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Peppol JS: Erreur AJAX envoi', {xhr: xhr, status: status, error: error});
                alert('✗ Erreur de communication avec le serveur:\n' + error + '\n\nVoir la console (F12) pour plus de détails.');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });
    
    // Bouton: Générer UBL
    $('#peppol_generate_ubl_btn').on('click', function(e) {
        e.preventDefault();
        console.log('Peppol JS: Clic sur Générer UBL');
        
        var url = peppolSendUrl + '?action=generate_ubl&id=' + invoiceId;
        console.log('Peppol JS: URL génération UBL =', url);
        
        window.location.href = url;
    });
    
    // Bouton: Rechercher dans Peppol
    $('#peppol_lookup_btn').on('click', function(e) {
        e.preventDefault();
        console.log('Peppol JS: Clic sur Rechercher dans Peppol');
        
        var btn = $(this);
        var originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Recherche...');
        
        $.ajax({
            url: peppolSendUrl,
            type: 'POST',
            data: {
                action: 'lookup',
                id: invoiceId,
                token: $('input[name="token"]').val()
            },
            dataType: 'json',
            success: function(response) {
                console.log('Peppol JS: Réponse lookup =', response);
                if (response.success) {
                    var msg = '✓ Participant trouvé dans le réseau Peppol !';
                    if (response.data && response.data.document_types) {
                        msg += '\n\nTypes de documents supportés : ' + response.data.document_types.length;
                    }
                    alert(msg);
                } else {
                    alert('✗ Participant non trouvé:\n' + (response.message || 'Erreur inconnue'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Peppol JS: Erreur AJAX lookup', {xhr: xhr, status: status, error: error});
                alert('✗ Erreur lors de la recherche:\n' + error);
            },
            complete: function() {
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });
    
    console.log('Peppol JS: Initialisation terminée');
});