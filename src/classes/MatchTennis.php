<?php

require_once __DIR__ . '/SetMatch.php';
require_once __DIR__ . '/Participation.php';

class MatchTennis {

    public const TYPE_SIMPLE = 1;
    public const TYPE_DOUBLE = 2;

    public const TITRE_LONGUEUR_MAX = 150;
    public const LIEU_LONGUEUR_MAX = 150;
    public const DUREE_MAX = 24 * 60;

    private ?int $id = null;
    private string $titre = '';
    private ?string $description = null;
    private ?string $dateMatch = null;
    private ?int $duree = null;
    private ?string $lieu = null;
    private int $campVainqueur = 0;
    private ?int $idCreateur = null;
    private int $idSurface = 0;
    private ?int $idMeteo = null;
    private int $idType = self::TYPE_SIMPLE;

    private string $surface = '';
    private ?string $meteo = null;
    private string $typeMatch = '';

    private array $sets = [];

    private array $participants = [];

    public function __construct(array $donnees = []) {
        $this->setTitre($donnees['titre'] ?? '');
        $this->setDescription($donnees['description'] ?? null);
        $this->setDateMatch($donnees['date_match'] ?? null);
        $this->setDuree($donnees['duree'] ?? null);
        $this->setLieu($donnees['lieu'] ?? null);
        $this->setIdSurface($donnees['id_surface'] ?? 0);
        $this->setIdMeteo($donnees['id_meteo'] ?? null);
        $this->setIdType($donnees['id_type'] ?? self::TYPE_SIMPLE);

        if (isset($donnees['sets']) && is_array($donnees['sets'])) {
            $this->setSetsDepuisFormulaire($donnees['sets']);
        }
    }

    public static function depuisLigneBd(array $ligne): self {
        $match = new self($ligne);
        $match->id = (int) $ligne['id_match'];
        $match->dateMatch = $ligne['date_match'];
        $match->campVainqueur = (int) $ligne['camp_vainqueur'];
        $match->idCreateur = (int) $ligne['id_createur'];
        $match->surface = $ligne['surface'] ?? '';
        $match->meteo = $ligne['meteo'] ?? null;
        $match->typeMatch = $ligne['type_match'] ?? '';
        return $match;
    }

    public function valider(): array {
        $erreurs = [];

        if ($this->titre === '' || mb_strlen($this->titre) > self::TITRE_LONGUEUR_MAX) {
            $erreurs[] = 'Le titre est obligatoire (' . self::TITRE_LONGUEUR_MAX . ' caractères max).';
        }
        if ($this->dateMatch === null) {
            $erreurs[] = 'La date du match n’est pas valide.';
        }
        if ($this->duree !== null && ($this->duree <= 0 || $this->duree > self::DUREE_MAX)) {
            $erreurs[] = 'La durée doit être un nombre de minutes positif.';
        }
        if ($this->lieu !== null && mb_strlen($this->lieu) > self::LIEU_LONGUEUR_MAX) {
            $erreurs[] = 'Le lieu est trop long (' . self::LIEU_LONGUEUR_MAX . ' caractères max).';
        }
        if ($this->idSurface <= 0) {
            $erreurs[] = 'Merci de choisir une surface.';
        }
        if (!in_array($this->idType, [self::TYPE_SIMPLE, self::TYPE_DOUBLE], true)) {
            $erreurs[] = 'Le type de match n’est pas valide.';
        }

        // Sets
        if (count($this->sets) === 0 || count($this->sets) > SetMatch::NOMBRE_MAX) {
            $erreurs[] = 'Un match doit contenir entre 1 et ' . SetMatch::NOMBRE_MAX . ' sets.';
        }
        foreach ($this->sets as $set) {
            $erreurs = array_merge($erreurs, $set->valider());
        }
        if ($this->sets && $this->calculerCampVainqueur() === 0) {
            $erreurs[] = 'Le score ne désigne pas de vainqueur (autant de sets gagnés de chaque côté).';
        }

        // Participants
        $parCamp = $this->idType === self::TYPE_DOUBLE ? 2 : 1;
        foreach ([1, 2] as $camp) {
            if (count($this->getParticipantsDuCamp($camp)) !== $parCamp) {
                $erreurs[] = "Le camp {$camp} doit contenir {$parCamp} joueur·euse(s).";
            }
        }
        $ids = array_map(fn(Participation $p) => $p->getIdUtilisateur(), $this->participants);
        if (count($ids) !== count(array_unique($ids))) {
            $erreurs[] = 'Une même personne ne peut pas apparaître deux fois dans le match.';
        }

        return $erreurs;
    }

    public function calculerCampVainqueur(): int {
        $setsGagnes = [1 => 0, 2 => 0];
        foreach ($this->sets as $set) {
            $setsGagnes[$set->getCampGagnant()]++;
        }
        if ($setsGagnes[1] === $setsGagnes[2]) {
            return 0;
        }
        return $setsGagnes[1] > $setsGagnes[2] ? 1 : 2;
    }

