# Guide de configuration - Dolibarr Peppol Export

## Table des matières

- [Configuration du module](#configuration-du-module)
- [Configuration de votre entreprise](#configuration-de-votre-entreprise)
- [Configuration des clients](#configuration-des-clients)
- [Identifiants Peppol par pays](#identifiants-peppol-par-pays)
- [Configuration avancée](#configuration-avancée)
- [Résolution des problèmes](#résolution-des-problèmes)

---

## Configuration du module

### Accéder à la configuration

1. Connectez-vous à Dolibarr en tant qu'administrateur
2. Allez dans **Configuration** → **Modules/Applications**
3. Recherchez **"Peppol Export"**
4. Cliquez sur l'icône **⚙️ Configuration**

### Paramètres requis

#### 1. URL de l'API Peppol

**Environnement de production :**
```
https://api.peppyrus.be/v1
```

**Environnement de test :**
```
https://api.test.peppyrus.be/v1
```

💡 **Astuce :** Commencez par l'environnement de test pour vos premiers essais !

#### 2. Clé API

Obtenez votre clé API depuis votre compte Peppyrus :

1. Connectez-vous à [customer.peppyrus.be](https://customer.peppyrus.be)
2. Allez dans **Settings** ou **API Keys**
3. Copiez votre clé API
4. Collez-la dans Dolibarr

⚠️ **Important :** Ne partagez jamais votre clé API publiquement !

#### 3. Votre identifiant Peppol

Format : `SCHEME:IDENTIFIER`

**Exemples :**
- Belgique : `9925:be0838264694`
- Pays-Bas : `9925:nl123456789B01`
- France : `9957:fr12345678901`

Voir la section [Identifiants Peppol par pays](#identifiants-peppol-par-pays) pour plus de détails.

### Sauvegarder la configuration

Cliquez sur **Enregistrer** pour appliquer les modifications.

---

## Configuration de votre entreprise

Pour que vos factures soient conformes PEPPOL, configurez correctement votre entreprise.

### Informations obligatoires

#### 1. Coordonnées bancaires

**Chemin :** Configuration → Société/Organisation → Section "Informations bancaires"

**Champs requis :**
- **IBAN** : Format sans espaces (ex: `BE68539007547034`)
- **BIC/SWIFT** : Code BIC de votre banque (ex: `GKCCBEBB`)

⚠️ **Erreur BR-61 :** Sans IBAN configuré, la validation UBL échouera !

#### 2. Informations légales

**Chemin :** Configuration → Société/Organisation

**Champs requis :**
- **Nom de l'entreprise**
- **Adresse complète**
- **Numéro de TVA**
- **Numéro d'entreprise**
- **Email** et **Téléphone**

### Vérifier votre configuration

Utilisez l'outil de diagnostic pour vérifier que tout est correct :

```
https://votre-dolibarr.com/custom/peppolexport/admin/diagnostic.php
```

---

## Configuration des clients

Chaque client qui recevra des factures Peppol doit avoir un identifiant Peppol configuré.

### Méthode 1 : Champ personnalisé (recommandé)

#### Créer un champ personnalisé

1. Allez dans **Configuration** → **Dictionnaires** → **Champs personnalisés**
2. Sélectionnez **Tiers**
3. Cliquez sur **Nouveau champ**

**Paramètres :**
- **Nom** : `peppol_id`
- **Libellé** : `ID Peppol`
- **Type** : `Texte`
- **Taille** : `100`
- **Aide** : `Format: 9925:be0123456789`
- **Obligatoire** : Non
- **Visible** : Liste et fiche

#### Remplir le champ

1. Ouvrez la **fiche client**
2. Allez dans l'onglet **Autres**
3. Remplissez le champ **ID Peppol**
4. Format : `9925:be0123456789`

### Méthode 2 : Utiliser "ID Prof 6"

Si vous ne voulez pas créer de champ personnalisé :

1. Ouvrez la **fiche client**
2. Section **Informations diverses**
3. Champ **ID Prof 6**
4. Entrez l'identifiant Peppol : `9925:be0123456789`

### Vérifier un participant

Pour vérifier qu'un client existe dans l'annuaire Peppol :

1. Ouvrez une **facture** pour ce client
2. Cliquez sur **🔍 Rechercher dans Peppol**
3. Vérifiez le résultat

---

## Identifiants Peppol par pays

Les identifiants Peppol ont le format : `SCHEME:IDENTIFIER`

### Europe

| Pays | Scheme | Format | Exemple |
|------|--------|--------|---------|
| 🇧🇪 **Belgique** | 9925 | 9925:beXXXXXXXXXX | `9925:be0838264694` |
| 🇳🇱 **Pays-Bas** | 9925 | 9925:nlXXXXXXXXXXXX | `9925:nl123456789B01` |
| 🇫🇷 **France** | 9957 | 9957:frXXXXXXXXXXX | `9957:fr12345678901` |
| 🇩🇪 **Allemagne** | 9930 | 9930:deXXXXXXXXXX | `9930:de123456789` |
| 🇮🇹 **Italie** | 9907 | 9907:itXXXXXXXXXXX | `9907:it12345678901` |
| 🇪🇸 **Espagne** | 9920 | 9920:esXXXXXXXXX | `9920:esA12345678` |
| 🇬🇧 **Royaume-Uni** | 9933 | 9933:gbXXXXXXXXXXXX | `9933:gb123456789012` |
| 🇸🇪 **Suède** | 9955 | 9955:seXXXXXXXXXXXX | `9955:se556123456701` |
| 🇳🇴 **Norvège** | 9908 | 9908:noXXXXXXXXXX | `9908:no123456785` |
| 🇩🇰 **Danemark** | 9901 | 9901:dkXXXXXXXX | `9901:dk12345678` |

### Autres schemes courants

| Scheme | Description | Exemple |
|--------|-------------|---------|
| 0007 | SE:ORGNR (Suède) | `0007:2021005489` |
| 0088 | GLN (Global Location Number) | `0088:1234567890123` |
| 0096 | DUNS (Dun & Bradstreet) | `0096:123456789` |
| 0184 | PEPPOL Participant ID | `0184:participant-id` |

### Comment trouver le bon scheme ?

1. Consultez la [liste complète des schemes EAS](https://docs.peppol.eu/poacc/billing/3.0/codelist/eas/)
2. Ou demandez directement à votre client son identifiant Peppol
3. Utilisez l'annuaire Peppol : [directory.peppol.eu](https://directory.peppol.eu/)

---

## Configuration avancée

### Mode test vs Production

#### Passer en mode test

Pour tester sans envoyer de vraies factures :

1. Configuration du module
2. URL API → `https://api.test.peppyrus.be/v1`
3. Utilisez une clé API de test

#### Passer en production

Quand vous êtes prêt :

1. Configuration du module
2. URL API → `https://api.peppyrus.be/v1`
3. Utilisez votre vraie clé API

### Logs et débogage

Les envois sont automatiquement enregistrés dans la base de données.

**Table :** `llx_peppolexport_log`

**Consulter les logs :**
```sql
SELECT * FROM llx_peppolexport_log 
WHERE fk_facture = 123 
ORDER BY date_export DESC;
```

### Permissions utilisateurs

Par défaut, les utilisateurs doivent avoir :
- ✅ Droit de **lecture des factures**
- ✅ Droit **"Exporter vers Peppol"**

**Pour donner le droit :**
1. Configuration → Utilisateurs/Groupes
2. Sélectionnez l'utilisateur
3. Onglet **Permissions**
4. Module **Peppol Export** → Cochez **"Exporter les factures vers Peppol"**

---

## Résolution des problèmes

### Erreur : "API Key Missing"

**Cause :** La clé API n'est pas configurée.

**Solution :**
1. Allez dans la configuration du module
2. Renseignez votre clé API Peppyrus
3. Sauvegardez

### Erreur : "Sender Peppol ID not configured"

**Cause :** Votre identifiant Peppol n'est pas configuré.

**Solution :**
1. Allez dans la configuration du module
2. Renseignez votre ID Peppol (format: `9925:be0123456789`)
3. Sauvegardez

### Erreur : "Recipient Peppol ID not configured"

**Cause :** Le client n'a pas d'identifiant Peppol.

**Solution :**
1. Ouvrez la fiche du client
2. Ajoutez son identifiant Peppol dans le champ approprié
3. Format : `9925:be0123456789`

### Erreur BR-61 : "IBAN manquant"

**Cause :** Votre IBAN n'est pas configuré dans Dolibarr.

**Solution :**
1. Configuration → Société/Organisation
2. Section "Informations bancaires"
3. Ajoutez votre IBAN (sans espaces)
4. Ajoutez votre BIC

### Erreur BR-25 : "Item name manquant"

**Cause :** Une ligne de facture n'a pas de libellé.

**Solution :**
1. Ouvrez la facture
2. Vérifiez que chaque ligne a un libellé
3. Modifiez si nécessaire
4. Réessayez l'envoi

### Les boutons n'apparaissent pas

**Causes possibles :**
1. Facture non validée
2. Cache navigateur
3. JavaScript bloqué

**Solutions :**
1. Validez la facture (statut ≥ 1)
2. Videz le cache navigateur (Ctrl+Shift+R)
3. Vérifiez la console JavaScript (F12)

### Participant non trouvé dans l'annuaire

**Cause :** Le client n'est pas enregistré sur le réseau Peppol.

**Solutions :**
1. Vérifiez l'identifiant Peppol du client
2. Demandez au client de s'inscrire sur Peppol
3. Vérifiez sur [directory.peppol.eu](https://directory.peppol.eu/)

---

## Étapes suivantes

Maintenant que votre configuration est complète :

1. 🧪 [Testez votre configuration](testing.md)
2. 📤 Envoyez votre première facture !
3. 📊 Consultez les logs d'envoi

---

## Support

- 🐛 **Bugs** : [GitHub Issues](https://github.com/votre-username/dolibarr-peppol-export/issues)
- 💬 **Questions** : [GitHub Discussions](https://github.com/votre-username/dolibarr-peppol-export/discussions)
- 📖 **Documentation** : [docs/](https://github.com/votre-username/dolibarr-peppol-export/tree/main/docs)
