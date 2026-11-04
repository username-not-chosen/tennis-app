<?php

require __DIR__ . '/../bootstrap.php';

$messageBienvenue = null;
if ($utilisateur && !empty($_SESSION['afficher_bienvenue'])) {
    $messageBienvenue = 'Bienvenue ' . $utilisateur['prenom'] . ' ' . $utilisateur['nom'] . ' !';
    unset($_SESSION['afficher_bienvenue']);
}

$pageTitle = 'Accueil';
require __DIR__ . '/../templates/header.php';
?>

<main>
    <!-- Hero -->
    <section class="hero">
        <div class="container hero__inner">
            <div class="hero__text">
                <h1 class="hero__title">SetPoint</h1>
                <p class="hero__tagline">Chaque match compte.</p>
                <?php if (!$utilisateur) { ?>
                    <a class="btn btn--lime" href="login.php">Se connecter</a>
                <?php } ?>
            </div>
            <img class="hero__ball" src="assets/img/balle.svg" alt="" width="238" height="238">
        </div>
    </section>

    <!-- Fonctionnalité 1 : enregistrer ses matchs -->
    <section class="feature">
        <div class="container feature__inner">
            <div class="feature__text">
                <h2>Enregistre tes matchs</h2>
                <p>Score, surface, durée, météo et ressenti : garde une trace de chaque rencontre.</p>
            </div>

            <!-- Illustration : carte de score (reprise de la maquette Figma) -->
            <div class="illu" aria-hidden="true">
                <img class="illu__ball" src="assets/img/balle.svg" alt="">
                <div class="card score-card">
                    <p class="score-card__title">Finale tournoi interne</p>
                    <p class="score-card__meta">11 mai 2026 · Terre battue</p>
                    <div class="score-card__row">
                        <span class="score-card__player is-bold">Toi</span>
                        <span class="set is-won">6</span><span class="set is-won">3</span><span class="set is-won">7</span>
                    </div>
                    <div class="score-card__row">
                        <span class="score-card__player">Lucas M.</span>
                        <span class="set">4</span><span class="set">6</span><span class="set">5</span>
                    </div>
                    <div class="score-card__footer">
                        <span class="badge badge--win">Victoire</span>
                        <span class="score-card__duration">1h36</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Fonctionnalité 2 : suivre sa progression -->
    <section class="feature feature--reverse">
        <div class="container feature__inner">
            <div class="feature__text">
                <h2>Suis ta progression</h2>
                <p>Taux de victoire, résultats par surface, temps de jeu par mois : vois comment tu évolues.</p>
            </div>

            <!-- Illustration : graphique (reprise de la maquette Figma) -->
            <div class="illu" aria-hidden="true">
                <img class="illu__ball illu__ball--small" src="assets/img/balle.svg" alt="">
                <div class="card chart-card">
                    <p class="chart-card__label">Taux de victoire</p>
                    <p class="chart-card__value">58% <span class="chip">+5%</span></p>
                    <div class="chart-card__bars">
                        <div class="bar" style="--h: 40%"><span>déc</span></div>
                        <div class="bar" style="--h: 51%"><span>jan</span></div>
                        <div class="bar" style="--h: 45%"><span>fév</span></div>
                        <div class="bar" style="--h: 68%"><span>mar</span></div>
                        <div class="bar" style="--h: 80%"><span>avr</span></div>
                        <div class="bar bar--highlight" style="--h: 100%"><span>mai</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php if ($messageBienvenue) { ?>
    <script>
        // json_encode transforme le texte en chaîne JavaScript sûre
        // (les guillemets et les balises sont échappés : pas d'injection possible)
        alert(<?= json_encode($messageBienvenue, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
    </script>
<?php } ?>

<?php require __DIR__ . '/../templates/footer.php'; ?>
