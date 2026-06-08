# FasiChat Classroom

## Équipe — Trinôme

| Nom complet | Rôle |
|---|---|
| Modengba Ebubu Melchi | Développeur |
| Lengo Kalonga Élie | Développeur |
| Bonina Botuli Joël | Développeur |

**Module :** Programmation Web en PHP — POO  
**Niveau :** Licence 2 / Sciences Informatiques  
**Année académique :** 2025 – 2026  
**Établissement :** Université Protestante au Congo (FASI)

---


Plateforme de messagerie académique développée en **PHP orienté objet pur** (sans framework).

## Installation

### Prérequis
- PHP >= 8.1 avec extensions : `PDO`, `pdo_mysql`, `gd`, `fileinfo`, `mbstring`
- MySQL / MariaDB >= 5.7
- Serveur Apache / Nginx

### Étapes

```bash
# 1. Placer le dossier dans votre répertoire web
#    Ex: /var/www/html/fasichat  ou  C:\xampp\htdocs\fasichat

# 2. Importer la base de données
mysql -u root -p < fasichat.sql

# 3. Ajuster la configuration si nécessaire
nano config/config.php
# → modifier DB_HOST, DB_NAME, DB_USER, DB_PASS

# 4. Créer les dossiers d'uploads (s'ils n'existent pas)
mkdir -p uploads/{images,videos,documents,audio}
chmod -R 755 uploads/ temp/

# 5. Accéder à l'application
http://localhost/fasichat/
```

## Comptes de démonstration (mot de passe : `password123`)

| Rôle         | Email                       |
|--------------|-----------------------------|
| Doyen        | doyen@fasichat.edu          |
| Vice-Doyen   | vdoyen@fasichat.edu         |
| Apparitaire  | apparitaire@fasichat.edu    |
| Enseignant   | enseignant1@fasichat.edu    |
| Enseignant 2 | enseignant2@fasichat.edu    |
| Assistant    | assistant@fasichat.edu      |
| Étudiant L2  | etudiant1@fasichat.edu      |
| Étudiant L2  | etudiant2@fasichat.edu      |
| Étudiant L3  | etudiant3@fasichat.edu      |

## Architecture POO

```
Utilisateur (abstract)
├── MembrePedagogique (abstract)
│   ├── Etudiant
│   ├── Enseignant
│   └── Assistant  ← extends Enseignant
└── MembreAdministratif (abstract)
    ├── AdminConvocable (abstract) ← implements Convocable
    │   ├── Doyen
    │   └── ViceDoyen
    └── Apparitaire

Message (abstract)
├── MessagePrive
├── MessagePublic
├── MessageMur
└── MessageDoyenViceDoyen

Fichier (abstract)
├── Image      ← compression GD
├── Video
├── Document
└── Audio
```

## Concepts POO appliqués

- **Héritage** : hiérarchie Utilisateur → sous-classes rôles
- **Polymorphisme** : `getRoleLabel()`, `getDashboardData()`, `traiterFichier()`
- **Encapsulation** : attributs privés/protégés, getters/setters
- **Classes abstraites** : `Utilisateur`, `MembrePedagogique`, `Message`, `Fichier`
- **Interface** : `Convocable` → implémentée par `Doyen` et `ViceDoyen`
- **Factory Pattern** : `UserFactory`, `FichierFactory`
- **Singleton** : `BaseDeDonnees`, `Session`

## Sécurité

- Requêtes PDO préparées (anti-injection SQL)
- Échappement `htmlspecialchars()` sur toutes les sorties
- Tokens CSRF sur tous les formulaires POST
- Détection MIME sécurisée (`finfo`) pour les fichiers
- Régénération d'ID de session après login
- Vérification stricte du rôle avant chaque action sensible
