# EduLib

EduLib est une mini plateforme web en PHP/MySQL pour partager des ressources pédagogiques entre étudiants.

## Aperçu

- Authentification des utilisateurs (inscription, connexion, déconnexion)
- Gestion de profil utilisateur
- Publication de ressources par catégorie
- Recherche et filtrage des ressources
- Upload d'image pour illustrer une ressource
- Espace d'administration pour gérer les utilisateurs

## Stack technique

- PHP (procédural)
- MySQL / MariaDB
- HTML/CSS natif (sans framework)
- Sessions PHP pour l'authentification

## Prérequis

- Serveur local PHP + MySQL (UwAmp, XAMPP, WAMP, etc.)
- PHP avec extension PDO MySQL activée
- Accès à phpMyAdmin (ou client SQL)

## Installation locale

1. Cloner le dépôt

```bash
git clone https://github.com/MaelysCC/EduLib.git
```

2. Copier le dossier du projet dans le répertoire web local (exemple UwAmp)

```text
C:\UwAmp\www\mini-projet
```

3. Créer la base de données et les tables

- Importer le fichier db.sql via phpMyAdmin
- Ou en ligne de commande :

```bash
mysql -u root -p < db.sql
```

4. Vérifier la configuration de la base dans config/db.php

Paramètres actuels :

- DB_HOST : localhost
- DB_NAME : edulib
- DB_USER : root
- DB_PASS : root

5. Ouvrir l'application dans le navigateur

```text
http://localhost/mini-projet/
```

## Compte administrateur de démonstration

Créé automatiquement par db.sql :

- Email : admin@edulib.fr
- Mot de passe : Admin1234

Pense à modifier ce mot de passe en environnement réel.

## Structure du projet

```text
mini-projet/
├─ index.php                  # Accueil et statistiques
├─ resources.php              # Liste, recherche et filtres des ressources
├─ resource-detail.php        # Détail d'une ressource
├─ add-resource.php           # Ajout d'une ressource (connecté)
├─ edit-resource.php          # Édition d'une ressource
├─ delete-resource.php        # Suppression d'une ressource
├─ register.php               # Inscription
├─ login.php                  # Connexion
├─ logout.php                 # Déconnexion
├─ profile.php                # Profil utilisateur
├─ db.sql                     # Schéma SQL + données de démonstration
├─ config/
│  └─ db.php                  # Connexion PDO, helpers auth, upload
├─ admin/
│  ├─ index.php               # Tableau de bord admin
│  ├─ users.php               # Liste des utilisateurs
│  ├─ edit-user.php           # Édition utilisateur
│  └─ delete-user.php         # Suppression utilisateur
├─ assets/
│  └─ style.css               # Styles globaux
└─ uploads/                   # Images uploadées
```

## Fonctionnement principal

1. Un utilisateur s'inscrit ou se connecte.
2. Il peut publier une fiche (titre, catégorie, contenu, image optionnelle).
3. Les ressources sont visibles publiquement avec recherche par mots-clés et filtre par catégorie.
4. Un administrateur peut accéder au back-office et gérer les utilisateurs.

## Sécurité et bonnes pratiques déjà en place

- Requêtes préparées PDO
- Échappement HTML via helper h()
- Vérifications d'accès (requireLogin, requireAdmin)
- Contrôle du type MIME et de la taille pour les images uploadées

## Améliorations possibles

- Ajout de pagination sur la liste des ressources
- Ajout d'un système de favoris et/ou commentaires
- Mise en place de tests (PHPUnit)
- Variables d'environnement pour les secrets DB
- Logs et gestion d'erreurs plus avancée

## Auteur

Projet universitaire EduLib.
