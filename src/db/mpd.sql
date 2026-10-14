-- SetPoint - MPD

-- Suppression des tables

DROP TABLE IF EXISTS participations;
DROP TABLE IF EXISTS sets;
DROP TABLE IF EXISTS matchs;
DROP TABLE IF EXISTS utilisateurs;
DROP TABLE IF EXISTS types_match;
DROP TABLE IF EXISTS meteos;
DROP TABLE IF EXISTS surfaces;
DROP TABLE IF EXISTS roles;

-- Tables de référence

CREATE TABLE roles (
    id_role  INT          NOT NULL AUTO_INCREMENT,
    libelle  VARCHAR(50)  NOT NULL UNIQUE,
    PRIMARY KEY (id_role)
);

CREATE TABLE surfaces (
    id_surface  INT          NOT NULL AUTO_INCREMENT,
    libelle     VARCHAR(50)  NOT NULL UNIQUE,
    PRIMARY KEY (id_surface)
);

CREATE TABLE meteos (
    id_meteo  INT          NOT NULL AUTO_INCREMENT,
    libelle   VARCHAR(50)  NOT NULL UNIQUE,
    PRIMARY KEY (id_meteo)
);

CREATE TABLE types_match (
    id_type  INT          NOT NULL AUTO_INCREMENT,
    libelle  VARCHAR(50)  NOT NULL UNIQUE,
    PRIMARY KEY (id_type)
);

-- Utilisateurs

CREATE TABLE utilisateurs (
    id_utilisateur     INT           NOT NULL AUTO_INCREMENT,
    prenom             VARCHAR(100)  NOT NULL,
    nom                VARCHAR(100)  NOT NULL,
    email              VARCHAR(255)  NOT NULL UNIQUE,
    mot_de_passe_hash  VARCHAR(255)  NOT NULL,
    date_naissance     DATE          NULL,
    sexe               VARCHAR(20)   NULL,
    langue             VARCHAR(2)    NOT NULL DEFAULT 'fr',
    date_inscription   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_role            INT           NOT NULL DEFAULT 1,
    PRIMARY KEY (id_utilisateur),
    FOREIGN KEY (id_role) REFERENCES roles (id_role)
);

-- Matchs

CREATE TABLE matchs (
    id_match        INT           NOT NULL AUTO_INCREMENT,
    titre           VARCHAR(150)  NOT NULL,
    description     TEXT          NULL,
    date_match      DATETIME      NOT NULL,
    duree           INT           NULL,      -- en minutes
    lieu            VARCHAR(150)  NULL,
    camp_vainqueur  INT           NOT NULL,  -- 1 ou 2
    id_createur     INT           NOT NULL,
    id_surface      INT           NOT NULL,
    id_meteo        INT           NULL,
    id_type         INT           NOT NULL,
    PRIMARY KEY (id_match),
    FOREIGN KEY (id_createur) REFERENCES utilisateurs (id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_surface)  REFERENCES surfaces (id_surface),
    FOREIGN KEY (id_meteo)    REFERENCES meteos (id_meteo),
    FOREIGN KEY (id_type)     REFERENCES types_match (id_type)
);

-- Sets

CREATE TABLE sets (
    id_match    INT  NOT NULL,
    numero_set  INT  NOT NULL,
    jeux_camp1  INT  NOT NULL,
    jeux_camp2  INT  NOT NULL,
    PRIMARY KEY (id_match, numero_set),
    FOREIGN KEY (id_match) REFERENCES matchs (id_match) ON DELETE CASCADE
);

-- Participations

CREATE TABLE participations (
    id_match        INT   NOT NULL,
    id_utilisateur  INT   NOT NULL,
    camp            INT   NOT NULL,  -- 1 ou 2
    note            INT   NULL,      -- de 1 à 5
    commentaire     TEXT  NULL,
    PRIMARY KEY (id_match, id_utilisateur),
    FOREIGN KEY (id_match)       REFERENCES matchs (id_match) ON DELETE CASCADE,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs (id_utilisateur) ON DELETE CASCADE
);

-- Données de référence

INSERT INTO roles (libelle) VALUES
    ('Joueur·euse'),
    ('Administrateur·trice');

INSERT INTO surfaces (libelle) VALUES
    ('Terre battue'), ('Dur'), ('Gazon'), ('Synthétique'), ('Indoor');

INSERT INTO meteos (libelle) VALUES
    ('Ensoleillé'), ('Nuageux'), ('Pluie'), ('Vent'), ('Indoor');

INSERT INTO types_match (libelle) VALUES
    ('Simple'), ('Double');
