-- =========================================================
-- Extensions v2 de la base Quincaillerie NPM
-- - parametres          : paramètres modifiables de la boutique
-- - resets_mot_de_passe : tokens de réinitialisation
-- - tentatives_connexion: rate limiting du login
-- - logs_admin          : journal des actions administrateur
-- =========================================================

CREATE TABLE IF NOT EXISTS parametres (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cle VARCHAR(100) UNIQUE NOT NULL,
    valeur TEXT,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO parametres (cle, valeur) VALUES
('nom_boutique',   'NPM Quincaillerie'),
('slogan',         'Tout pour reussir vos projets'),
('adresse',        'Dakar, Senegal'),
('telephone',      '+221 77 000 00 00'),
('email_contact',  'contact@npm.sn'),
('devise',         'FCFA'),
('logo_texte',     'NPM'),
('logo_soustitre', 'QUINCAILLERIE'),
('footer_texte',   'Votre partenaire pour vos besoins en outils, materiaux et equipements de quincaillerie.');


CREATE TABLE IF NOT EXISTS resets_mot_de_passe (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_expiration DATETIME NOT NULL,
    utilise TINYINT(1) DEFAULT 0,
    INDEX idx_token (token_hash),
    INDEX idx_utilisateur (utilisateur_id),
    CONSTRAINT fk_reset_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS tentatives_connexion (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ip VARCHAR(45) NOT NULL,
    identifiant VARCHAR(150),
    succes TINYINT(1) DEFAULT 0,
    date_tentative TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_date (ip, date_tentative)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS logs_admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    admin_nom VARCHAR(150),
    action VARCHAR(100) NOT NULL,
    cible_type VARCHAR(50),
    cible_id INT,
    details TEXT,
    ip VARCHAR(45),
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin (admin_id),
    INDEX idx_date (date_action),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
