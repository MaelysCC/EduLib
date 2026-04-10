<?php
require_once __DIR__ . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /mini-projet/resources.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: /mini-projet/resources.php');
    exit;
}

$pdo  = getPDO();
$stmt = $pdo->prepare('SELECT auteur_id, ressource_id FROM commentaires WHERE id = ?');
$stmt->execute([$id]);
$c = $stmt->fetch();

if (!$c || ($c['auteur_id'] !== $_SESSION['user_id'] && !isAdmin())) {
    header('Location: /mini-projet/resources.php');
    exit;
}

$pdo->prepare('DELETE FROM commentaires WHERE id = ?')->execute([$id]);
header('Location: /mini-projet/resource-detail.php?id=' . $c['ressource_id'] . '#commentaires');
exit;
