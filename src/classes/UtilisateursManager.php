<?php

require_once __DIR__ . '/Utilisateur.php';

class UtilisateursManager {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function ajouterUtilisateur(Utilisateur $utilisateur, string $motDePasse): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO utilisateurs (prenom, nom, email, mot_de_passe_hash, date_naissance, sexe, langue, id_role)
             VALUES (:prenom, :nom, :email, :hash, :date_naissance, :sexe, :langue, :id_role)'
        );
        $stmt->execute([
            'prenom' => $utilisateur->getPrenom(),
            'nom' => $utilisateur->getNom(),
            'email' => $utilisateur->getEmail(),
            'hash' => password_hash($motDePasse, PASSWORD_DEFAULT),
            'date_naissance' => $utilisateur->getDateNaissance(),
            'sexe' => $utilisateur->getSexe(),
            'langue' => $utilisateur->getLangue(),
            'id_role' => $utilisateur->getIdRole(),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function emailExiste(string $email, ?int $idAExclure = null): bool {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM utilisateurs
             WHERE email = :email AND id_utilisateur <> :id'
        );
        $stmt->execute([
            'email' => $email,
            'id' => $idAExclure ?? 0,
        ]);
        return $stmt->fetchColumn() > 0;
    }

    public function getUtilisateurParId(int $id): ?Utilisateur {
        $stmt = $this->pdo->prepare('SELECT * FROM utilisateurs WHERE id_utilisateur = :id');
        $stmt->execute(['id' => $id]);
        $ligne = $stmt->fetch();

        return $ligne ? Utilisateur::depuisLigneBd($ligne) : null;
    }

    public function getUtilisateurParEmail(string $email): ?Utilisateur {
        $stmt = $this->pdo->prepare('SELECT * FROM utilisateurs WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $ligne = $stmt->fetch();

        return $ligne ? Utilisateur::depuisLigneBd($ligne) : null;
    }

    public function verifierIdentifiants(string $email, string $motDePasse): ?Utilisateur {
        $stmt = $this->pdo->prepare('SELECT * FROM utilisateurs WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $ligne = $stmt->fetch();

        if (!$ligne || !password_verify($motDePasse, $ligne['mot_de_passe_hash'])) {
            return null;
        }

        return Utilisateur::depuisLigneBd($ligne);
    }


    public function getUtilisateurs(): array {
        $stmt = $this->pdo->query('SELECT * FROM utilisateurs ORDER BY nom, prenom');

        $utilisateurs = [];
        foreach ($stmt->fetchAll() as $ligne) {
            $utilisateurs[] = Utilisateur::depuisLigneBd($ligne);
        }
        return $utilisateurs;
    }


    public function modifierUtilisateur(Utilisateur $utilisateur): void {
        $stmt = $this->pdo->prepare(
            'UPDATE utilisateurs
             SET prenom = :prenom, nom = :nom, email = :email,
                 date_naissance = :date_naissance, sexe = :sexe, langue = :langue
             WHERE id_utilisateur = :id'
        );
        $stmt->execute([
            'prenom' => $utilisateur->getPrenom(),
            'nom' => $utilisateur->getNom(),
            'email' => $utilisateur->getEmail(),
            'date_naissance' => $utilisateur->getDateNaissance(),
            'sexe' => $utilisateur->getSexe(),
            'langue' => $utilisateur->getLangue(),
            'id' => $utilisateur->getId(),
        ]);
    }

    public function modifierMotDePasse(int $id, string $nouveauMotDePasse): void {
        $stmt = $this->pdo->prepare(
            'UPDATE utilisateurs SET mot_de_passe_hash = :hash WHERE id_utilisateur = :id'
        );
        $stmt->execute([
            'hash' => password_hash($nouveauMotDePasse, PASSWORD_DEFAULT),
            'id' => $id,
        ]);
    }

    public function changerRole(int $id, int $idRole): void {
        $stmt = $this->pdo->prepare('UPDATE utilisateurs SET id_role = :id_role WHERE id_utilisateur = :id');
        $stmt->execute([
            'id_role' => $idRole,
            'id' => $id,
        ]);
    }

    public function supprimerUtilisateur(int $id): void {
        $stmt = $this->pdo->prepare('DELETE FROM utilisateurs WHERE id_utilisateur = :id');
        $stmt->execute(['id' => $id]);
    }
}
