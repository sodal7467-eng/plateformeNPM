-- =========================================================
-- Comptes de test (évaluation / démonstration)
--
-- À importer APRÈS schema.sql :
--   mysql -uroot quincaillerie_npm < database/comptes_test.sql
--
-- Mots de passe en clair (voir README, section « Comptes de test ») :
--   Admin   : admin@npm-test.com   / Admin@2026
--   Employé : employe@npm-test.com / Employe@2026
--   Client  : client@npm-test.com  / Client@2026
--
-- Ne pas utiliser ces comptes en production.
-- =========================================================

SET NAMES utf8mb4;

INSERT INTO utilisateurs (nom, email, telephone, mot_de_passe, role, type_client)
VALUES
    ('Admin Test',   'admin@npm-test.com',   '770000001',
     '$2y$10$12z8FICsqxTpV71rT6TFeumxY3PjYArmIBt/2NOEqOUiQ3ppsDQRm', 'admin',   'en_ligne'),
    ('Employé Test', 'employe@npm-test.com', '770000002',
     '$2y$10$AliT9FS9e84HBBFwWN0LYurxtNypqoEx1Mqo8U6GyvIhpBs2J39Ay', 'employe', 'en_ligne'),
    ('Client Test',  'client@npm-test.com',  '770000003',
     '$2y$10$rlYHiTNnmHMDcVbTRZ2d3e8CCuSJ1aEAoLXzVcL1CcdBVNj0tupMa', 'client',  'en_ligne')
ON DUPLICATE KEY UPDATE
    nom          = VALUES(nom),
    telephone    = VALUES(telephone),
    mot_de_passe = VALUES(mot_de_passe),
    role         = VALUES(role);
