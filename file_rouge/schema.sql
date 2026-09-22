-- ==========================================================
-- Application : Gestion de Finances Personnelles
-- Fichier     : schema.sql
-- Description : Création de la base de données et des tables
-- SGBD        : MySQL 8.x / MariaDB
-- ==========================================================

-- 1. Création de la base de données
CREATE DATABASE IF NOT EXISTS `gestion_finances`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `gestion_finances`;

-- Désactivation temporaire des contraintes pour permettre la réinitialisation propre si nécessaire
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `Transaction`;
DROP TABLE IF EXISTS `Categorie_depense`;
DROP TABLE IF EXISTS `Compte_bancaire`;
DROP TABLE IF EXISTS `Utilisateur`;
SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================================
-- 2. Table : Utilisateur
-- ==========================================================
CREATE TABLE `Utilisateur` (
    `id_utilisateur` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(100) NOT NULL,
    `prenom` VARCHAR(100) NOT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `mot_de_passe` VARCHAR(255) NOT NULL,
    `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 3. Table : Compte_bancaire
-- Relation (1,n) : Un utilisateur possède plusieurs comptes
-- Suppression en cascade : si un utilisateur est supprimé,
-- ses comptes sont automatiquement supprimés.
-- ==========================================================
CREATE TABLE `Compte_bancaire` (
    `id_compte` INT AUTO_INCREMENT PRIMARY KEY,
    `id_utilisateur` INT NOT NULL,
    `nom_compte` VARCHAR(100) NOT NULL,
    `type_compte` ENUM('courant', 'epargne', 'autre') NOT NULL DEFAULT 'courant',
    `solde` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `date_ouverture` DATE NOT NULL,
    CONSTRAINT `fk_compte_utilisateur`
        FOREIGN KEY (`id_utilisateur`)
        REFERENCES `Utilisateur` (`id_utilisateur`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 4. Table : Categorie_depense
-- Types : 'depense' ou 'revenu'
-- ==========================================================
CREATE TABLE `Categorie_depense` (
    `id_categorie` INT AUTO_INCREMENT PRIMARY KEY,
    `nom_categorie` VARCHAR(100) NOT NULL,
    `type` ENUM('depense', 'revenu') NOT NULL DEFAULT 'depense',
    `icone` VARCHAR(50) NULL DEFAULT '🏷️'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 5. Table : Transaction
-- Relation (1,n) : Un compte a plusieurs transactions (CASCADE)
-- Relation (1,n) : Une catégorie classe plusieurs transactions (RESTRICT)
-- La suppression d'un compte supprime ses transactions (CASCADE).
-- La suppression d'une catégorie utilisée est bloquée (RESTRICT)
-- pour préserver l'historique comptable.
-- ==========================================================
CREATE TABLE `Transaction` (
    `id_transaction` INT AUTO_INCREMENT PRIMARY KEY,
    `id_compte` INT NOT NULL,
    `id_categorie` INT NOT NULL,
    `montant` DECIMAL(10,2) NOT NULL,
    `type_transaction` ENUM('debit', 'credit') NOT NULL,
    `description` VARCHAR(255) NULL,
    `date_transaction` DATE NOT NULL,
    CONSTRAINT `fk_transaction_compte`
        FOREIGN KEY (`id_compte`)
        REFERENCES `Compte_bancaire` (`id_compte`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT `fk_transaction_categorie`
        FOREIGN KEY (`id_categorie`)
        REFERENCES `Categorie_depense` (`id_categorie`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour optimiser les performances des jointures et tris fréquents
CREATE INDEX `idx_compte_user` ON `Compte_bancaire` (`id_utilisateur`);
CREATE INDEX `idx_transaction_compte` ON `Transaction` (`id_compte`);
CREATE INDEX `idx_transaction_categorie` ON `Transaction` (`id_categorie`);
CREATE INDEX `idx_transaction_date` ON `Transaction` (`date_transaction`);
