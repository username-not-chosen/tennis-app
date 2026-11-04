<?php

$pageTitle = $pageTitle ?? 'SetPoint';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> · SetPoint</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <header class="site-header">
        <div class="container site-header__inner">
            <a class="logo" href="index.php">SetPoint</a>

            <nav class="site-header__nav">
                <div class="lang-switch">
                    <a href="?lang=fr" class="is-active">FR</a>
                    <span>/</span>
                    <a href="?lang=en">EN</a>
                </div>

                <?php if ($utilisateur) { ?>
                    <span class="site-header__hello">Bonjour <?= htmlspecialchars($utilisateur['prenom']) ?></span>
                    <a class="btn btn--soft" href="logout.php">Se déconnecter</a>
                <?php } else { ?>
                    <a class="btn btn--soft" href="login.php">Se connecter</a>
                    <a class="btn btn--green" href="register.php">S’inscrire</a>
                <?php } ?>
            </nav>
        </div>
    </header>
