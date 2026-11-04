<?php

class Utilisateur {
    public const SEXES = ['Homme', 'Femme', 'Autre'];
    public const LANGUES = ['fr', 'en'];

    public const ROLE_JOUEUR = 1;
    public const ROLE_ADMIN = 2;

    public const MOT_DE_PASSE_LONGUEUR_MIN = 8;

    private ?int $id = null;
    private string $prenom = '';
    private string $nom = '';
    private string $email = '';
    private ?string $dateNaissance = null;
    private ?string $sexe = null;
    private string $langue = 'fr';
    private ?string $dateInscription = null;
    private int $idRole = self::ROLE_JOUEUR;

    public function __construct(array $donnees = []) {
        $this->setPrenom($donnees['prenom'] ?? '');
        $this->setNom($donnees['nom'] ?? '');
        $this->setEmail($donnees['email'] ?? '');
        $this->setDateNaissance($donnees['date_naissance'] ?? null);
        $this->setSexe($donnees['sexe'] ?? null);
        $this->setLangue($donnees['langue'] ?? 'fr');
    }

    public static function depuisLigneBd(array $ligne): self {
        $utilisateur = new self($ligne);
        $utilisateur->id = (int) $ligne['id_utilisateur'];
        $utilisateur->idRole = (int) ($ligne['id_role'] ?? self::ROLE_JOUEUR);
        $utilisateur->dateInscription = $ligne['date_inscription'] ?? null;
        return $utilisateur;
    }

    /**
     * Vérifie les données de l'utilisateur.
     * Si $motDePasse est fourni, il est vérifié aussi.
     *
     * @return string[] la liste des erreurs (vide si tout est valide)
     */

    public function valider(?string $motDePasse = null): array {
        $erreurs = [];

        if ($this->prenom === '' || mb_strlen($this->prenom) > 100) {
            $erreurs[] = 'Le prénom est obligatoire (100 caractères max).';
        }
        if ($this->nom === '' || mb_strlen($this->nom) > 100) {
            $erreurs[] = 'Le nom est obligatoire (100 caractères max).';
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL) || mb_strlen($this->email) > 255) {
            $erreurs[] = 'L’adresse e-mail n’est pas valide.';
        }
        if ($motDePasse !== null) {
            $erreurs = array_merge($erreurs, self::validerMotDePasse($motDePasse));
        }
        if ($this->sexe !== null && !in_array($this->sexe, self::SEXES, true)) {
            $erreurs[] = 'Le sexe choisi n’est pas valide.';
        }
        if ($this->dateNaissance !== null) {
            $date = DateTime::createFromFormat('Y-m-d', $this->dateNaissance);
            if (!$date || $date->format('Y-m-d') !== $this->dateNaissance || $date > new DateTime()) {
                $erreurs[] = 'La date de naissance n’est pas valide.';
            }
        }
        if (!in_array($this->langue, self::LANGUES, true)) {
            $erreurs[] = 'La langue choisie n’est pas valide.';
        }

        return $erreurs;
    }

    /**
     * Vérifie un mot de passe en clair (utile aussi pour le changement de mot de passe).
     *
     * @return string[] la liste des erreurs
     */
    public static function validerMotDePasse(string $motDePasse): array {
        if (mb_strlen($motDePasse) < self::MOT_DE_PASSE_LONGUEUR_MIN) {
            return ['Le mot de passe doit contenir au moins ' . self::MOT_DE_PASSE_LONGUEUR_MIN . ' caractères.'];
        }
        return [];
    }

    public function estAdmin(): bool {
        return $this->idRole === self::ROLE_ADMIN;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getPrenom(): string {
        return $this->prenom;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function getNomComplet(): string {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getDateNaissance(): ?string {
        return $this->dateNaissance;
    }

    public function getSexe(): ?string {
        return $this->sexe;
    }

    public function getLangue(): string {
        return $this->langue;
    }

    public function getDateInscription(): ?string {
        return $this->dateInscription;
    }

    public function getIdRole(): int {
        return $this->idRole;
    }

    public function setPrenom(string $prenom): void {
        $this->prenom = trim($prenom);
    }

    public function setNom(string $nom): void {
        $this->nom = trim($nom);
    }

    public function setEmail(string $email): void {
        $this->email = trim($email);
    }

    public function setDateNaissance(?string $dateNaissance): void {
        $dateNaissance = trim((string) $dateNaissance);
        $this->dateNaissance = $dateNaissance === '' ? null : $dateNaissance;
    }

    public function setSexe(?string $sexe): void {
        $sexe = trim((string) $sexe);
        $this->sexe = $sexe === '' ? null : $sexe;
    }

    public function setLangue(string $langue): void {
        $this->langue = trim($langue);
    }

    public function setIdRole(int $idRole): void {
        $this->idRole = $idRole;
    }
}
