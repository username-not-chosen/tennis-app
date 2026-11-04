<?php

require __DIR__ . '/../bootstrap.php';

if ($utilisateur) {
    header('Location: index.php');
    exit;
}

// Valeurs affichées dans le formulaire (vides au premier affichage)
$donnees = [
    'prenom' => '',
    'nom' => '',
    'email' => '',
    'sexe' => '',
    'date_naissance' => '',
];
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($donnees as $champ => $valeur) {
        $donnees[$champ] = trim($_POST[$champ] ?? '');
    }
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    $nouvelUtilisateur = new Utilisateur($donnees);
    $erreurs = $nouvelUtilisateur->valider($motDePasse);

    if (!$erreurs && $utilisateursManager->emailExiste($nouvelUtilisateur->getEmail())) {
        $erreurs[] = 'Un compte existe déjà avec cette adresse e-mail.';
    }

    if (!$erreurs) {
        $id = $utilisateursManager->ajouterUtilisateur($nouvelUtilisateur, $motDePasse);

        connecter($utilisateursManager->getUtilisateurParId($id));
        header('Location: index.php');
        exit;
    }
}

$pageTitle = 'S’inscrire';
require __DIR__ . '/../templates/header.php';
?>

<main class="auth">
    <div class="auth__court auth__court--ball-left">
        <form class="auth__card" method="post" action="register.php">
            <h1>S’inscrire</h1>

            <?php if ($erreurs) { ?>
                <div class="alert alert--error">
                    <ul>
                        <?php foreach ($erreurs as $erreur) { ?>
                            <li><?= htmlspecialchars($erreur) ?></li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>

            <div class="field-row">
                <label class="field">
                    <span>Prénom</span>
                    <input type="text" name="prenom" value="<?= htmlspecialchars($donnees['prenom']) ?>" required maxlength="100" autocomplete="given-name">
                </label>
                <label class="field">
                    <span>Nom</span>
                    <input type="text" name="nom" value="<?= htmlspecialchars($donnees['nom']) ?>" required maxlength="100" autocomplete="family-name">
                </label>
            </div>

            <label class="field">
                <span>E-mail</span>
                <input type="email" name="email" value="<?= htmlspecialchars($donnees['email']) ?>" required maxlength="255" autocomplete="email">
            </label>

            <label class="field">
                <span>Mot de passe <small>(8 caractères min.)</small></span>
                <input type="password" name="mot_de_passe" required minlength="<?= Utilisateur::MOT_DE_PASSE_LONGUEUR_MIN ?>" autocomplete="new-password">
            </label>

            <div class="field-row">
                <label class="field">
                    <span>Sexe</span>
                    <select name="sexe">
                        <option value="">—</option>
                        <?php foreach (Utilisateur::SEXES as $sexe) { ?>
                            <option value="<?= htmlspecialchars($sexe) ?>" <?= $donnees['sexe'] === $sexe ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sexe) ?>
                            </option>
                        <?php } ?>
                    </select>
                </label>
                <label class="field">
                    <span>Date de naissance</span>
                    <input type="date" name="date_naissance" value="<?= htmlspecialchars($donnees['date_naissance']) ?>" max="<?= date('Y-m-d') ?>">
                </label>
            </div>

            <button class="btn btn--green btn--block" type="submit">S’inscrire</button>

            <p class="auth__switch">Déjà un compte ? <a href="login.php">Se connecter</a></p>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
