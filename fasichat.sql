
-- FasiChat Classroom 
-- Tous les mots de passe : password123

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS fasichat;
CREATE DATABASE fasichat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fasichat;

CREATE TABLE promotions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    annee VARCHAR(9) NOT NULL DEFAULT '2025-2026',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('etudiant','enseignant','assistant','doyen','vice_doyen','apparitaire') NOT NULL,
    promotion_id INT NULL,
    avatar_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE cours (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(200) NOT NULL,
    description TEXT NULL,
    promotion_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cours_enseignants (
    cours_id INT NOT NULL,
    user_id INT NOT NULL,
    PRIMARY KEY (cours_id, user_id),
    FOREIGN KEY (cours_id) REFERENCES cours(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    expediteur_id INT NOT NULL,
    destinataire_id INT NULL,
    cours_id INT NULL,
    promotion_id INT NULL,
    contenu TEXT NOT NULL,
    type_message ENUM('prive','public_promotion','mur','doyen_vice_doyen') NOT NULL,
    lu TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expediteur_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (destinataire_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (cours_id) REFERENCES cours(id) ON DELETE SET NULL,
    FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE fichiers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    message_id INT NOT NULL,
    nom_original VARCHAR(255) NOT NULL,
    nom_stocke VARCHAR(255) NOT NULL,
    type_mime VARCHAR(100) NOT NULL,
    taille BIGINT NOT NULL,
    type_fichier ENUM('image','video','document','audio') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE convocations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    expediteur_id INT NOT NULL,
    objet VARCHAR(255) NOT NULL,
    date_reunion DATETIME NOT NULL,
    lieu VARCHAR(255) NOT NULL,
    message_explicatif TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expediteur_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE convocation_destinataires (
    convocation_id INT NOT NULL,
    user_id INT NOT NULL,
    lu TINYINT(1) DEFAULT 0,
    PRIMARY KEY (convocation_id, user_id),
    FOREIGN KEY (convocation_id) REFERENCES convocations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE valve_annonces (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    auteur_id INT NOT NULL,
    date_expiration DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (auteur_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;


-- DONNEES DE TEST (mot de passe : password123)


INSERT INTO promotions (nom, annee) VALUES
    ('L2 Informatique', '2025-2026'),
    ('L3 Informatique', '2025-2026');

INSERT INTO users (nom, prenom, email, password, role, promotion_id) VALUES
('Kutangila Mayoya', 'David',    'doyen@fasichat.edu',        '$2y$12$0fpUfGZHurEouSbBvwDk9eCZVymaTGl1Bqru7X8g5/gTH/YU.tE/y', 'doyen',        NULL),
('Mampuya',          'Precie',   'vdoyen@fasichat.edu',       '$2y$12$0fpUfGZHurEouSbBvwDk9eCZVymaTGl1Bqru7X8g5/gTH/YU.tE/y', 'vice_doyen',   NULL),
('Mr ',           'Appariteur',   'apparitaire@fasichat.edu',  '$2y$12$0fpUfGZHurEouSbBvwDk9eCZVymaTGl1Bqru7X8g5/gTH/YU.tE/y', 'apparitaire',  NULL),
('Ntumba',           'MC',  'enseignant1@fasichat.edu',  '$2y$12$0fpUfGZHurEouSbBvwDk9eCZVymaTGl1Bqru7X8g5/gTH/YU.tE/y', 'enseignant',   NULL),
('Kuyunsa',              '',  'enseignant2@fasichat.edu',  '$2y$12$0fpUfGZHurEouSbBvwDk9eCZVymaTGl1Bqru7X8g5/gTH/YU.tE/y', 'enseignant',   NULL),
('Jired',            '',         'assistant@fasichat.edu',    '$2y$12$0fpUfGZHurEouSbBvwDk9eCZVymaTGl1Bqru7X8g5/gTH/YU.tE/y', 'assistant',    NULL),
('Modengba',           'Melchi', 'etudiant1@fasichat.edu',    '$2y$12$0fpUfGZHurEouSbBvwDk9eCZVymaTGl1Bqru7X8g5/gTH/YU.tE/y', 'etudiant',     1),
('Lenga',           'Elie','etudiant2@fasichat.edu',    '$2y$12$0fpUfGZHurEouSbBvwDk9eCZVymaTGl1Bqru7X8g5/gTH/YU.tE/y', 'etudiant',     1),
('Bonina',            'Joel',  'etudiant3@fasichat.edu',    '$2y$12$0fpUfGZHurEouSbBvwDk9eCZVymaTGl1Bqru7X8g5/gTH/YU.tE/y', 'etudiant',     2),
('Luteba',            'Winner',    'etudiant4@fasichat.edu',    '$2y$12$0fpUfGZHurEouSbBvwDk9eCZVymaTGl1Bqru7X8g5/gTH/YU.tE/y', 'etudiant',     2),
('Mampuya',          'Precie',   'mampuya.prof@fasichat.edu', '$2y$12$0fpUfGZHurEouSbBvwDk9eCZVymaTGl1Bqru7X8g5/gTH/YU.tE/y', 'enseignant',   NULL),
('Kodjo',            'Ndukuma',  'kodjo@fasichat.edu',        '$2y$12$0fpUfGZHurEouSbBvwDk9eCZVymaTGl1Bqru7X8g5/gTH/YU.tE/y', 'enseignant',   NULL);

-- Cours
-- ID 1 : Programmation Web PHP (L2)
-- ID 2 : Bases de Données (L2)
-- ID 3 : Algorithmes Avances (L3)
-- ID 4 : Reseaux Informatiques (L3)
-- ID 5 : Droit de l'informatique (L2) — NOUVEAU
INSERT INTO cours (titre, description, promotion_id) VALUES
('Programmation Web PHP',     'Developpement d applications web en PHP oriente objet', 1),
('Bases de Donnees',          'Conception et optimisation de bases de donnees relationnelles', 1),
('Algorithmes Avances',       'Structures de donnees et complexite algorithmique', 2),
('Reseaux Informatiques',     'Protocoles, architectures et securite des reseaux', 2),
('Droit de l informatique',   'Cadre juridique du numerique, protection des donnees, cybercriminalite', 1);

-- Affectations :
-- PHP (1)        → Mampuya Precie prof (ID 11) + Jired assistant (ID 6)
-- Reseaux (4)    → Jired (ID 6)
-- Droit info (5) → Kodjo Ndukuma (ID 12)
INSERT INTO cours_enseignants (cours_id, user_id) VALUES
(1, 11), (1, 6),
(2,  5),
(3,  4),
(4,  6),
(5, 12);

-- Messages de demonstration
INSERT INTO messages (expediteur_id, destinataire_id, promotion_id, contenu, type_message) VALUES
(7, 11, 1, 'Bonjour Professeur, j ai une question sur le TP heritage en PHP. Pouvez-vous m aider ?', 'public_promotion'),
(11, 7, 1, 'Bonjour ! L heritage se fait via le mot-cle extends. Relisez le chapitre 3.', 'public_promotion'),
(7,  8, NULL, 'Salut Aboubacar, tu as compris la question 3 du dernier TP ?', 'prive'),
(8,  7, NULL, 'Oui ! Il faut utiliser parent::__construct() pour appeler le constructeur mere.', 'prive'),
(1,  2, NULL, 'Precie, pouvez-vous preparer l ordre du jour de la reunion de vendredi ?', 'doyen_vice_doyen'),
(2,  1, NULL, 'Bien entendu David. Je prepare un compte-rendu complet avec les statistiques.', 'doyen_vice_doyen');

INSERT INTO messages (expediteur_id, cours_id, contenu, type_message) VALUES
(11, 1, 'Le TP note portera sur la conception d une mini-application MVC en PHP. Preparez les classes abstraites et interfaces.', 'mur'),
(5,  2, 'Rappel : rendu du projet BDD fixe au 15 janvier. Utilisez imperativement les cles etrangeres.', 'mur'),
(12, 5, 'Introduction au cours : nous aborderons le RGPD, la cybercriminalite et la propriete intellectuelle numerique.', 'mur');

-- Convocation
INSERT INTO convocations (expediteur_id, objet, date_reunion, lieu, message_explicatif) VALUES
(1, 'Reunion pedagogique du premier semestre', '2026-02-10 10:00:00',
 'Salle de conference A - Batiment principal',
 'Bilan pedagogique du S1 et planification des evaluations du S2.');

INSERT INTO convocation_destinataires (convocation_id, user_id) VALUES
(1,4),(1,5),(1,6),(1,11),(1,12);

-- Valve
INSERT INTO valve_annonces (titre, contenu, auteur_id, date_expiration) VALUES
('Inscriptions pedagogiques S2',
 'Les inscriptions pedagogiques pour le second semestre 2025-2026 sont ouvertes du 15 au 31 janvier 2026.',
 3, '2026-01-31'),
('Resultats du premier semestre disponibles',
 'Les resultats du S1 sont disponibles sur le portail etudiant. Deliberations du 5 janvier 2026.',
 3, NULL);
