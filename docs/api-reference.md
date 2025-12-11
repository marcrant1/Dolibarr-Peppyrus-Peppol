# Référence API - Dolibarr Peppol Export

Documentation technique des classes et méthodes du module.

## Table des matières

- [Classes principales](#classes-principales)
- [UBLGenerator](#ublgenerator)
- [PeppolAPI](#peppolapi)
- [Fonctions utilitaires](#fonctions-utilitaires)
- [Hooks](#hooks)
- [Base de données](#base-de-données)

---

## Classes principales

### UBLGenerator

**Fichier** : `class/ublgenerator.class.php`

Génère des fichiers UBL 2.1 conformes PEPPOL BIS Billing 3.0.

#### Méthodes publiques

##### `__construct($db)`
Constructeur de la classe.

**Paramètres :**
- `$db` (Database) : Instance de connexion à la base de données Dolibarr

**Exemple :**
```php
$ublGenerator = new UBLGenerator($db);
```

##### `generateFromInvoice($invoice_id)`
Génère un fichier UBL XML pour une facture.

**Paramètres :**
- `invoice_id` (int) : ID de la facture Dolibarr

**Retour :**
- (string|false) : XML UBL ou false en cas d'erreur

**Exemple :**
```php
$ubl_xml = $ublGenerator->generateFromInvoice(123);
if ($ubl_xml) {
    file_put_contents('invoice.xml', $ubl_xml);
}
```

#### Méthodes privées

##### `addElement($xml, $parent, $name, $value)`
Ajoute un élément XML avec gestion des valeurs vides.

##### `formatDate($timestamp)`
Formate une date au format ISO 8601 (YYYY-MM-DD).

##### `addSupplierParty($xml, $root)`
Ajoute les informations du fournisseur (votre entreprise).

##### `addCustomerParty($xml, $root)`
Ajoute les informations du client.

##### `addInvoiceLines($xml, $root)`
Ajoute les lignes de facture.

##### `addPaymentMeans($xml, $root)`
Ajoute les moyens de paiement (IBAN, BIC).

##### `addTaxTotal($xml, $root)`
Ajoute les totaux de TVA.

##### `addLegalMonetaryTotal($xml, $root)`
Ajoute les totaux monétaires.

---

### PeppolAPI

**Fichier** : `class/peppolapi.class.php`

Client pour l'API Peppyrus.

#### Méthodes publiques

##### `__construct($db)`
Constructeur de la classe.

**Paramètres :**
- `$db` (Database) : Instance de connexion à la base de données

**Exemple :**
```php
$peppolApi = new PeppolAPI($db);
```

##### `sendDocument($ubl_xml, $recipient_id, $document_type)`
Envoie un document UBL vers le réseau Peppol.

**Paramètres :**
- `$ubl_xml` (string) : Contenu XML du document UBL
- `$recipient_id` (string) : ID Peppol du destinataire (ex: `9925:be0123456789`)
- `$document_type` (string) : Type de document PEPPOL

**Retour :**
- (array) : Tableau avec les clés :
  - `success` (bool) : true si succès
  - `response` (array) : Réponse de l'API
  - `message` (string) : Message d'erreur si échec

**Exemple :**
```php
$document_type = 'busdox-docid-qns::urn:oasis:names:specification:ubl:schema:xsd:Invoice-2::Invoice##urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0::2.1';

$result = $peppolApi->sendDocument($ubl_xml, '9925:be0123456789', $document_type);

if ($result['success']) {
    echo "Document envoyé !";
} else {
    echo "Erreur : " . $result['message'];
}
```

##### `lookupParticipant($participant_id)`
Recherche un participant dans l'annuaire Peppol.

**Paramètres :**
- `$participant_id` (string) : ID Peppol à rechercher

**Retour :**
- (array) : Tableau avec les clés :
  - `success` (bool) : true si trouvé
  - `found` (bool) : true si participant existe
  - `message` (string) : Message de résultat

**Exemple :**
```php
$result = $peppolApi->lookupParticipant('9925:be0123456789');
if ($result['found']) {
    echo "Participant trouvé !";
}
```

#### Méthodes privées

##### `getApiConfig()`
Récupère la configuration de l'API depuis Dolibarr.

**Retour :**
- (array) : Configuration (api_url, api_key, sender_id)

##### `makeApiRequest($endpoint, $data, $method)`
Effectue une requête HTTP vers l'API Peppyrus.

**Paramètres :**
- `$endpoint` (string) : Endpoint de l'API
- `$data` (array) : Données à envoyer
- `$method` (string) : Méthode HTTP (GET, POST, etc.)

**Retour :**
- (array) : Réponse de l'API

---

## Fonctions utilitaires

**Fichier** : `lib/peppolexport.lib.php`

### `getPeppolIdFromCompany($company)`

Récupère l'ID Peppol d'un tiers.

**Paramètres :**
- `$company` (Societe) : Objet Societe de Dolibarr

**Retour :**
- (string|null) : ID Peppol ou null si non trouvé

**Exemple :**
```php
$company = new Societe($db);
$company->fetch(123);
$peppol_id = getPeppolIdFromCompany($company);
```

### `validatePeppolId($peppol_id)`

Valide le format d'un ID Peppol.

**Paramètres :**
- `$peppol_id` (string) : ID à valider

**Retour :**
- (bool) : true si valide

**Format attendu :** `SCHEME:IDENTIFIER` (ex: `9925:be0123456789`)

### `getPeppolDocumentType($invoice_type)`

Retourne le document type PEPPOL selon le type de facture.

**Paramètres :**
- `$invoice_type` (int) : Type de facture Dolibarr

**Retour :**
- (string) : Document type PEPPOL complet

---

## Hooks

**Fichier** : `class/actions_peppolexport.class.php`

### Actions disponibles

#### `formObjectOptions($parameters, &$object, &$action)`

Injecte des boutons dans les fiches factures.

**Boutons ajoutés :**
- **Générer UBL** : Télécharge le fichier XML
- **Rechercher dans Peppol** : Vérifie l'existence du destinataire
- **Envoyer vers Peppol** : Envoie la facture

**Conditions :**
- Facture validée (statut ≥ 1)
- Module activé
- Page `card.php` du module facture

---

## Base de données

### Table : llx_peppolexport_log

Enregistre tous les envois vers Peppol.

#### Structure

```sql
CREATE TABLE llx_peppolexport_log (
    rowid INT AUTO_INCREMENT PRIMARY KEY,
    fk_facture INT NOT NULL,
    date_export DATETIME NOT NULL,
    recipient_id VARCHAR(255),
    document_type VARCHAR(255),
    status VARCHAR(50),
    response_message TEXT,
    fk_user_export INT,
    INDEX idx_facture (fk_facture),
    INDEX idx_date (date_export)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

#### Colonnes

| Colonne | Type | Description |
|---------|------|-------------|
| `rowid` | INT | ID auto-incrémenté |
| `fk_facture` | INT | Référence à la facture (llx_facture) |
| `date_export` | DATETIME | Date et heure de l'envoi |
| `recipient_id` | VARCHAR(255) | ID Peppol du destinataire |
| `document_type` | VARCHAR(255) | Type de document PEPPOL |
| `status` | VARCHAR(50) | Statut : 'success' ou 'failed' |
| `response_message` | TEXT | Réponse complète de l'API (JSON) |
| `fk_user_export` | INT | ID de l'utilisateur ayant effectué l'envoi |

#### Requêtes utiles

##### Voir les derniers envois
```sql
SELECT 
    l.rowid,
    f.ref AS facture_ref,
    l.date_export,
    l.recipient_id,
    l.status,
    u.login AS user
FROM llx_peppolexport_log l
LEFT JOIN llx_facture f ON f.rowid = l.fk_facture
LEFT JOIN llx_user u ON u.rowid = l.fk_user_export
ORDER BY l.date_export DESC
LIMIT 10;
```

##### Statistiques d'envoi
```sql
SELECT 
    status,
    COUNT(*) AS total,
    DATE(date_export) AS jour
FROM llx_peppolexport_log
GROUP BY status, DATE(date_export)
ORDER BY jour DESC;
```

##### Envois pour une facture
```sql
SELECT *
FROM llx_peppolexport_log
WHERE fk_facture = 123
ORDER BY date_export DESC;
```

---

## Configuration

### Constantes Dolibarr

Ces constantes sont définies dans `core/modules/modPeppolExport.class.php` :

| Constante | Type | Description | Défaut |
|-----------|------|-------------|--------|
| `PEPPOLEXPORT_API_URL` | string | URL de l'API Peppyrus | '' |
| `PEPPOLEXPORT_API_KEY` | string | Clé API Peppyrus | '' |
| `PEPPOLEXPORT_PEPPOL_ID` | string | Votre ID Peppol | '' |

### Accès aux constantes

```php
global $conf;

$api_url = $conf->global->PEPPOLEXPORT_API_URL;
$api_key = $conf->global->PEPPOLEXPORT_API_KEY;
$sender_id = $conf->global->PEPPOLEXPORT_PEPPOL_ID;
```

---

## Format UBL généré

### Structure de base (Invoice)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">
    
    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0</cbc:CustomizationID>
    <cbc:ProfileID>urn:fdc:peppol.eu:2017:poacc:billing:01:1.0</cbc:ProfileID>
    <cbc:ID>FACTURE_REF</cbc:ID>
    <cbc:IssueDate>2024-11-26</cbc:IssueDate>
    <cbc:InvoiceTypeCode>380</cbc:InvoiceTypeCode>
    <cbc:DocumentCurrencyCode>EUR</cbc:DocumentCurrencyCode>
    
    <!-- AccountingSupplierParty -->
    <!-- AccountingCustomerParty -->
    <!-- PaymentMeans -->
    <!-- TaxTotal -->
    <!-- LegalMonetaryTotal -->
    <!-- InvoiceLines -->
    
</Invoice>
```

### Types de documents

| Type | Code | Description |
|------|------|-------------|
| Invoice | 380 | Facture standard |
| CreditNote | 381 | Avoir |

---

## Codes d'erreur

### Erreurs API Peppyrus

| Code | Description | Solution |
|------|-------------|----------|
| 401 | API Key invalide | Vérifier la clé API |
| 404 | Participant non trouvé | Vérifier l'ID Peppol |
| 400 | UBL invalide | Valider le XML |
| 500 | Erreur serveur | Réessayer plus tard |

### Erreurs de validation UBL

| Code | Description | Solution |
|------|-------------|----------|
| BR-61 | IBAN manquant | Configurer l'IBAN |
| BR-25 | Libellé ligne manquant | Ajouter un libellé |
| BR-CO-15 | TVA incohérente | Vérifier les taux |

---

## Exemples d'utilisation

### Exemple complet d'envoi

```php
<?php
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
dol_include_once('/peppolexport/class/ublgenerator.class.php');
dol_include_once('/peppolexport/class/peppolapi.class.php');
dol_include_once('/peppolexport/lib/peppolexport.lib.php');

// Charger la facture
$invoice_id = 123;
$invoice = new Facture($db);
$invoice->fetch($invoice_id);
$invoice->fetch_thirdparty();

// Récupérer l'ID Peppol du client
$recipient_id = getPeppolIdFromCompany($invoice->thirdparty);

if (empty($recipient_id)) {
    die('Client sans ID Peppol');
}

// Générer le UBL
$ublGenerator = new UBLGenerator($db);
$ubl_xml = $ublGenerator->generateFromInvoice($invoice_id);

if (!$ubl_xml) {
    die('Erreur de génération UBL');
}

// Déterminer le type de document
$is_credit_note = ($invoice->type == Facture::TYPE_CREDIT_NOTE);
$document_type = $is_credit_note ? 
    'busdox-docid-qns::urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2::CreditNote##urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0::2.1' :
    'busdox-docid-qns::urn:oasis:names:specification:ubl:schema:xsd:Invoice-2::Invoice##urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0::2.1';

// Envoyer vers Peppol
$peppolApi = new PeppolAPI($db);
$result = $peppolApi->sendDocument($ubl_xml, $recipient_id, $document_type);

if ($result['success']) {
    echo "✅ Facture envoyée avec succès !";
    
    // Logger en base de données
    $sql = "INSERT INTO ".MAIN_DB_PREFIX."peppolexport_log ";
    $sql .= "(fk_facture, date_export, recipient_id, document_type, status, response_message, fk_user_export) ";
    $sql .= "VALUES (";
    $sql .= $invoice_id.", ";
    $sql .= "'".$db->idate(dol_now())."', ";
    $sql .= "'".$db->escape($recipient_id)."', ";
    $sql .= "'".$db->escape($document_type)."', ";
    $sql .= "'success', ";
    $sql .= "'".$db->escape(json_encode($result['response']))."', ";
    $sql .= $user->id;
    $sql .= ")";
    $db->query($sql);
} else {
    echo "❌ Erreur : " . $result['message'];
}
?>
```

---

## Support et ressources

- [Documentation Peppyrus API](https://peppyrus.be/api)
- [Spécifications PEPPOL BIS Billing 3.0](https://docs.peppol.eu/poacc/billing/3.0/)
- [Format UBL 2.1](http://docs.oasis-open.org/ubl/UBL-2.1.html)
- [GitHub Issues](https://github.com/votre-username/dolibarr-peppol-export/issues)