    public function getScoreTexte(): string {
        return implode(' ', array_map(
            fn(SetMatch $set) => $set->getJeuxCamp1() . '-' . $set->getJeuxCamp2(),
            $this->sets
        ));
    }

    public function getCampDe(int $idUtilisateur): ?int {
        foreach ($this->participants as $participant) {
            if ($participant->getIdUtilisateur() === $idUtilisateur) {
                return $participant->getCamp();
            }
        }
        return null;
    }

    public function estGagnePar(int $idUtilisateur): ?bool {
        $camp = $this->getCampDe($idUtilisateur);
        return $camp === null ? null : $camp === $this->campVainqueur;
    }

    public function estCreePar(int $idUtilisateur): bool {
        return $this->idCreateur === $idUtilisateur;
    }

    public function getParticipantsDuCamp(int $camp): array {
        return array_values(array_filter($this->participants, fn(Participation $p) => $p->getCamp() === $camp));
    }

    // Getters

    public function getId(): ?int {
        return $this->id;
    }

    public function getTitre(): string {
        return $this->titre;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function getDateMatch(): ?string {
        return $this->dateMatch;
    }

    public function getDateMatchPourFormulaire(): string {
        return $this->dateMatch === null ? '' : date('Y-m-d\TH:i', strtotime($this->dateMatch));
    }

    public function getDuree(): ?int {
        return $this->duree;
    }

    public function getLieu(): ?string {
        return $this->lieu;
    }

    public function getCampVainqueur(): int {
        return $this->campVainqueur;
    }

    public function getIdCreateur(): ?int {
        return $this->idCreateur;
    }

    public function getIdSurface(): int {
        return $this->idSurface;
    }

    public function getIdMeteo(): ?int {
        return $this->idMeteo;
    }

    public function getIdType(): int {
        return $this->idType;
    }

    public function getSurface(): string {
        return $this->surface;
    }

    public function getMeteo(): ?string {
        return $this->meteo;
    }

    public function getTypeMatch(): string {
        return $this->typeMatch;
    }

    /** @return SetMatch[] */
    public function getSets(): array {
        return $this->sets;
    }

    /** @return Participation[] */
    public function getParticipants(): array {
        return $this->participants;
    }

    // Setters

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function setIdCreateur(int $idCreateur): void {
        $this->idCreateur = $idCreateur;
    }

    public function setTitre(string $titre): void {
        $this->titre = trim($titre);
    }

    public function setDescription(?string $description): void {
        $description = trim((string) $description);
        $this->description = $description === '' ? null : $description;
    }

    public function setDateMatch(?string $dateMatch): void {
        $dateMatch = trim((string) $dateMatch);
        $this->dateMatch = null;

        foreach (['Y-m-d\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i'] as $format) {
            $date = DateTime::createFromFormat($format, $dateMatch);
            if ($date && $date->format($format) === $dateMatch) {
                $this->dateMatch = $date->format('Y-m-d H:i:s');
                return;
            }
        }
    }

    public function setDuree(int|string|null $duree): void {
        $duree = trim((string) $duree);
        $this->duree = $duree === '' ? null : (int) $duree;
    }

    public function setLieu(?string $lieu): void {
        $lieu = trim((string) $lieu);
        $this->lieu = $lieu === '' ? null : $lieu;
    }

    public function setIdSurface(int|string $idSurface): void {
        $this->idSurface = (int) $idSurface;
    }

    public function setIdMeteo(int|string|null $idMeteo): void {
        $idMeteo = trim((string) $idMeteo);
        $this->idMeteo = $idMeteo === '' ? null : (int) $idMeteo;
    }

    public function setIdType(int|string $idType): void {
        $this->idType = (int) $idType;
    }

    public function setSets(array $sets): void {
        $this->sets = array_values($sets);
        $this->campVainqueur = $this->calculerCampVainqueur();
    }

    public function setSetsDepuisFormulaire(array $setsFormulaire): void {
        $sets = [];
        $numero = 1;
        foreach ($setsFormulaire as $ligne) {
            $camp1 = trim((string) ($ligne['camp1'] ?? ''));
            $camp2 = trim((string) ($ligne['camp2'] ?? ''));
            if ($camp1 === '' && $camp2 === '') {
                continue;
            }
            $sets[] = new SetMatch($numero, (int) $camp1, (int) $camp2);
            $numero++;
        }
        $this->setSets($sets);
    }

    public function setParticipants(array $idsCamp1, array $idsCamp2): void {
        $this->participants = [];
        foreach ([1 => $idsCamp1, 2 => $idsCamp2] as $camp => $ids) {
            foreach ($ids as $id) {
                if ((int) $id > 0) {
                    $this->participants[] = new Participation($this->id ?? 0, (int) $id, $camp);
                }
            }
        }
    }

    public function setParticipantsDepuisBd(array $participants): void {
        $this->participants = $participants;
    }
}
