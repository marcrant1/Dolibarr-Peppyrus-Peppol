<?php
/* Copyright (C) 2024 Custom Module
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

class modPeppolExport extends DolibarrModules
{
    public function __construct($db)
    {
        global $langs, $conf;

        $this->db = $db;
        $this->numero = 500000;
        $this->rights_class = 'peppolexport';
        $this->family = "technic";
        $this->module_position = '90';
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        $this->description = "Module pour exporter les factures au format UBL vers Peppol";
        $this->descriptionlong = "Exporte les factures et avoirs au format UBL 2.1 (PEPPOL BIS Billing 3.0) vers un point d'accès Peppol";
        $this->editor_name = 'Custom';
        $this->editor_url = '';
        $this->version = '1.0.0';
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        $this->picto = 'generic';

        $this->dirs = array("/peppolexport/temp");
        $this->config_page_url = array("setup.php@peppolexport");
        $this->depends = array('modFacture');
        $this->requiredby = array();
        $this->conflictwith = array();
        $this->langfiles = array("peppolexport@peppolexport");
        $this->phpmin = array(7, 0);
        $this->need_dolibarr_version = array(11, 0);

        $this->const = array(
            0 => array('PEPPOLEXPORT_API_URL', 'chaine', '', 'URL de l\'API Peppol', 0),
            1 => array('PEPPOLEXPORT_API_KEY', 'chaine', '', 'Clé API Peppol', 0),
            2 => array('PEPPOLEXPORT_PEPPOL_ID', 'chaine', '', 'Votre ID Peppol', 0),
        );

        $this->rights = array();
        $r = 0;

        $r++;
        $this->rights[$r][0] = $this->numero + $r;
        $this->rights[$r][1] = 'Exporter les factures vers Peppol';
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'export';
        $this->rights[$r][5] = '';

        $this->menu = array();
    }

    public function init($options = '')
    {
        $result = $this->_load_tables('/peppolexport/sql/');
        if ($result < 0) return -1;
        return $this->_init(array(), $options);
    }

    public function remove($options = '')
    {
        $sql = array();
        return $this->_remove($sql, $options);
    }
}