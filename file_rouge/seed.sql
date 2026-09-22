-- ==========================================================
-- Application : Gestion de Finances Personnelles
-- Fichier     : seed.sql
-- Description : Données de test réalistes
-- Remarque    : Le mot de passe de tous les utilisateurs de test est : password123
-- ==========================================================

USE `gestion_finances`;

-- Désactivation temporaire des contraintes pour réinitialisation propre
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `Transaction`;
TRUNCATE TABLE `Categorie_depense`;
TRUNCATE TABLE `Compte_bancaire`;
TRUNCATE TABLE `Utilisateur`;
SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================================
-- 1. Insertion des Utilisateurs (3 utilisateurs)
-- Hash généré via password_hash('password123', PASSWORD_BCRYPT)
-- ==========================================================
INSERT INTO `Utilisateur` (`id_utilisateur`, `nom`, `prenom`, `email`, `mot_de_passe`, `date_creation`) VALUES
(1, 'Dubois', 'Thomas', 'thomas.dubois@email.com', '$2y$10$efteiVNJ4JgrfzWnDZApKe6uHjFrTCyEPfsUViWqazasj4sc4eb9K', '2023-01-10 09:30:00'),
(2, 'Mansouri', 'Sarah', 'sarah.mansouri@email.com', '$2y$10$efteiVNJ4JgrfzWnDZApKe6uHjFrTCyEPfsUViWqazasj4sc4eb9K', '2023-03-15 14:15:00'),
(3, 'Martin', 'Lucas', 'lucas.martin@email.com', '$2y$10$efteiVNJ4JgrfzWnDZApKe6uHjFrTCyEPfsUViWqazasj4sc4eb9K', '2023-06-01 11:00:00');

-- ==========================================================
-- 2. Insertion des Catégories de Dépense / Revenu (6 catégories)
-- ==========================================================
INSERT INTO `Categorie_depense` (`id_categorie`, `nom_categorie`, `type`, `icone`) VALUES
(1, 'Alimentation', 'depense', '🛒'),
(2, 'Transport',    'depense', '🚗'),
(3, 'Loisirs',      'depense', '🎬'),
(4, 'Salaire',      'revenu',  '💼'),
(5, 'Loyer',        'depense', '🏠'),
(6, 'Santé',        'depense', '💊');

-- ==========================================================
-- 3. Insertion des Comptes Bancaires (2 comptes par utilisateur = 6 comptes)
-- Les soldes ci-dessous correspondent exactement au cumul des transactions insérées ci-après.
-- ==========================================================
INSERT INTO `Compte_bancaire` (`id_compte`, `id_utilisateur`, `nom_compte`, `type_compte`, `solde`, `date_ouverture`) VALUES
(1, 1, 'Compte Courant BNP',      'courant', 1587.50, '2023-01-15'),
(2, 1, 'Livret A Épargne',        'epargne', 3150.00, '2023-02-01'),
(3, 2, 'Compte Courant BoursoBank','courant', 2089.50, '2023-03-20'),
(4, 2, 'PEL Épargne Logement',    'epargne', 5500.00, '2023-04-01'),
(5, 3, 'Compte Chèque SG',        'courant', 1297.20, '2023-06-05'),
(6, 3, 'Compte Investissement',   'autre',   1750.00, '2023-06-15');

-- ==========================================================
-- 4. Insertion des Transactions (20 transactions réalistes)
-- ==========================================================
INSERT INTO `Transaction` (`id_transaction`, `id_compte`, `id_categorie`, `montant`, `type_transaction`, `description`, `date_transaction`) VALUES
-- Transactions pour Thomas Dubois - Compte Courant BNP (id_compte = 1)
(1,  1, 4, 2500.00, 'credit', 'Virement Salaire Entreprise ACME', '2024-01-28'),
(2,  1, 5, 750.00,  'debit',  'Paiement Loyer Janvier',            '2024-02-01'),
(3,  1, 1, 85.50,   'debit',  'Courses hebdomadaires Carrefour',   '2024-02-03'),
(4,  1, 2, 45.00,   'debit',  'Plein carburant station Total',     '2024-02-05'),
(5,  1, 3, 32.00,   'debit',  'Séance cinéma UGC & boissons',      '2024-02-08'),

-- Transactions pour Thomas Dubois - Livret A Épargne (id_compte = 2)
(6,  2, 4, 3000.00, 'credit', 'Virement versement initial livret', '2023-02-01'),
(7,  2, 4, 150.00,  'credit', 'Prime annuelle & intérêts',         '2023-12-31'),

-- Transactions pour Sarah Mansouri - Compte Courant BoursoBank (id_compte = 3)
(8,  3, 4, 3200.00, 'credit', 'Virement Salaire Cabinet Conseil',  '2024-01-27'),
(9,  3, 5, 890.00,  'debit',  'Loyer Appartement F3',              '2024-02-02'),
(10, 3, 1, 120.00,  'debit',  'Courses Bio Monoprix',              '2024-02-04'),
(11, 3, 6, 65.00,   'debit',  'Consultation Ophtalmologiste',      '2024-02-07'),
(12, 3, 2, 35.50,   'debit',  'Recharge Pass Navigo Mensuel',      '2024-02-09'),

-- Transactions pour Sarah Mansouri - PEL Épargne (id_compte = 4)
(13, 4, 4, 5000.00, 'credit', 'Apport initial épargne logement',   '2023-04-01'),
(14, 4, 4, 500.00,  'credit', 'Virement mensuel épargne programmée','2024-01-05'),

-- Transactions pour Lucas Martin - Compte Chèque SG (id_compte = 5)
(15, 5, 4, 1950.00, 'credit', 'Virement Salaire Développeur Junior','2024-01-29'),
(16, 5, 5, 550.00,  'debit',  'Loyer Studio Meublé',               '2024-02-01'),
(17, 5, 6, 42.80,   'debit',  'Pharmacie & médicaments',           '2024-02-06'),
(18, 5, 3, 60.00,   'debit',  'Sortie Restaurant Italien',         '2024-02-10'),

-- Transactions pour Lucas Martin - Compte Investissement (id_compte = 6)
(19, 6, 4, 2000.00, 'credit', 'Dépôt initial investissement ETF',  '2023-06-15'),
(20, 6, 3, 250.00,  'debit',  'Frais de courtage et achat parts',  '2023-07-02');
