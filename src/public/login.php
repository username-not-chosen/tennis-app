<?php

require __DIR__ . '/../bootstrap.php';

if ($utilisateur) {
    header('Location: index.php');
    exit;
}

$email = '';
$erreur = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    if ($email === '' || $motDePasse === '') {
        $erreur = 'Merci de remplir tous les champs.';
    } else {
        $user = $utilisateursManager->verifierIdentifiants($email, $motDePasse);

        if ($user) {
            connecter($user);
            header('Location: index.php');
            exit;
        }

        $erreur = 'E-mail ou mot de passe incorrect.';
    }
}

$pageTitle = 'Se connecter';
require __DIR__ . '/../templates/header.php';
?>

<main class="auth">
    <div class="auth__court">
        <form class="auth__card" method="post" action="login.php">
            <h1>Se connecter</h1>

            <?php if ($erreur) { ?>
                <p class="alert alert--error"><?= htmlspecialchars($erreur) ?></p>
            <?php } ?>

            <label class="field">
                <span>E-mail</span>
                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required autocomplete="email">
            </label>

            <label class="field">
                <span>Mot de passe</span>
                <input type="password" name="mot_de_passe" required autocomplete="current-password">
            </label>

            <button class="btn btn--green btn--block" type="submit">Se connecter</button>

            <p class="auth__switch">Pas encore de compte ? <a href="register.php">S’inscrire</a></p>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
