<?php

session_start();

require __DIR__ . '/classes/Database.php';
require __DIR__ . '/classes/Utilisateur.php';
require __DIR__ . '/classes/UtilisateursManager.php';

$database = new Database(__DIR__ . '/config/database.ini');
$pdo = $database->getPdo();

$utilisateursManager = new UtilisateursManager($pdo);

$utilisateur = $_SESSION['utilisateur'] ?? null;


function connecter(Utilisateur $user): void {

    session_regenerate_id(true);

    $_SESSION['utilisateur'] = [
        'id' => $user->getId(),
        'prenom' => $user->getPrenom(),
        'nom' => $user->getNom(),
        'id_role' => $user->getIdRole(),
    ];
    $_SESSION['afficher_bienvenue'] = true;
}
