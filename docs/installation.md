# Guide d'installation - Dolibarr Peppol Export

## Table des matières

- [Prérequis](#prérequis)
- [Méthode 1 : Installation automatique](#méthode-1--installation-automatique-recommandée)
- [Méthode 2 : Installation manuelle](#méthode-2--installation-manuelle)
- [Méthode 3 : Installation via Git](#méthode-3--installation-via-git)
- [Vérification de l'installation](#vérification-de-linstallation)
- [Résolution des problèmes](#résolution-des-problèmes)

---

## Prérequis

### Serveur

- **Dolibarr** : Version 11.0 ou supérieure (testé sur 19.0 et 20.x)
- **PHP** : Version 7.0 ou supérieure
- **Extensions PHP requises** :
  - `curl` - Pour les appels API
  - `json` - Pour le traitement JSON
  - `openssl` - Pour les connexions HTTPS
  - `xml` - Pour la génération UBL
  - `dom` - Pour la manipulation XML

### Vérifier les extensions PHP

```bash
php -m | grep -E '(curl|json|openssl|xml|dom)'
```

Si des extensions manquent :

```bash
# Sur Ubuntu/Debian
sudo apt-get install php-curl php-xml php-json

# Sur CentOS/RHEL
sudo yum install php-curl php-xml php-json
```

### Compte Peppyrus

Avant d'installer le module, créez un compte gratuit :

1. Allez sur [peppyrus.be](https://peppyrus.be)
2. Cliquez sur "Register" ou "S'inscrire"
3. Complétez le formulaire d'inscription
4. Vérifiez votre email
5. Connectez-vous à [customer.peppyrus.be](https://customer.peppyrus.be)
6. Notez votre **API Key** (dans Settings)
7. Notez votre **Peppol Participant ID**

---

## Méthode 1 : Installation automatique (recommandée)

### Étape 1 : Télécharger l'installateur

Téléchargez le fichier `install_peppolexport.php` depuis GitHub.

### Étape 2 : Placer l'installateur

Placez le fichier dans le répertoire `/htdocs/custom/` de votre installation Dolibarr :

```bash
mv install_peppolexport.php /var/www/html/dolibarr/htdocs/custom/
```

### Étape 3 : Exécuter l'installateur

Accédez à l'installateur via votre navigateur :

```
https://votre-domaine.com/custom/install_peppolexport.php
```

### Étape 4 : Suivre les instructions

L'installateur va :
1. ✅ Vérifier les prérequis
2. ✅ Télécharger le module depuis GitHub
3. ✅ Extraire les fichiers
4. ✅ Définir les permissions correctes
5. ✅ Afficher les instructions d'activation

### Étape 5 : Supprimer l'installateur

**IMPORTANT** : Pour des raisons de sécurité, supprimez le fichier d'installation :

```bash
rm /var/www/html/dolibarr/htdocs/custom/install_peppolexport.php
```

### Étape 6 : Activer le module

1. Connectez-vous à Dolibarr en tant qu'administrateur
2. Allez dans **Configuration** → **Modules/Applications**
3. Recherchez **"Peppol Export"**
4. Cliquez sur **Activer/On**

---

## Méthode 2 : Installation manuelle

### Étape 1 : Télécharger le module

Téléchargez la dernière version depuis GitHub :

```bash
cd /tmp
wget https://github.com/votre-username/dolibarr-peppol-export/archive/refs/heads/main.zip
```

### Étape 2 : Extraire l'archive

```bash
unzip main.zip
```

### Étape 3 : Copier vers Dolibarr

```bash
sudo cp -r dolibarr-peppol-export-main /var/www/html/dolibarr/htdocs/custom/peppolexport
```

### Étape 4 : Définir les permissions

```bash
sudo chown -R www-data:www-data /var/www/html/dolibarr/htdocs/custom/peppolexport
sudo chmod -R 755 /var/www/html/dolibarr/htdocs/custom/peppolexport
sudo chmod 644 /var/www/html/dolibarr/htdocs/custom/peppolexport/class/*.php
```

### Étape 5 : Activer le module

1. Connectez-vous à Dolibarr en tant qu'administrateur
2. Allez dans **Configuration** → **Modules/Applications**
3. Recherchez **"Peppol Export"**
4. Cliquez sur **Activer/On**

---

## Méthode 3 : Installation via Git

### Étape 1 : Cloner le dépôt

```bash
cd /var/www/html/dolibarr/htdocs/custom/
sudo git clone https://github.com/votre-username/dolibarr-peppol-export.git peppolexport
```

### Étape 2 : Définir les permissions

```bash
sudo chown -R www-data:www-data peppolexport/
sudo chmod -R 755 peppolexport/
```

### Étape 3 : Activer le module

1. Connectez-vous à Dolibarr en tant qu'administrateur
2. Allez dans **Configuration** → **Modules/Applications**
3. Recherchez **"Peppol Export"**
4. Cliquez sur **Activer/On**

---

## Vérification de l'installation

### 1. Vérifier que le module apparaît

Allez dans **Configuration** → **Modules/Applications** et recherchez "Peppol Export".

### 2. Vérifier les tables SQL

Les tables suivantes doivent avoir été créées :

```sql
SELECT * FROM information_schema.tables 
WHERE table_schema = 'dolibarr' 
AND table_name = 'llx_peppolexport_log';
```

### 3. Tester la configuration

Utilisez l'outil de diagnostic :

```
https://votre-domaine.com/custom/peppolexport/admin/diagnostic.php
```

### 4. Vérifier les permissions

```bash
ls -la /var/www/html/dolibarr/htdocs/custom/peppolexport/
```

Vous devriez voir :
- Propriétaire : `www-data` (ou votre utilisateur web)
- Permissions répertoires : `755` (drwxr-xr-x)
- Permissions fichiers : `644` (-rw-r--r--)

---

## Résolution des problèmes

### Le module n'apparaît pas dans la liste

**Causes possibles :**
1. Mauvais emplacement des fichiers
2. Permissions incorrectes
3. Cache Dolibarr non vidé

**Solutions :**

1. Vérifiez l'emplacement :
```bash
ls -la /var/www/html/dolibarr/htdocs/custom/peppolexport/core/modules/modPeppolExport.class.php
```

2. Vérifiez les permissions :
```bash
sudo chown -R www-data:www-data /var/www/html/dolibarr/htdocs/custom/peppolexport
sudo chmod -R 755 /var/www/html/dolibarr/htdocs/custom/peppolexport
```

3. Videz le cache Dolibarr :
   - **Configuration** → **Sécurité** → **Vider le cache**

4. Redémarrez PHP-FPM/Apache :
```bash
sudo service php7.4-fpm restart
sudo service apache2 restart
```

### Erreur "Cannot load Dolibarr"

Le module ne trouve pas `main.inc.php`.

**Solution :**
Vérifiez que votre Dolibarr est bien installé dans le chemin standard :
```bash
ls -la /var/www/html/dolibarr/htdocs/main.inc.php
```

### Erreur SQL lors de l'activation

**Cause :** Problème avec la création de la table.

**Solution :**
Créez la table manuellement :

```sql
CREATE TABLE IF NOT EXISTS llx_peppolexport_log (
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

### Extensions PHP manquantes

**Erreur :** `Call to undefined function curl_init()`

**Solution :**
```bash
# Ubuntu/Debian
sudo apt-get install php-curl php-xml php-json
sudo service apache2 restart

# CentOS/RHEL
sudo yum install php-curl php-xml php-json
sudo service httpd restart
```

### Permissions refusées

**Erreur :** `Permission denied` lors de l'écriture

**Solution :**
```bash
sudo chown -R www-data:www-data /var/www/html/dolibarr/htdocs/custom/peppolexport
sudo chmod -R 755 /var/www/html/dolibarr/htdocs/custom/peppolexport
```

---

## Prochaines étapes

Une fois l'installation terminée :

1. 📖 Lisez le [Guide de configuration](configuration.md)
2. 🧪 Testez avec le [Guide de test](testing.md)
3. 🚀 Commencez à envoyer des factures !

---

## Support

- 🐛 **Bugs** : [GitHub Issues](https://github.com/votre-username/dolibarr-peppol-export/issues)
- 💬 **Questions** : [GitHub Discussions](https://github.com/votre-username/dolibarr-peppol-export/discussions)
- 📧 **Forum Dolibarr** : [forum.dolibarr.org](https://forum.dolibarr.org)
