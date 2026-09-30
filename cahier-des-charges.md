# Cahier des charges – SetPoint

Projet réalisé dans le cadre du cours - Programmation Serveur 2 à la HEIG-VD.

## Membres de l'équipe

- Mattia Morel (@username-not-chosen - https://github.com/username-not-chosen)
- Aurélien Niederhäuser (@aurelienniederhauser - https://github.com/aurelienniederhauser)

## Description du projet

SetPoint est un carnet de matchs de tennis en ligne. Il permet aux
joueur·euses d'enregistrer leurs matchs (score, adversaire, lieu, surface,
météo, durée…), d'y ajouter un ressenti personnel et de suivre leur progression
grâce à des statistiques (pourcentage de victoire, temps de jeu par mois).

Lorsqu'un match oppose deux personnes inscrites sur la plateforme, les
informations du match (dont le score) sont partagées entre elles. La note de
ressenti et les commentaires restent en revanche strictement personnels.

## Rôles

| Rôle                        | Droits                                                                                                                                                                          |
| :-------------------------- | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Visiteur·euse (non connecté·e) | Consulter la page d'accueil, s'inscrire, se connecter.                                                                                                                          |
| Joueur·euse                 | Gérer ses propres matchs, voir les matchs partagés auxquels il·elle participe, gérer ses notes personnelles, consulter ses statistiques, modifier son profil. |
| Administrateur·trice        | Tout ce que peut faire un·e joueur·euse, plus la gestion des comptes utilisateur·trices (liste, changement de rôle, suppression).                                                |

## Fonctionnalités principales

### Comptes et profil

- Créer un compte (nom, prénom, e-mail, mot de passe).
- Se connecter et se déconnecter.
- Modifier son profil (nom, e-mail, mot de passe, langue préférée).
- Mots de passe stockés de manière sécurisée (hachage).

### Gestion des matchs

- Créer, consulter, modifier et supprimer un match.
- Informations d'un match :
  - titre et description ;
  - date ;
  - adversaire : soit un·e joueur·euse inscrit·e sur la plateforme, soit
    simplement un nom saisi librement ;
  - score (sets) et résultat (victoire / défaite) ;
  - durée du match ;
  - lieu de la rencontre ;
  - surface du court (terre battue, dur, gazon, synthétique, indoor) ;
  - météo (ensoleillé, nuageux, pluie, vent, indoor…) ;
  - type de match (simple / double).
- Liste de ses matchs, triée par date.

### Notes privées et partage (autorisation)

- Les informations communes d'un match (score, date, lieu, surface, etc.) sont
  visibles par toutes les personnes inscrites qui y participent.
- Chaque participant·e peut ajouter **sa propre** note de ressenti (de 1 à 5)
  et **son propre** commentaire. Ils ne sont visibles que par lui·elle-même.
- Un·e joueur·euse ne peut ni voir ni modifier les matchs auxquels il·elle ne
  participe pas.

### Statistiques

- Pourcentage de victoire (global et par surface).
- Temps de jeu total par mois.
- Nombre de matchs joués (simple / double).

### E-mails

- E-mail de confirmation à l'inscription.
- E-mail de notification lorsqu'un·e autre joueur·euse vous ajoute comme
  adversaire ou partenaire dans un match.

### Multilingue

- Application disponible en français et en anglais, sur toutes les pages.

## Pages

**Publiques** (accessibles sans connexion) :

1. Accueil (présentation de l'application)
2. Inscription
3. Connexion

**Privées** (accessibles après connexion) :

1. Tableau de bord (derniers matchs + statistiques résumées)
2. Liste de mes matchs
3. Détail d'un match (avec note et commentaire personnels)
4. Création / modification d'un match
5. Statistiques détaillées
6. Profil
7. Administration des utilisateur·trices (administrateur·trice uniquement)

## Données principales (aperçu)

- **users** : les comptes (nom, e-mail, mot de passe haché, rôle, langue).
- **matches** : les informations communes d'un match (titre, description, date,
  score, durée, lieu, surface, météo, type, créateur·trice).
- **match_participants** : lien entre un match et ses participant·es inscrit·es,
  avec la note de ressenti et le commentaire **personnels** de chacun·e.

Le schéma complet (MCD, MLD, MPD) sera fourni séparément à la séance 4.

## Fonctionnalités optionnelles (si le temps le permet)

- Filtres et recherche dans la liste des matchs (par adversaire, surface,
  période).
- Graphiques pour les statistiques.
- Historique des confrontations contre un·e même adversaire (face-à-face).
- Statistiques par lieu ou par conditions météo.
- Export de ses matchs en CSV.

## Conclusion

_À compléter en fin de projet : fonctionnalités réellement implémentées,
difficultés rencontrées, solutions apportées et bilan._
