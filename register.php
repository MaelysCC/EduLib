<?php
require_once __DIR__ . '/config/db.php';

if (isLoggedIn()) {
    header('Location: /mini-projet/');
    exit;
}

$errors = [];
$data   = ['nom' => '', 'prenom' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['nom']    = trim($_POST['nom']    ?? '');
    $data['prenom'] = trim($_POST['prenom'] ?? '');
    $data['email']  = trim($_POST['email']  ?? '');
    $mdp            = $_POST['mot_de_passe']     ?? '';
    $mdp2           = $_POST['mot_de_passe2']    ?? '';

    if ($data['nom'] === '')    $errors[] = 'Le nom est requis.';
    if ($data['prenom'] === '') $errors[] = 'Le prénom est requis.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Adresse e-mail invalide.';
    if (strlen($mdp) < 8)       $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    if ($mdp !== $mdp2)         $errors[] = 'Les mots de passe ne correspondent pas.';

    if (empty($errors)) {
        $pdo = getPDO();
        $check = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ?');
        $check->execute([$data['email']]);
        if ($check->fetch()) {
            $errors[] = 'Cette adresse e-mail est déjà utilisée.';
        } else {
            $hash = password_hash($mdp, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare(
                'INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$data['nom'], $data['prenom'], $data['email'], $hash]);
            header('Location: /mini-projet/login.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer un compte — EduLib</title>
    <link rel="stylesheet" href="/mini-projet/assets/style.css">
</head>
<body>

<nav>
    <div class="container nav-inner">
        <a href="/mini-projet/" class="nav-brand">EduLib</a>
        <div class="nav-links">
            <a href="/mini-projet/">Accueil</a>
            <a href="/mini-projet/resources.php">Ressources</a>
            <a href="/mini-projet/login.php">Connexion</a>
        </div>
    </div>
</nav>

<main>
    <div class="auth-box" style="max-width:480px">
        <h1>Créer un compte</h1>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): ?>
                    <div><?= h($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div style="display:flex;gap:.75rem">
                <div class="form-group" style="flex:1">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom"
                           value="<?= h($data['prenom']) ?>" required autocomplete="given-name">
                </div>
                <div class="form-group" style="flex:1">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom"
                           value="<?= h($data['nom']) ?>" required autocomplete="family-name">
                </div>
            </div>
            <div class="form-group">
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email"
                       value="<?= h($data['email']) ?>" required autocomplete="email">
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe <span class="text-muted text-sm">(8 caractères minimum)</span></label>
                <input type="password" id="mot_de_passe" name="mot_de_passe"
                       required autocomplete="new-password" minlength="8">
            </div>
            <div class="form-group">
                <label for="mot_de_passe2">Confirmer le mot de passe</label>
                <input type="password" id="mot_de_passe2" name="mot_de_passe2"
                       required autocomplete="new-password">
            </div>
            <button type="submit" class="btn" style="width:100%">Créer mon compte</button>
        </form>

        <p class="text-sm text-muted mt-2" style="text-align:center">
            Déjà inscrit ? <a href="/mini-projet/login.php">Se connecter</a>
        </p>
    </div>
</main>

<footer>
    <div class="container">
        <a href="#">Mentions légales</a> &mdash; EduLib &copy; <?= date('Y') ?>
    </div>
</footer>

</body>
</html>
