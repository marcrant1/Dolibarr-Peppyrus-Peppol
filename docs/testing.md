# Guide de test - Dolibarr Peppol Export

## Table des matières

- [Avant de commencer](#avant-de-commencer)
- [Tests de configuration](#tests-de-configuration)
- [Tests de génération UBL](#tests-de-génération-ubl)
- [Tests d'envoi](#tests-denvoi)
- [Tests en production](#tests-en-production)
- [Checklist finale](#checklist-finale)

---

## Avant de commencer

### Prérequis

Assurez-vous d'avoir :
- ✅ Installé et activé le module
- ✅ Configuré votre clé API Peppyrus
- ✅ Configuré votre identifiant Peppol
- ✅ Configuré votre IBAN
- ✅ Au moins une facture de test dans Dolibarr

### Environnements de test

Peppyrus propose deux environnements :

| Environnement | URL API | Usage |
|---------------|---------|-------|
| **Test** | `https://api.test.peppyrus.be/v1` | Pour les tests sans impact réel |
| **Production** | `https://api.peppyrus.be/v1` | Pour les envois réels |

💡 **Recommandation :** Commencez TOUJOURS par l'environnement de test !

---

## Tests de configuration

### Test 1 : Vérification automatique

Utilisez l'outil de diagnostic intégré :

```
https://votre-dolibarr.com/custom/peppolexport/admin/diagnostic.php
```

**Ce qui est vérifié :**
- ✅ Extensions PHP (curl, json, xml, dom, openssl)
- ✅ Configuration du module
- ✅ Configuration de votre entreprise
- ✅ Connexion à l'API Peppyrus
- ✅ Permissions fichiers

**Résultat attendu :** Tous les tests doivent être ✅ verts.

### Test 2 : Vérification manuelle

#### a) Configuration du module

1. Allez dans **Configuration** → **Modules** → **Peppol Export** → ⚙️
2. Vérifiez :
   - URL API : `https://api.test.peppyrus.be/v1` (pour test)
   - Clé API : Renseignée et valide
   - Votre ID Peppol : Format correct (`9925:be0123456789`)

#### b) Configuration de l'entreprise

1. **Configuration** → **Société/Organisation**
2. Vérifiez :
   - Nom de l'entreprise : ✅
   - Adresse complète : ✅
   - Numéro de TVA : ✅
   - IBAN : ✅ (sans espaces)
   - BIC : ✅

#### c) Configuration d'un client

1. Ouvrez une fiche client
2. Vérifiez :
   - Nom : ✅
   - Adresse complète : ✅
   - Numéro de TVA : ✅
   - ID Peppol : ✅ (format: `9925:be0123456789`)

---

## Tests de génération UBL

### Test 3 : Générer un fichier UBL

#### Via l'interface Dolibarr

1. Ouvrez une **facture validée**
2. En bas de page, cliquez sur **📄 Générer UBL**
3. Un fichier XML doit être téléchargé

**Vérifications :**
- ✅ Le fichier se télécharge correctement
- ✅ Le nom du fichier correspond à la référence de la facture
- ✅ Le fichier peut s'ouvrir dans un éditeur XML/navigateur

#### Via l'outil de test

```
https://votre-dolibarr.com/custom/peppolexport/tools/test_ubl.php?id=123
```

**Vérifications :**
- ✅ Message "UBL generated successfully"
- ✅ XML est affiché
- ✅ XML est "well-formed"
- ✅ Le bouton de téléchargement fonctionne

### Test 4 : Valider le fichier UBL

#### Validation de base (structure XML)

1. Ouvrez le fichier XML dans un navigateur (Chrome, Firefox)
2. Vérifiez qu'il n'y a **pas d'erreur de syntaxe**
3. Le XML doit être affiché avec une structure hiérarchique

#### Validation PEPPOL (optionnel)

Utilisez le validateur PEPPOL officiel :

1. Allez sur [test-infra.peppol.eu](https://test-infra.peppol.eu/)
2. Ou [ecosio.com/en/peppol-and-xml-document-validator](https://ecosio.com/en/peppol-and-xml-document-validator/)
3. Uploadez votre fichier XML
4. Vérifiez que la validation passe à 100%

**Erreurs courantes et solutions :**

| Erreur | Cause | Solution |
|--------|-------|----------|
| **BR-61** | IBAN manquant | Ajoutez l'IBAN dans Configuration → Société |
| **BR-25** | Libellé ligne manquant | Ajoutez un libellé à toutes les lignes |
| **BR-CO-15** | TVA incohérente | Vérifiez les taux de TVA |

---

## Tests d'envoi

### Test 5 : Rechercher un participant

#### Via l'interface

1. Ouvrez une **facture validée**
2. Cliquez sur **🔍 Rechercher dans Peppol**
3. Une popup doit s'afficher avec le résultat

**Résultats possibles :**

✅ **Participant found**
- Le client est bien enregistré sur Peppol
- Vous pouvez lui envoyer des factures

❌ **Participant not found**
- Le client n'est pas (encore) sur Peppol
- Vérifiez l'identifiant Peppol
- Demandez au client de s'inscrire

### Test 6 : Envoyer une facture de test

⚠️ **Important :** Utilisez d'abord l'**environnement de test** !

#### Configuration pour le test

1. **Configuration** → **Modules** → **Peppol Export** → ⚙️
2. URL API → `https://api.test.peppyrus.be/v1`
3. Clé API → Votre clé API **de test**

#### Envoi

1. Ouvrez une **facture validée**
2. Vérifiez que le client a un ID Peppol valide
3. Cliquez sur **📤 Envoyer vers Peppol**
4. Confirmez l'envoi
5. Attendez la confirmation

**Résultats possibles :**

✅ **Invoice sent successfully**
- La facture a été envoyée
- Vérifiez sur [customer.test.peppyrus.be](https://customer.test.peppyrus.be)

❌ **Error sending to Peppol**
- Lisez le message d'erreur
- Vérifiez les logs
- Voir [Résolution des erreurs](#résolution-des-erreurs)

#### Vérifier l'envoi sur Peppyrus

1. Connectez-vous à [customer.test.peppyrus.be](https://customer.test.peppyrus.be)
2. Allez dans **Sent documents** ou **Documents envoyés**
3. Vérifiez que votre facture apparaît
4. Statut doit être **Sent** ou **Delivered**

### Test 7 : Vérifier les logs

Les envois sont enregistrés dans la base de données.

#### Via phpMyAdmin ou ligne de commande :

```sql
SELECT * FROM llx_peppolexport_log 
ORDER BY date_export DESC 
LIMIT 10;
```

**Colonnes importantes :**
- `fk_facture` : ID de la facture
- `date_export` : Date/heure de l'envoi
- `recipient_id` : ID Peppol du destinataire
- `status` : success ou failed
- `response_message` : Réponse de l'API

---

## Tests en production

### Test 8 : Premier envoi réel

Une fois que tous les tests sont ✅ :

#### 1. Basculer en production

1. **Configuration** → **Modules** → **Peppol Export** → ⚙️
2. URL API → `https://api.peppyrus.be/v1`
3. Clé API → Votre **vraie** clé API
4. Sauvegardez

#### 2. Choisir une facture de test

Choisissez une facture réelle mais :
- 💡 De faible montant
- 💡 Vers un client avec qui vous avez un bon contact
- 💡 Que vous pouvez annuler si nécessaire

#### 3. Envoyer

1. Ouvrez la facture
2. Cliquez sur **📤 Envoyer vers Peppol**
3. Confirmez
4. Vérifiez la confirmation

#### 4. Vérifier la réception

1. Contactez le client
2. Demandez-lui de confirmer la réception
3. Vérifiez sur [customer.peppyrus.be](https://customer.peppyrus.be)

### Test 9 : Test des avoirs

Les avoirs (credit notes) suivent le même processus :

1. Créez un avoir à partir d'une facture
2. Validez l'avoir
3. Envoyez vers Peppol

**Différences :**
- Document type : `CreditNote` au lieu de `Invoice`
- Référence à la facture originale dans le XML

---

## Checklist finale

### Avant le déploiement en production

- [ ] Tous les tests de configuration sont ✅
- [ ] La génération UBL fonctionne correctement
- [ ] La validation PEPPOL passe à 100%
- [ ] Les tests d'envoi en environnement de test sont réussis
- [ ] Les logs sont correctement enregistrés
- [ ] Un premier envoi réel a été vérifié
- [ ] Le client a bien reçu la facture
- [ ] Les outils de test (`/tools/`) ont été supprimés

### Sécurité

- [ ] Clé API en production (pas de test)
- [ ] URL API en production (`https://api.peppyrus.be/v1`)
- [ ] Dossier `/tools/` supprimé
- [ ] Permissions fichiers correctes (755/644)
- [ ] Accès limité aux utilisateurs autorisés

### Documentation

- [ ] Guide d'utilisation distribué aux utilisateurs
- [ ] Liste des clients Peppol maintenue à jour
- [ ] Procédure d'erreur documentée
- [ ] Contact support identifié

---

## Résolution des erreurs

### Erreurs d'envoi courantes

#### "API Key Missing"
**Solution :** Configurez votre clé API dans le module.

#### "Sender Peppol ID not configured"
**Solution :** Configurez votre ID Peppol dans le module.

#### "Recipient Peppol ID not configured"
**Solution :** Ajoutez l'ID Peppol du client dans sa fiche.

#### "Participant not found"
**Solution :** 
- Vérifiez l'ID Peppol du client
- Le client doit s'inscrire sur Peppol

#### "Invalid UBL" ou erreurs BR-XX
**Solution :** 
- Vérifiez que toutes les données obligatoires sont présentes
- Utilisez le validateur en ligne pour identifier l'erreur précise
- Voir le guide de configuration pour les solutions spécifiques

### Erreurs techniques

#### "Connection timeout"
**Solution :**
- Vérifiez votre connexion internet
- Vérifiez les pare-feu/proxies
- Testez l'URL API dans un navigateur

#### "SSL certificate problem"
**Solution :**
```bash
# Mettre à jour les certificats CA
sudo apt-get update
sudo apt-get install ca-certificates
```

---

## Performances et limites

### Limites de l'API Peppyrus

- **Rate limiting** : Consultez la documentation Peppyrus
- **Taille maximale** : ~10 MB par document
- **Timeout** : 30 secondes par requête

### Optimisations

- Ne pas envoyer les brouillons
- Envoyer uniquement les factures validées
- Éviter les envois multiples du même document
- Utiliser les logs pour tracer les envois

---

## Support et ressources

### Documentation

- [README principal](../README.md)
- [Guide d'installation](installation.md)
- [Guide de configuration](configuration.md)

### Support technique

- 🐛 [GitHub Issues](https://github.com/votre-username/dolibarr-peppol-export/issues)
- 💬 [GitHub Discussions](https://github.com/votre-username/dolibarr-peppol-export/discussions)
- 📧 [Forum Dolibarr](https://forum.dolibarr.org)

### Ressources externes

- [Documentation Peppyrus](https://peppyrus.be)
- [Spécifications PEPPOL BIS Billing 3.0](https://docs.peppol.eu/poacc/billing/3.0/)
- [Annuaire Peppol](https://directory.peppol.eu/)
- [Validateur en ligne](https://ecosio.com/en/peppol-and-xml-document-validator/)

---

**Bon tests ! 🚀**
