# 💰 FinancesManager — Application de Gestion de Finances Personnelles

Application web complète de gestion budgétaire et financière développée en **PHP natif (sans framework)**, **MySQL (PDO)** et **HTML5 / CSS3 / JavaScript vanilla**.

---

## 🚀 Fonctionnalités Clés

1. **Dashboard Financier Intéractif** :
   - Vue consolidée du patrimoine : Solde Total Global cumulé.
   - Statistiques en temps réel : nombre de comptes, utilisateurs, transactions.
   - Aperçu des comptes bancaires et historique des dernières opérations.
   - Accès rapide aux 4 modules CRUD.

2. **Synchronisation Automatique des Soldes Bancaires** :
   - À chaque ajout d'une transaction de débit, le solde du compte bancaire diminue immédiatement.
   - À chaque crédit, le solde augmente en temps réel.
   - En cas de modification ou de suppression d'une transaction, l'impact financier est automatiquement recalculé ou annulé de façon réciproque et atomique (`PDO::beginTransaction()` / `commit()`).

3. **Intégrité Référentielle & Sécurité des Données** :
   - `ON DELETE CASCADE` configuré entre Utilisateur et Compte, et entre Compte et Transaction.
   - `ON DELETE RESTRICT` sur les Catégories : bloque la suppression accidentelle d'une catégorie si elle est utilisée par des transactions, avec message explicatif à l'utilisateur.
   - Protection totale contre les injections SQL via requêtes préparées PDO systématiques.
   - Hachage sécurisé des mots de passe avec `password_hash()` (BCRYPT).
   - Protection contre les failles XSS via échappement systématique `htmlspecialchars()`.

4. **Interface Responsive & Épurée (Vanilla)** :
   - Design moderne sans dépendance externe (CSS Grid, Flexbox, variables CSS).
   - Boîtes de confirmation natives pour les suppressions critiques (`data-confirm`).
   - Validation dynamique des champs côté client avant soumission.
   - Notifications flash automatiques avec disparition progressive.

---

## 📁 Structure du Projet

```
file_rouge/
│
├── config/
│   └── db.php                     # Connexion PDO, configuration MySQL & fonctions utilitaires
│
├── includes/
│   ├── header.php                 # En-tête HTML, barre de navigation, messages flash
│   └── footer.php                 # Pied de page HTML et scripts
│
├── assets/
│   ├── css/
│   │   └── style.css              # Feuilles de styles modernes, responsive et légères
│   └── js/
│       └── script.js              # Confirmations de suppression, validations client, UX
│
├── utilisateurs/                  # Module CRUD Utilisateurs
│   ├── add.php                    # Formulaire de création (hachage du mot de passe)
│   ├── list.php                   # Liste avec comptage des comptes rattachés
│   ├── edit.php                   # Modification du profil (changement de mot de passe optionnel)
│   └── delete.php                 # Suppression avec cascade
│
├── comptes/                       # Module CRUD Comptes Bancaires
│   ├── add.php                    # Création avec select du titulaire (Utilisateur)
│   ├── list.php                   # Liste avec jointure Utilisateur & solde formaté
│   ├── edit.php                   # Modification du libellé, type et titulaire
│   └── delete.php                 # Suppression avec cascade sur l'historique
│
├── categories/                    # Module CRUD Catégories de Dépense / Revenu
│   ├── add.php                    # Ajout avec sélecteur d'icônes/emojis
│   ├── list.php                   # Liste avec badge Dépense / Revenu et nombre d'opérations
│   ├── edit.php                   # Édition des métadonnées
│   └── delete.php                 # Suppression protégée par contrainte RESTRICT
│
├── transactions/                  # Module CRUD Transactions
│   ├── add.php                    # Ajout avec menus déroulants (Compte, Catégorie) + MàJ automatique du solde
│   ├── list.php                   # Historique avec filtres (par compte, catégorie) & solde actuel
│   ├── edit.php                   # Modification avec réajustement différentiel du solde
│   └── delete.php                 # Suppression avec annulation de l'impact sur le solde
│
├── schema.sql                     # Script DDL de création de la base et des contraintes
├── seed.sql                       # Jeu de données de test réaliste
├── index.php                      # Tableau de bord principal (Accueil)
└── README.md                      # Documentation complète
```

---

## 🛠️ Installation & Configuration

### 1. Prérequis
- Serveur Web (Apache, Nginx ou le serveur intégré PHP).
- PHP 8.x avec extension `pdo_mysql` activée.
- Serveur MySQL 8.x ou MariaDB.

### 2. Configuration de la base de données
Par défaut, le fichier `config/db.php` est configuré avec :
- **Hôte** : `127.0.0.1`
- **Port** : `3306`
- **Base** : `gestion_finances`
- **Utilisateur** : `root`
- **Mot de passe** : `Solicode@2` (ou personnalisable directement dans `config/db.php` ou via variables d'environnement).

### 3. Exécution des scripts SQL

Dans votre terminal ou via MySQL Workbench / phpMyAdmin :

```bash
# 1. Création de la base et des tables
mysql -u root -p < schema.sql

# 2. Insertion des données de test
mysql -u root -p < seed.sql
```

### 4. Lancement de l'application

Vous pouvez lancer le serveur local intégré de PHP depuis le dossier `file_rouge` :

```bash
php -S localhost:8000
```

Puis ouvrez votre navigateur sur : [http://localhost:8000](http://localhost:8000)

*(L'application fonctionne également sous Apache / Laragon / XAMPP dans un sous-dossier comme `http://localhost/file_rouge/` grâce à la détection dynamique d'URL de base).*

---

## 👤 Données de Test (`seed.sql`)

Le mot de passe de tous les utilisateurs créés dans `seed.sql` est : `password123`

| Utilisateur | Email | Comptes bancaires associés |
| :--- | :--- | :--- |
| **Thomas Dubois** | `thomas.dubois@email.com` | Compte Courant BNP, Livret A Épargne |
| **Sarah Mansouri** | `sarah.mansouri@email.com` | Compte Courant BoursoBank, PEL Épargne |
| **Lucas Martin** | `lucas.martin@email.com` | Compte Chèque SG, Compte Investissement |

---

## 🧪 Guide de Test des Opérations CRUD & Relations

### Test 1 : Synchronisation du solde en temps réel
1. Rendez-vous sur la page **Transactions** ➔ **➕ Nouvelle transaction**.
2. Notez le solde du compte sélectionné (ex: 1 587,50 €).
3. Enregistrez un **Débit** de **100,00 €**.
4. Le solde du compte passe immédiatement à **1 487,50 €**.
5. Cliquez sur **Supprimer** sur cette transaction : le compte est recrédité de 100,00 € et revient exactement à **1 587,50 €**.

### Test 2 : Contrainte d'intégrité RESTRICT (Catégorie)
1. Rendez-vous sur la page **Catégories**.
2. Essayez de supprimer la catégorie **Alimentation** (qui contient des transactions).
3. Une alerte bloquante s'affiche expliquant que la catégorie est protégée car liée à des transactions.
4. Créez une nouvelle catégorie temporaire (0 transaction) : celle-ci se supprime immédiatement sans restriction.

### Test 3 : Suppression en cascade (Utilisateur)
1. Créez un utilisateur de test via **Utilisateurs ➔ ➕ Nouvel utilisateur**.
2. Ouvrez-lui un compte bancaire dans **Comptes ➔ ➕ Nouveau compte**.
3. Ajoutez une transaction sur ce compte.
4. Supprimez cet utilisateur : la contrainte `ON DELETE CASCADE` supprime automatiquement le compte et la transaction sans laisser d'orphelins.
