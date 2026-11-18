<?php

require_once __DIR__ . '/MatchTennis.php';

class MatchsManager {
    private PDO $pdo;

    private const SELECT_MATCH = '
        SELECT m.*, s.libelle AS surface, me.libelle AS meteo, t.libelle AS type_match
        FROM matchs m
        JOIN surfaces s ON s.id_surface = m.id_surface
        LEFT JOIN meteos me ON me.id_meteo = m.id_meteo
        JOIN types_match t ON t.id_type = m.id_type';

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Validation

    public function validerMatch(MatchTennis $match, int $idUtilisateurConnecte): array {
        $erreurs = $match->valider();

        if ($match->getCampDe($idUtilisateurConnecte) === null) {
            $erreurs[] = 'Tu dois faire partie des joueur·euses du match.';
        }
        if (!$this->existe('surfaces', 'id_surface', $match->getIdSurface())) {
            $erreurs[] = 'La surface choisie n’existe pas.';
        }
        if ($match->getIdMeteo() !== null && !$this->existe('meteos', 'id_meteo', $match->getIdMeteo())) {
            $erreurs[] = 'La météo choisie n’existe pas.';
        }

        $ids = array_map(fn(Participation $p) => $p->getIdUtilisateur(), $match->getParticipants());
        if ($ids && $this->compterUtilisateurs($ids) !== count(array_unique($ids))) {
            $erreurs[] = 'Un·e des joueur·euses choisi·es n’existe pas.';
        }

        return $erreurs;
    }

    // Create

    public function ajouterMatch(MatchTennis $match, int $idCreateur): int {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO matchs (titre, description, date_match, duree, lieu, camp_vainqueur,
                                     id_createur, id_surface, id_meteo, id_type)
                 VALUES (:titre, :description, :date_match, :duree, :lieu, :camp_vainqueur,
                         :id_createur, :id_surface, :id_meteo, :id_type)'
            );
            $stmt->execute($this->parametresMatch($match) + ['id_createur' => $idCreateur]);
            $idMatch = (int) $this->pdo->lastInsertId();

            $this->enregistrerSets($idMatch, $match->getSets());
            foreach ($match->getParticipants() as $participant) {
                $this->enregistrerParticipant($idMatch, $participant);
            }

            $this->pdo->commit();
            return $idMatch;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Read

    public function getMatchPourUtilisateur(int $idMatch, int $idUtilisateur): ?MatchTennis {
        $stmt = $this->pdo->prepare(
            self::SELECT_MATCH . '
            JOIN participations p ON p.id_match = m.id_match AND p.id_utilisateur = :id_utilisateur
            WHERE m.id_match = :id_match'
        );
        $stmt->execute(['id_match' => $idMatch, 'id_utilisateur' => $idUtilisateur]);
        $ligne = $stmt->fetch();

        if (!$ligne) {
            return null;
        }

        $matchs = [MatchTennis::depuisLigneBd($ligne)];
        $this->chargerSetsEtParticipants($matchs);
        return $matchs[0];
    }

    public function getMatchsUtilisateur(int $idUtilisateur, ?int $limite = null): array {
        $sql = self::SELECT_MATCH . '
            JOIN participations p ON p.id_match = m.id_match AND p.id_utilisateur = :id_utilisateur
            ORDER BY m.date_match DESC';
        if ($limite !== null) {
            $sql .= ' LIMIT :limite';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('id_utilisateur', $idUtilisateur, PDO::PARAM_INT);
        if ($limite !== null) {
            $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
        }
        $stmt->execute();

        $matchs = array_map(fn(array $ligne) => MatchTennis::depuisLigneBd($ligne), $stmt->fetchAll());
        $this->chargerSetsEtParticipants($matchs);
        return $matchs;
    }

    public function estParticipant(int $idMatch, int $idUtilisateur): bool {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM participations WHERE id_match = :id_match AND id_utilisateur = :id_utilisateur'
        );
        $stmt->execute(['id_match' => $idMatch, 'id_utilisateur' => $idUtilisateur]);
        return $stmt->fetchColumn() > 0;
    }

