Je veux que tu crées une application web complète de gestion de finances personnelles avec la stack suivante :
- Frontend : HTML5, CSS3, JavaScript (vanilla)
- Backend : PHP (natif, sans framework)
- Base de données : MySQL

### 1. Base de données
Crée une base de données nommée `gestion_finances` avec les 4 tables suivantes et leurs relations :

**Table Utilisateur**
- id_utilisateur (INT, PK, AUTO_INCREMENT)
- nom (VARCHAR)
- prenom (VARCHAR)
- email (VARCHAR, UNIQUE)
- mot_de_passe (VARCHAR, haché avec password_hash)
- date_creation (DATETIME, DEFAULT CURRENT_TIMESTAMP)

**Table Compte_bancaire**
- id_compte (INT, PK, AUTO_INCREMENT)
- id_utilisateur (INT, FK -> Utilisateur.id_utilisateur)
- nom_compte (VARCHAR)
- type_compte (ENUM: 'courant', 'epargne', 'autre')
- solde (DECIMAL(10,2))
- date_ouverture (DATE)

**Table Categorie_depense**
- id_categorie (INT, PK, AUTO_INCREMENT)
- nom_categorie (VARCHAR)
- type (ENUM: 'depense', 'revenu')
- icone (VARCHAR, optionnel)

**Table Transaction**
- id_transaction (INT, PK, AUTO_INCREMENT)
- id_compte (INT, FK -> Compte_bancaire.id_compte)
- id_categorie (INT, FK -> Categorie_depense.id_categorie)
- montant (DECIMAL(10,2))
- type_transaction (ENUM: 'debit', 'credit')
- description (VARCHAR)
- date_transaction (DATE)

Relations :
- Un Utilisateur peut avoir plusieurs Comptes bancaires (1,n)
- Un Compte bancaire peut avoir plusieurs Transactions (1,n)
- Une Catégorie de dépense peut être liée à plusieurs Transactions (1,n)
- Utilise les contraintes FOREIGN KEY avec ON DELETE CASCADE où c'est logique

Génère le fichier SQL complet (CREATE DATABASE, CREATE TABLE, contraintes de clés étrangères).

### 2. CRUD
Pour chaque table (Utilisateur, Compte_bancaire, Categorie_depense, Transaction), crée les 4 opérations CRUD en PHP (avec PDO et requêtes préparées pour éviter les injections SQL) :
- Ajouter (formulaire HTML + traitement PHP en INSERT)
- Afficher (liste des données dans un tableau HTML, avec jointures pour afficher les noms liés, ex: nom de l'utilisateur, nom de la catégorie au lieu des IDs)
- Modifier (formulaire pré-rempli + UPDATE)
- Supprimer (avec confirmation JS avant suppression, DELETE)

### 3. Structure du projet
Organise le projet ainsi :
- /config/db.php (connexion PDO)
- /assets/css/style.css (design simple et propre, responsive)
- /assets/js/script.js (confirmations, validations côté client)
- /utilisateurs/ (add.php, list.php, edit.php, delete.php)
- /comptes/ (add.php, list.php, edit.php, delete.php)
- /categories/ (add.php, list.php, edit.php, delete.php)
- /transactions/ (add.php, list.php, edit.php, delete.php)
- index.php (page d'accueil avec menu de navigation vers les 4 sections)

### 4. Données de test
Insère des données de test réalistes dans un fichier seed.sql :
- 3 utilisateurs
- 2 comptes bancaires par utilisateur
- 6 catégories (ex: Alimentation, Transport, Loisirs, Salaire, Loyer, Santé)
- 15-20 transactions réparties sur les comptes et catégories

### 5. Gestion des relations
- Dans les formulaires d'ajout/modification de Transaction, affiche des listes déroulantes (select) pour choisir le Compte bancaire et la Catégorie de dépense
- Idem pour le formulaire de Compte bancaire (select de l'utilisateur propriétaire)
- Dans l'affichage des transactions, montre le solde du compte mis à jour automatiquement après chaque ajout/suppression de transaction

### 6. Tests
Ajoute des commentaires dans le code expliquant comment tester chaque opération CRUD, et vérifie que les contraintes de clés étrangères empêchent la suppression d'un utilisateur/compte/catégorie encore utilisé (ou gère la cascade proprement).

Le design doit être simple, propre et responsive (pas besoin de framework CSS, juste du CSS bien organisé).