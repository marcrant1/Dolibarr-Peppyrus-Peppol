<?php
/**
 * Script to send invoice to Peppol via AJAX
 * File: peppol_send.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Chemin absolu vers main.inc.php
$main_path = '/home/one967telemarcrant1967/public_html/dolibarr-19.0.0/htdocs/main.inc.php';

if (!file_exists($main_path)) {
    die(json_encode(array('success' => false, 'message' => 'main.inc.php not found at: ' . $main_path)));
}

$res = include $main_path;

if (!$res || !defined('DOL_DOCUMENT_ROOT')) {
    die(json_encode(array('success' => false, 'message' => 'Failed to load Dolibarr')));
}

require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
dol_include_once('/peppolexport/class/ublgenerator.class.php');
dol_include_once('/peppolexport/class/peppolapi.class.php');
dol_include_once('/peppolexport/lib/peppolexport.lib.php');

$langs->load("peppolexport@peppolexport");

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');

// Access control
if (!$user->rights->facture->lire) {
    print json_encode(array('success' => false, 'message' => 'Access forbidden'));
    exit;
}

// Initialize objects
$invoice = new Facture($db);
$result = $invoice->fetch($id);

if ($result <= 0) {
    print json_encode(array('success' => false, 'message' => 'Invoice not found'));
    exit;
}

$invoice->fetch_thirdparty();

/*
 * ACTIONS
 */

if ($action == 'send') {
    
    // Check if API is configured
    if (empty($conf->global->PEPPOLEXPORT_API_KEY)) {
        print json_encode(array('success' => false, 'message' => 'API Key manquante'));
        exit;
    }
    
    // Get recipient Peppol ID
    $recipient_id = getPeppolIdFromCompany($invoice->thirdparty);
    
    if (empty($recipient_id)) {
        print json_encode(array('success' => false, 'message' => 'ID Peppol client non configuré'));
        exit;
    }
    
    // Generate UBL
    $ublGenerator = new UBLGenerator($db);
    $ubl_xml = $ublGenerator->generateFromInvoice($id);
    
    if (!$ubl_xml) {
        print json_encode(array('success' => false, 'message' => 'Erreur génération UBL'));
        exit;
    }
    
    // Send to Peppol
    $peppolApi = new PeppolAPI($db);
    
    // Determine document type
    $is_credit_note = ($invoice->type == Facture::TYPE_CREDIT_NOTE);
    $document_type = $is_credit_note ? 
        'urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2::CreditNote##urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0::2.1' :
        'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2::Invoice##urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0::2.1';
    
    $result = $peppolApi->sendDocument($ubl_xml, $recipient_id, $document_type);
    
    // Log the action in database
    if ($result['success']) {
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."peppolexport_log ";
        $sql .= "(fk_facture, date_export, recipient_id, document_type, status, response_message, fk_user_export) ";
        $sql .= "VALUES (";
        $sql .= $id.", ";
        $sql .= "'".$db->idate(dol_now())."', ";
        $sql .= "'".$db->escape($recipient_id)."', ";
        $sql .= "'".$db->escape($document_type)."', ";
        $sql .= "'success', ";
        $sql .= "'".$db->escape(json_encode($result['response']))."', ";
        $sql .= $user->id;
        $sql .= ")";
        $db->query($sql);
    }
    
    print json_encode($result);
    exit;
    
} elseif ($action == 'generate_ubl') {
    
    // Just generate and download UBL
    $ublGenerator = new UBLGenerator($db);
    $ubl_xml = $ublGenerator->generateFromInvoice($id);
    
    if (!$ubl_xml) {
        print json_encode(array('success' => false, 'message' => 'Erreur génération UBL'));
        exit;
    }
    
    // Set headers for download
    header('Content-Type: application/xml; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $invoice->ref . '.xml"');
    header('Content-Length: ' . strlen($ubl_xml));
    
    print $ubl_xml;
    exit;
    
} elseif ($action == 'lookup') {
    
    // Lookup participant in Peppol directory
    $recipient_id = GETPOST('recipient_id', 'alpha');
    
    if (empty($recipient_id)) {
        $recipient_id = getPeppolIdFromCompany($invoice->thirdparty);
    }
    
    if (empty($recipient_id)) {
        print json_encode(array('success' => false, 'message' => 'ID Peppol non configuré'));
        exit;
    }
    
    $peppolApi = new PeppolAPI($db);
    $result = $peppolApi->lookupParticipant($recipient_id);
    
    print json_encode($result);
    exit;
}

// If no valid action
print json_encode(array('success' => false, 'message' => 'Action invalide'));
exit;