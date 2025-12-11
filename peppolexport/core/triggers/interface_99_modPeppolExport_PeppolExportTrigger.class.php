<?php
/**
 * Trigger for Peppol Export actions
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';

class InterfacePeppolExportTrigger extends DolibarrTriggers
{
    public function __construct($db)
    {
        $this->db = $db;

        $this->name = preg_replace('/^Interface/i', '', get_class($this));
        $this->family = "peppol";
        $this->description = "Peppol Export triggers";
        $this->version = '1.0';
        $this->picto = 'generic';
    }

    public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
    {
        // Exemple : envoi automatique lors de la validation
        // if ($action == 'BILL_VALIDATE') {
        //     if (!empty($conf->global->PEPPOLEXPORT_AUTO_SEND)) {
        //         // Auto-send logic here
        //     }
        // }

        return 0;
    }
}