# Plateforme NPM — Boutique en ligne + Back-office

Application PHP/MySQL pour une quincaillerie (ou tout petit commerce).
Vitrine e-commerce côté client + espace administration complet
(produits, ventes en magasin, factures, crédits clients, dépenses,
rapport mensuel, gestion d'équipe).

---

## 🔑 Comptes de test

Ces comptes sont créés en important `database/comptes_test.sql`
(après `schema.sql`). Le fichier `database/catalogue_demo.sql` ajoute
un catalogue de démonstration (6 catégories, 59 produits avec leurs
images, déjà présentes dans `uploads/`) :

```bash
mysql -uroot -e "CREATE DATABASE quincaillerie_npm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -uroot quincaillerie_npm < database/schema.sql
mysql -uroot quincaillerie_npm < database/comptes_test.sql
mysql -uroot quincaillerie_npm < database/catalogue_demo.sql
```

Sans fichier `.env`, l'application utilise par défaut la base
`quincaillerie_npm` sur `localhost` avec l'utilisateur `root` sans mot
de passe (configuration XAMPP standard).

| Rôle | Email | Téléphone | Mot de passe | Accès après connexion |
|------|-------|-----------|--------------|-----------------------|
| **Administrateur** | `admin@npm-test.com` | `770000001` | `Admin@2026` | Back-office `admin/` (produits, ventes, factures, crédits, dépenses, rapports, équipe, paramètres) |
| **Employé** | `employe@npm-test.com` | `770000002` | `Employe@2026` | Boutique (compte membre de l'équipe, visible dans Admin → Employés) |
| **Client** | `client@npm-test.com` | `770000003` | `Client@2026` | Boutique : panier, commandes, factures, notifications, profil |

**Se connecter :** page `connexion.php`, choisir *Email* ou *Téléphone*
puis saisir l'identifiant et le mot de passe ci-dessus.
L'administrateur est redirigé automatiquement vers `admin/index.php`.

> ⚠️ Comptes réservés à l'évaluation / démonstration — à supprimer
> avant une mise en production réelle.

---

## 1. Prérequis

- **PHP 8.0+**
- **MySQL 5.7+** ou MariaDB 10.3+
- **Apache** avec `mod_rewrite`
- Un environnement local type **XAMPP** / **Laragon** pour développer,
  un **VPS Linux** (Ubuntu/Debian) ou un mutualisé pour la mise en ligne.

---

## 2. Installation (méthode simple — assistant graphique)

L'application inclut un **assistant d'installation** qui fait tout
pour vous en 5 étapes.

```bash
# 1. Copier le projet sur le serveur
#    Local : C:\xampp\htdocs\plateformeNPM
#    VPS   : /var/www/html/monsite

# 2. Démarrer Apache + MySQL
```

Ouvrir dans le navigateur : **http://votre-domaine/installation.php**

L'assistant vérifie les prérequis, crée la base, importe le schéma,
crée le premier administrateur et personnalise la boutique
(nom, logo, coordonnées). À la fin il se supprime lui-même.

Accès ensuite :
- Site public : http://localhost/plateformeNPM/
- Admin : http://localhost/plateformeNPM/admin/ (avec le compte créé)


## 2 bis. Installation manuelle (si vous préférez)

```bash
mysql -uroot -e "CREATE DATABASE quincaillerie_npm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -uroot quincaillerie_npm < database/schema.sql
mysql -uroot quincaillerie_npm < database/comptes_test.sql   # comptes de test
mysql -uroot quincaillerie_npm < database/catalogue_demo.sql # catalogue + images
cp .env.example .env
# Éditer .env (DB_USER, DB_PASSWORD, APP_URL)
```

Puis créer manuellement le premier admin en base (voir section 4).

---

## 3. Configuration (`.env`)

| Variable | Description | Défaut |
|----------|-------------|--------|
| `APP_ENV` | `development` ou `production` | `production` |
| `APP_DEBUG` | Affiche les erreurs DB à l'écran si `true` | `false` |
| `APP_URL` | URL publique du site (sans slash final) | — |
| `DB_HOST` / `DB_NAME` / `DB_USER` / `DB_PASSWORD` | Connexion MySQL | — |
| `LOGIN_MAX_ATTEMPTS` | Nombre de tentatives avant blocage | `5` |
| `LOGIN_LOCKOUT_MINUTES` | Durée du blocage | `15` |
| `RESET_TOKEN_MINUTES` | Validité d'un lien de réinitialisation | `30` |

Le fichier `.env` **ne doit jamais être committé**. Le `.gitignore`
l'exclut déjà. Un `.env.example` sert de modèle.

---

## 4. Premier admin

Le premier administrateur est à créer directement dans la base
(ou depuis l'espace admin par un autre admin) :

```sql
INSERT INTO utilisateurs (nom, email, telephone, mot_de_passe, role)
VALUES (
    'Mon Nom',
    'moi@boutique.com',
    '77XXXXXXX',
    -- Générer le hash avec :
    --   php -r "echo password_hash('MonMotDePasse', PASSWORD_DEFAULT);"
    '$2y$10$....',
    'admin'
);
```

---

## 5. Personnaliser la boutique

Une fois connecté en admin, aller sur **Paramètres**
(icône ⚙️ dans la barre latérale) pour modifier :
- Le nom de la boutique (utilisé dans le navbar, le footer, les titres)
- Le logo texte + sous-titre
- Le slogan
- Les coordonnées (adresse, téléphone, email)
- Le texte du footer
- La devise (FCFA, EUR, XOF, …)

Aucune modification de code n'est nécessaire pour installer chez un
nouveau client.

---

## 6. Sécurité

- Toutes les requêtes SQL utilisent des **prepared statements** (PDO).
- Les mots de passe sont hashés avec `password_hash` (bcrypt).
- Un jeton **CSRF** protège les actions POST sensibles.
- Un **rate-limiting par IP** bloque le brute-force du login.
- Un **journal d'actions admin** trace les suppressions et modifications.
- Les erreurs de base sont masquées en production (`APP_DEBUG=false`).

**À faire côté serveur en production :**
- Servir en **HTTPS** (Let's Encrypt gratuit).
- Rendre `.env` non lisible par le web (déjà interdit par `.htaccess`).
- Sauvegarde quotidienne de la base (`mysqldump` en cron).
- Journal Apache/PHP centralisé.

---

## 7. Déploiement sur VPS Linux

```bash
# Sur le serveur
sudo apt install apache2 php php-mysql mysql-server unzip
sudo a2enmod rewrite && sudo systemctl restart apache2

# Envoyer le projet dans /var/www/plateformeNPM
# Puis :
cd /var/www/plateformeNPM
cp .env.example .env
nano .env   # renseigner les identifiants DB de prod
sudo chown -R www-data:www-data uploads logs

# Créer la base + importer les schémas
mysql -uroot -p < database/schema.sql
mysql -uroot -p < database/schema_v2.sql

# Certificat HTTPS
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d monsite.com
```

Un `VirtualHost` Apache doit pointer sur `/var/www/plateformeNPM/`
avec `AllowOverride All` pour respecter les `.htaccess`.

---

## 8. Sauvegarde de la base

À mettre dans `crontab -e` :

```
0 2 * * * mysqldump -uroot -pMOTDEPASSE quincaillerie_npm | gzip > /backups/npm_$(date +\%F).sql.gz
```

---

## 9. Support & évolutions

Projet développé par **Soda Lô** (sodal7467@gmail.com).
