-- EduLib — Script de création de la base de données
-- Exécuter via phpMyAdmin ou : mysql -u root -p < db.sql

CREATE DATABASE IF NOT EXISTS edulib CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edulib;

CREATE TABLE IF NOT EXISTS utilisateurs (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe    VARCHAR(255) NOT NULL,
    role            ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ressources (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titre       VARCHAR(200) NOT NULL,
    description TEXT,
    contenu     TEXT NOT NULL,
    categorie   VARCHAR(100) NOT NULL,
    image       VARCHAR(255) NULL,
    auteur_id   INT NOT NULL,
    date_depot  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Si vous mettez à jour une base existante, exécutez cette ligne :
-- ALTER TABLE ressources ADD COLUMN image VARCHAR(255) NULL AFTER categorie;

-- Compte administrateur par défaut
-- Email : admin@edulib.fr  |  Mot de passe : Admin1234
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role)
VALUES (
    'Admin',
    'EduLib',
    'admin@edulib.fr',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin'
);

-- Ressources de démonstration
INSERT INTO ressources (titre, description, contenu, categorie, auteur_id) VALUES
(
    'Introduction aux algorithmes de tri',
    'Présentation des principaux algorithmes de tri avec leur complexité.',
    'Les algorithmes de tri sont fondamentaux en informatique.\n\n**Tri à bulles (Bubble Sort)**\nComplexité : O(n²)\nPrincipe : comparer deux éléments adjacents et les inverser si nécessaire.\n\n**Tri rapide (Quick Sort)**\nComplexité moyenne : O(n log n)\nPrincipe : choisir un pivot, partitionner le tableau, récurser.\n\n**Tri fusion (Merge Sort)**\nComplexité : O(n log n)\nPrincipe : diviser le tableau en deux, trier chaque moitié, fusionner.',
    'Informatique',
    1
),
(
    'Les suites numériques — rappels et exercices',
    'Fiche de révision sur les suites arithmétiques et géométriques.',
    'Une suite est une fonction définie sur N.\n\n**Suite arithmétique**\nRaison r constante : u(n) = u(0) + n×r\nSomme des n premiers termes : n × (u(0) + u(n-1)) / 2\n\n**Suite géométrique**\nRaison q constante : u(n) = u(0) × q^n\nSomme des n premiers termes : u(0) × (1 - q^n) / (1 - q) si q ≠ 1',
    'Mathématiques',
    1
),
(
    'Introduction au droit des contrats',
    'Notions essentielles pour comprendre la formation et l''exécution d''un contrat.',
    'Un contrat est un accord de volontés entre deux ou plusieurs personnes.\n\n**Conditions de formation**\n1. Capacité des parties\n2. Consentement libre et éclairé\n3. Objet certain et licite\n4. Cause licite\n\n**Vices du consentement**\n- Erreur (Art. 1132 C. civ.)\n- Dol (Art. 1137 C. civ.)\n- Violence (Art. 1140 C. civ.)',
    'Droit',
    1
);
