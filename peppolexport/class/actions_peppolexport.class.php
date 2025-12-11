<?php
/**
 * Hooks for Peppol Export module
 * VERSION COMPATIBLE DOLIBARR 20.x
 * File: class/actions_peppolexport.class.php
 */

class ActionsPeppolExport
{
    public $db;
    public $error = '';
    public $errors = array();
    public $results = array();
    public $resprints;
    
    /**
     * Constructor
     */
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /**
     * Overloading the doActions function
     * Hook pour Dolibarr 20.x
     */
    public function doActions($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs;
        
        $contexts = explode(':', $parameters['context']);
        
        if (in_array('invoicecard', $contexts)) {
            // On est sur une fiche facture
            return 0;
        }
        
        return 0;
    }
    
    /**
     * Add buttons in invoice card
     * Hook principal pour ajouter les boutons
     */
    public function addMoreActionsButtons($parameters, &$object, &$action)
    {
        global $conf, $user, $langs;
        
        $contexts = explode(':', $parameters['context']);
        
        // Vérifier qu'on est sur une fiche facture
        if (!in_array('invoicecard', $contexts)) {
            return 0;
        }
        
        // Check if module is active
        if (empty($conf->peppolexport->enabled)) {
            return 0;
        }
        
        // Check user rights
        if (!$user->hasRight('facture', 'lire')) {
            return 0;
        }
        
        // Only for validated invoices
        if (!is_object($object) || empty($object->id)) {
            return 0;
        }
        
        // Vérifier le statut (compatible Dolibarr 20.x)
        $valid_status = array(1, 2); // 1=Validée, 2=Payée
        if (!in_array($object->statut, $valid_status) && !in_array($object->status, $valid_status)) {
            return 0;
        }
        
        $langs->load("peppolexport@peppolexport");
        
        // Include JavaScript
        $out = '<script src="' . dol_buildpath('/peppolexport/js/peppolexport.js', 1) . '"></script>';
        
        // Add buttons
        $out .= '<div class="inline-block divButAction">';
        
        // Send to Peppol button
        if ($user->hasRight('facture', 'creer')) {
            $out .= '<a id="peppol_send_btn" class="butAction" href="#" title="'.$langs->trans('SendToPeppol').'">';
            $out .= '<span class="fa fa-paper-plane paddingright"></span>';
            $out .= $langs->trans('SendToPeppol');
            $out .= '</a>';
        }
        
        // Generate UBL button
        $out .= '<a id="peppol_generate_ubl_btn" class="butAction" href="#" title="'.$langs->trans('GenerateUBL').'">';
        $out .= '<span class="fa fa-file-code-o paddingright"></span>';
        $out .= $langs->trans('GenerateUBL');
        $out .= '</a>';
        
        // Lookup button
        $out .= '<a id="peppol_lookup_btn" class="butAction" href="#" title="'.$langs->trans('PeppolNetworkLookup').'">';
        $out .= '<span class="fa fa-search paddingright"></span>';
        $out .= $langs->trans('PeppolNetworkLookup');
        $out .= '</a>';
        
        $out .= '</div>';
        
        $this->resprints = $out;
        
        return 0;
    }
    
    /**
     * Hook executeHooks (appelé par Dolibarr 20.x)
     */
    public function printCommonFooter($parameters, &$object, &$action)
    {
        return $this->addMoreActionsButtons($parameters, $object, $action);
    }
    
    /**
     * Add information in invoice card
     */
    public function formObjectOptions($parameters, &$object, &$action)
    {
        global $conf, $langs, $db;
        
        $contexts = explode(':', $parameters['context']);
        
        // Only on invoice card
        if (!in_array('invoicecard', $contexts)) {
            return 0;
        }
        
        // Check if module is active
        if (empty($conf->peppolexport->enabled)) {
            return 0;
        }
        
        $langs->load("peppolexport@peppolexport");
        
        // Show Peppol ID if configured
        if (!empty($object->thirdparty)) {
            require_once dol_buildpath('/peppolexport/lib/peppolexport.lib.php');
            $peppol_id = getPeppolIdFromCompany($object->thirdparty);
            
            if (!empty($peppol_id)) {
                $out = '<tr>';
                $out .= '<td class="titlefield">' . $langs->trans('YourPeppolID') . '</td>';
                $out .= '<td>' . dol_escape_htmltag($peppol_id) . '</td>';
                $out .= '</tr>';
                
                $this->resprints = $out;
            } else {
                $out = '<tr>';
                $out .= '<td class="titlefield">' . $langs->trans('YourPeppolID') . '</td>';
                $out .= '<td><span class="opacitymedium">' . $langs->trans('PeppolRecipientNotConfigured') . '</span></td>';
                $out .= '</tr>';
                
                $this->resprints = $out;
            }
        }
        
        return 0;
    }
}