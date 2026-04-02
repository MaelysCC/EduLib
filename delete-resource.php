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
$stmt = $pdo->prepare('SELECT auteur_id, image FROM ressources WHERE id = ?');
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r || ($r['auteur_id'] !== $_SESSION['user_id'] && !isAdmin())) {
    header('Location: /mini-projet/resources.php');
    exit;
}

deleteImageFile($r['image']);
$pdo->prepare('DELETE FROM ressources WHERE id = ?')->execute([$id]);
header('Location: /mini-projet/resources.php?deleted=1');
exit;
