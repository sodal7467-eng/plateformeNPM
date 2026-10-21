-- =========================================================
-- Base de données Quincaillerie NPM
-- Structure complète — à importer sur une base VIDE.
--
-- Utilisation :
--   1. CREATE DATABASE nom_boutique CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--   2. mysql -uroot nom_boutique < database/schema.sql
--   3. Ouvrir installation.php dans le navigateur pour créer le premier admin.
-- =========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;


-- =========================================================
-- 1) UTILISATEURS (clients, employés, administrateurs)
-- =========================================================
CREATE TABLE IF NOT EXISTS utilisateurs (
    id            INT PRIMARY KEY AUTO_INCREMENT,
    nom           VARCHAR(100) NOT NULL,
    email         VARCHAR(150) DEFAULT NULL,
    telephone     VARCHAR(30) DEFAULT NULL,
    mot_de_passe  VARCHAR(255) NOT NULL,
    role          VARCHAR(20) NOT NULL DEFAULT 'client',
    type_client   ENUM('en_ligne','boutique') NOT NULL DEFAULT 'en_ligne',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================
-- 2) CATÉGORIES DE PRODUITS
-- =========================================================
CREATE TABLE IF NOT EXISTS categories (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    nom         VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    image       VARCHAR(255) DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================
-- 3) PRODUITS
-- =========================================================
CREATE TABLE IF NOT EXISTS produits (
    id            INT PRIMARY KEY AUTO_INCREMENT,
    nom           VARCHAR(255) NOT NULL,
    description   TEXT DEFAULT NULL,
    prix          DECIMAL(10,2) NOT NULL,
    stock         INT NOT NULL DEFAULT 0,
    image         VARCHAR(255) DEFAULT NULL,
    categorie     VARCHAR(255) NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================
-- 4) COMMANDES (en ligne ou en boutique)
-- =========================================================
CREATE TABLE IF NOT EXISTS commandes (
    id             INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT NOT NULL,
    total          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    statut         VARCHAR(50) NOT NULL DEFAULT 'En attente',
    type_vente     ENUM('en_ligne','boutique') NOT NULL DEFAULT 'en_ligne',
    date_commande  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY fk_commandes_utilisateur (utilisateur_id),
    CONSTRAINT fk_commandes_utilisateur FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateurs (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================
-- 5) LIGNES DE COMMANDE
-- =========================================================
CREATE TABLE IF NOT EXISTS commande_details (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    commande_id INT NOT NULL,
    produit_id  INT NOT NULL,
    quantite    INT NOT NULL,
    prix        DECIMAL(10,2) NOT NULL,
    KEY fk_details_commande (commande_id),
    KEY fk_details_produit  (produit_id),
    CONSTRAINT fk_details_commande FOREIGN KEY (commande_id)
        REFERENCES commandes (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_details_produit  FOREIGN KEY (produit_id)
        REFERENCES produits  (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================
-- 6) FACTURES
-- =========================================================
CREATE TABLE IF NOT EXISTS factures (
    id             INT PRIMARY KEY AUTO_INCREMENT,
    commande_id    INT NOT NULL,
    numero_facture VARCHAR(50) NOT NULL,
    token_public   VARCHAR(64) DEFAULT NULL,
    date_facture   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    montant_total  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    montant_paye   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    reste_a_payer  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    statut         VARCHAR(50) NOT NULL DEFAULT 'Impayée',
    UNIQUE KEY uniq_numero (numero_facture),
    UNIQUE KEY uniq_token_public (token_public),
    KEY fk_facture_commande (commande_id),
    CONSTRAINT fk_facture_commande FOREIGN KEY (commande_id)
        REFERENCES commandes (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================
-- 7) CRÉDITS CLIENTS (ce que les clients doivent encore)
-- =========================================================
CREATE TABLE IF NOT EXISTS credits_clients (
    id             INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT NOT NULL,
    commande_id    INT DEFAULT NULL,
    montant_total  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    montant_paye   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    reste_a_payer  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    statut         VARCHAR(50) NOT NULL DEFAULT 'En cours',
    date_credit    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY fk_credit_utilisateur (utilisateur_id),
    KEY fk_credit_commande    (commande_id),
    CONSTRAINT fk_credit_utilisateur FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateurs (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_credit_commande    FOREIGN KEY (commande_id)
        REFERENCES commandes    (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================
-- 8) PAIEMENTS SUR CRÉDIT (règlements partiels)
-- =========================================================
CREATE TABLE IF NOT EXISTS paiements_credits (
    id             INT PRIMARY KEY AUTO_INCREMENT,
    credit_id      INT NOT NULL,
    montant        DECIMAL(10,2) NOT NULL,
    date_paiement  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    commentaire    VARCHAR(255) DEFAULT NULL,
    KEY fk_paiement_credit (credit_id),
    CONSTRAINT fk_paiement_credit FOREIGN KEY (credit_id)
        REFERENCES credits_clients (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================
-- 9) DÉPENSES DE LA BOUTIQUE
-- =========================================================
CREATE TABLE IF NOT EXISTS depenses (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    description  VARCHAR(255) NOT NULL,
    categorie    VARCHAR(100) DEFAULT NULL,
    montant      DECIMAL(12,2) NOT NULL,
    date_depense DATE NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================
-- 10) MESSAGES DU FORMULAIRE DE CONTACT
-- =========================================================
CREATE TABLE IF NOT EXISTS messages (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    nom          VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NOT NULL,
    telephone    VARCHAR(30) DEFAULT NULL,
    sujet        VARCHAR(200) NOT NULL,
    message      TEXT NOT NULL,
    lu           TINYINT(1) NOT NULL DEFAULT 0,
    date_message TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================
-- 11) PARAMÈTRES MODIFIABLES DE LA BOUTIQUE
-- =========================================================
CREATE TABLE IF NOT EXISTS parametres (
    id                INT PRIMARY KEY AUTO_INCREMENT,
    cle               VARCHAR(100) UNIQUE NOT NULL,
    valeur            TEXT DEFAULT NULL,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO parametres (cle, valeur) VALUES
('nom_boutique',   'Ma Quincaillerie'),
('slogan',         'Tout pour reussir vos projets'),
('adresse',        ''),
('telephone',      ''),
('email_contact',  ''),
('devise',         'FCFA'),
('logo_texte',     'MQ'),
('logo_soustitre', 'QUINCAILLERIE'),
('footer_texte',   'Votre partenaire pour vos besoins en outils et materiaux.');


-- =========================================================
-- 12) RÉINITIALISATION DE MOT DE PASSE (tokens à usage unique)
-- =========================================================
CREATE TABLE IF NOT EXISTS resets_mot_de_passe (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id  INT NOT NULL,
    token_hash      VARCHAR(255) NOT NULL,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_expiration DATETIME NOT NULL,
    utilise         TINYINT(1) DEFAULT 0,
    KEY idx_token       (token_hash),
    KEY idx_utilisateur (utilisateur_id),
    CONSTRAINT fk_reset_utilisateur FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateurs (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================
-- 13) TENTATIVES DE CONNEXION (rate limiting anti brute-force)
-- =========================================================
CREATE TABLE IF NOT EXISTS tentatives_connexion (
    id             INT PRIMARY KEY AUTO_INCREMENT,
    ip             VARCHAR(45) NOT NULL,
    identifiant    VARCHAR(150) DEFAULT NULL,
    succes         TINYINT(1) DEFAULT 0,
    date_tentative TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_ip_date (ip, date_tentative)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================
-- 14) JOURNAL DES ACTIONS ADMINISTRATEUR
-- =========================================================
CREATE TABLE IF NOT EXISTS logs_admin (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    admin_id    INT NOT NULL,
    admin_nom   VARCHAR(150) DEFAULT NULL,
    action      VARCHAR(100) NOT NULL,
    cible_type  VARCHAR(50) DEFAULT NULL,
    cible_id    INT DEFAULT NULL,
    details     TEXT DEFAULT NULL,
    ip          VARCHAR(45) DEFAULT NULL,
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_admin  (admin_id),
    KEY idx_date   (date_action),
    KEY idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================
-- 15) NOTIFICATIONS CLIENT (cloche du site)
-- =========================================================
CREATE TABLE IF NOT EXISTS notifications_clients (
    id               INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id   INT NOT NULL,
    message          VARCHAR(255) NOT NULL,
    lien             VARCHAR(255) DEFAULT NULL,
    lu               TINYINT(1) NOT NULL DEFAULT 0,
    date_creation    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_utilisateur_lu (utilisateur_id, lu),
    CONSTRAINT fk_notification_utilisateur FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateurs (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


SET FOREIGN_KEY_CHECKS = 1;
