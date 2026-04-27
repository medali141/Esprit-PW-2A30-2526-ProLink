<?php
declare(strict_types=1);

/**
 * Téléversement d’images pour le forum (JPEG, PNG, GIF, WebP, max. 2 Mo).
 * Le chemin stocké en base est relatif à `view/` (ex. uploads/forum/f_… .jpg).
 */
class ForumImageHelper
{
    private const MAX_BYTES = 2 * 1024 * 1024;

    /**
     * @return array{ok: bool, path: ?string, error: ?string} path null si aucun fichier
     */
    public static function processUpload(string $fieldName = 'photo'): array
    {
        if (empty($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
            return ['ok' => true, 'path' => null, 'error' => null];
        }
        $f = $_FILES[$fieldName];
        if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'path' => null, 'error' => null];
        }
        if (($f['error'] ?? 0) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'path' => null, 'error' => 'Téléversement de l’image impossible.'];
        }
        if (($f['size'] ?? 0) > self::MAX_BYTES) {
            return ['ok' => false, 'path' => null, 'error' => 'Image trop lourde (max. 2 Mo).'];
        }
        $tmp = $f['tmp_name'] ?? '';
        if (!is_string($tmp) || $tmp === '' || !is_uploaded_file($tmp)) {
            return ['ok' => false, 'path' => null, 'error' => 'Fichier image invalide.'];
        }
        $info = @getimagesize($tmp);
        if ($info === false) {
            return ['ok' => false, 'path' => null, 'error' => 'Le fichier n’est pas une image valide.'];
        }
        $mime = $info['mime'] ?? '';
        $extMap = [
            'image/jpeg' => '.jpg',
            'image/png' => '.png',
            'image/gif' => '.gif',
            'image/webp' => '.webp',
        ];
        if (!isset($extMap[$mime])) {
            return ['ok' => false, 'path' => null, 'error' => 'Format non autorisé (JPEG, PNG, GIF, WebP).'];
        }
        $ext = $extMap[$mime];
        $viewRoot = dirname(__DIR__) . '/view';
        $dir = $viewRoot . '/uploads/forum';
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            return ['ok' => false, 'path' => null, 'error' => 'Dossier d’upload indisponible.'];
        }
        $name = 'f_' . time() . '_' . bin2hex(random_bytes(4)) . $ext;
        $dest = $dir . DIRECTORY_SEPARATOR . $name;
        if (!@move_uploaded_file($tmp, $dest)) {
            return ['ok' => false, 'path' => null, 'error' => 'Enregistrement de l’image échoué.'];
        }
        return ['ok' => true, 'path' => 'uploads/forum/' . $name, 'error' => null];
    }

    public static function removeFileIfSafe(?string $dbPath): void
    {
        if ($dbPath === null || $dbPath === '') {
            return;
        }
        if (!preg_match('#^uploads/forum/f_[a-zA-Z0-9._-]+\.(jpe?g|png|gif|webp)$#i', $dbPath)) {
            return;
        }
        $full = dirname(__DIR__) . '/view/' . str_replace('/', DIRECTORY_SEPARATOR, $dbPath);
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