    public function estCreateur(int $idMatch, int $idUtilisateur): bool {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM matchs WHERE id_match = :id_match AND id_createur = :id_utilisateur'
        );
        $stmt->execute(['id_match' => $idMatch, 'id_utilisateur' => $idUtilisateur]);
        return $stmt->fetchColumn() > 0;
    }

    // Update

    public function modifierMatch(MatchTennis $match, int $idUtilisateur): ?array {
        $idMatch = $match->getId();
        if ($idMatch === null || !$this->estCreateur($idMatch, $idUtilisateur)) {
            return null;
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE matchs
                 SET titre = :titre, description = :description, date_match = :date_match,
                     duree = :duree, lieu = :lieu, camp_vainqueur = :camp_vainqueur,
                     id_surface = :id_surface, id_meteo = :id_meteo, id_type = :id_type
                 WHERE id_match = :id_match AND id_createur = :id_createur'
            );
            $stmt->execute($this->parametresMatch($match) + [
                'id_match' => $idMatch,
                'id_createur' => $idUtilisateur,
            ]);

            $stmt = $this->pdo->prepare('DELETE FROM sets WHERE id_match = :id_match');
            $stmt->execute(['id_match' => $idMatch]);
            $this->enregistrerSets($idMatch, $match->getSets());

            $anciensIds = $this->getIdsParticipants($idMatch);
            $nouveauxIds = array_map(fn(Participation $p) => $p->getIdUtilisateur(), $match->getParticipants());

            $idsARetirer = array_diff($anciensIds, $nouveauxIds);
            if ($idsARetirer) {
                $marqueurs = implode(', ', array_fill(0, count($idsARetirer), '?'));
                $stmt = $this->pdo->prepare(
                    "DELETE FROM participations WHERE id_match = ? AND id_utilisateur IN ($marqueurs)"
                );
                $stmt->execute([$idMatch, ...array_values($idsARetirer)]);
            }

            foreach ($match->getParticipants() as $participant) {
                $this->enregistrerParticipant($idMatch, $participant);
            }

            $this->pdo->commit();
            return array_values(array_diff($nouveauxIds, $anciensIds));
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Delete

    public function supprimerMatch(int $idMatch, int $idUtilisateur): bool {
        $stmt = $this->pdo->prepare('DELETE FROM matchs WHERE id_match = :id_match AND id_createur = :id_createur');
        $stmt->execute(['id_match' => $idMatch, 'id_createur' => $idUtilisateur]);
        return $stmt->rowCount() > 0;
    }

    // Méthodes privées

    private function parametresMatch(MatchTennis $match): array {
        return [
            'titre' => $match->getTitre(),
            'description' => $match->getDescription(),
            'date_match' => $match->getDateMatch(),
            'duree' => $match->getDuree(),
            'lieu' => $match->getLieu(),
            'camp_vainqueur' => $match->calculerCampVainqueur(),
            'id_surface' => $match->getIdSurface(),
            'id_meteo' => $match->getIdMeteo(),
            'id_type' => $match->getIdType(),
        ];
    }

    private function enregistrerSets(int $idMatch, array $sets): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO sets (id_match, numero_set, jeux_camp1, jeux_camp2)
             VALUES (:id_match, :numero_set, :jeux_camp1, :jeux_camp2)'
        );
        foreach ($sets as $set) {

            $stmt->execute([
                'id_match' => $idMatch,
                'numero_set' => $set->getNumero(),
                'jeux_camp1' => $set->getJeuxCamp1(),
                'jeux_camp2' => $set->getJeuxCamp2(),
            ]);
        }
    }

    private function enregistrerParticipant(int $idMatch, Participation $participant): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO participations (id_match, id_utilisateur, camp)
             VALUES (:id_match, :id_utilisateur, :camp)
             ON DUPLICATE KEY UPDATE camp = VALUES(camp)'
        );
        $stmt->execute([
            'id_match' => $idMatch,
            'id_utilisateur' => $participant->getIdUtilisateur(),
            'camp' => $participant->getCamp(),
        ]);
    }

    private function getIdsParticipants(int $idMatch): array {
        $stmt = $this->pdo->prepare('SELECT id_utilisateur FROM participations WHERE id_match = :id_match');
        $stmt->execute(['id_match' => $idMatch]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private function chargerSetsEtParticipants(array $matchs): void {
        if (!$matchs) {
            return;
        }

        $parId = [];
        foreach ($matchs as $match) {
            $parId[$match->getId()] = $match;
        }
        $ids = array_keys($parId);

        $marqueurs = implode(', ', array_fill(0, count($ids), '?'));

        $stmt = $this->pdo->prepare("SELECT * FROM sets WHERE id_match IN ($marqueurs) ORDER BY numero_set");
        $stmt->execute($ids);
        $sets = [];
        foreach ($stmt->fetchAll() as $ligne) {
            $sets[(int) $ligne['id_match']][] = SetMatch::depuisLigneBd($ligne);
        }

        $stmt = $this->pdo->prepare(
            "SELECT p.id_match, p.id_utilisateur, p.camp, u.prenom, u.nom
             FROM participations p
             JOIN utilisateurs u ON u.id_utilisateur = p.id_utilisateur
             WHERE p.id_match IN ($marqueurs)
             ORDER BY p.camp, u.nom, u.prenom"
        );
        $stmt->execute($ids);
        $participants = [];
        foreach ($stmt->fetchAll() as $ligne) {
            $participants[(int) $ligne['id_match']][] = Participation::depuisLigneBd($ligne);
        }

        foreach ($parId as $id => $match) {
            $match->setSets($sets[$id] ?? []);
            $match->setParticipantsDepuisBd($participants[$id] ?? []);
        }
    }

    private function existe(string $table, string $colonne, int $id): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM $table WHERE $colonne = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    private function compterUtilisateurs(array $ids): int {
        $ids = array_values(array_unique($ids));
        $marqueurs = implode(', ', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE id_utilisateur IN ($marqueurs)");
        $stmt->execute($ids);
        return (int) $stmt->fetchColumn();
    }
}
