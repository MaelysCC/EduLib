<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'edulib');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_CHARSET', 'utf8mb4');

define('CATEGORIES', [
    'Informatique',
    'Mathématiques',
    'Physique',
    'Chimie',
    'Droit',
    'Économie',
    'Langues',
    'Autre',
]);

function getPDO(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /mini-projet/login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /mini-projet/');
        exit;
    }
}

function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $stmt = getPDO()->prepare('SELECT * FROM utilisateurs WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

class ImageUploadException extends RuntimeException {}

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', '/mini-projet/uploads/');
define('UPLOAD_MAX_BYTES', 2 * 1024 * 1024); // 2 Mo
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('UPLOAD_MAX_WIDTH',  1200); // px — au-delà on réduit
define('UPLOAD_MAX_HEIGHT', 1200); // px
define('UPLOAD_WEBP_QUALITY', 82); // 0-100, bon ratio qualité/poids

/**
 * Traite un fichier uploadé depuis $_FILES[$field].
 * Convertit l'image en WebP, la redimensionne si nécessaire,
 * puis l'enregistre dans UPLOAD_DIR.
 * Retourne le nom du fichier .webp créé, ou null si aucun fichier.
 */
function handleImageUpload(string $field) {
    if (empty($_FILES[$field]['name'])) return null;

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new ImageUploadException('Erreur lors du téléversement du fichier.');
    }
    if ($file['size'] > UPLOAD_MAX_BYTES) {
        throw new ImageUploadException('L\'image ne doit pas dépasser 2 Mo.');
    }

    // Vérification du type MIME réel (pas la déclaration du client)
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, UPLOAD_ALLOWED_TYPES, true)) {
        throw new ImageUploadException('Format non accepté. Utilisez JPG, PNG, WebP ou GIF.');
    }

    if (!extension_loaded('gd')) {
        throw new ImageUploadException('L\'extension GD est requise pour le traitement des images.');
    }

    // Chargement de l'image en mémoire (détection automatique du format)
    $data = file_get_contents($file['tmp_name']);
    $src  = @imagecreatefromstring($data);
    if ($src === false) {
        throw new ImageUploadException('Impossible de lire l\'image.');
    }

    // Préserver la transparence (PNG/GIF/WebP avec alpha)
    imagesavealpha($src, true);

    // Redimensionnement si l'image dépasse les limites
    $origW = imagesx($src);
    $origH = imagesy($src);
    $ratio = $origW / max($origH, 1);

    if ($origW > UPLOAD_MAX_WIDTH || $origH > UPLOAD_MAX_HEIGHT) {
        if ($origW / UPLOAD_MAX_WIDTH > $origH / UPLOAD_MAX_HEIGHT) {
            $newW = UPLOAD_MAX_WIDTH;
            $newH = (int) round(UPLOAD_MAX_WIDTH / $ratio);
        } else {
            $newH = UPLOAD_MAX_HEIGHT;
            $newW = (int) round(UPLOAD_MAX_HEIGHT * $ratio);
        }

        $dst = imagecreatetruecolor($newW, $newH);
        // Fond transparent pour les images avec alpha
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefill($dst, 0, 0, $transparent);

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($src);
        $src = $dst;
    }

    // Enregistrement en WebP
    $filename = bin2hex(random_bytes(12)) . '.webp';
    $dest     = UPLOAD_DIR . $filename;

    $ok = imagewebp($src, $dest, UPLOAD_WEBP_QUALITY);
    imagedestroy($src);

    if (!$ok) {
        throw new ImageUploadException('Impossible d\'enregistrer l\'image en WebP.');
    }

    return $filename;
}

function deleteImageFile($filename) {
    if (!is_string($filename) || $filename === '') {
        return;
    }

    $path = UPLOAD_DIR . $filename;
    if (file_exists($path)) {
        unlink($path);
    }
}
