<?php
declare(strict_types=1);

/**
 * Upload / suppression des photos de profil (stockées sous view/uploads/profiles/).
 * Chemin relatif à enregistrer en base : uploads/profiles/user_{id}.ext
 */
final class ProfilePhotoHelper
{
    public const MAX_BYTES = 2_097_152; // 2 Mo

    public static function storageDir(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profiles';
    }

    /** Chemin relatif depuis le dossier view (pour URL : $baseUrl . '/' . path). */
    public static function viewRelativePathFor(int $userId, string $ext): string
    {
        $ext = strtolower($ext);
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $ext = 'jpg';
        }
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }
        return 'uploads/profiles/user_' . $userId . '.' . $ext;
    }

    public static function deleteAllForUser(int $userId): void
    {
        $dir = self::storageDir();
        if (!is_dir($dir)) {
            return;
        }
        foreach ((glob($dir . DIRECTORY_SEPARATOR . 'user_' . $userId . '.*') ?: []) as $f) {
            if (is_file($f)) {
                @unlink($f);
            }
        }
    }

    /**
     * @param array{name:string,type:string,tmp_name:string,error:int,size:int} $file entrée $_FILES['photo']
     * @return array{ok:bool, path:?string, error:?string} path = chemin relatif view ou null si aucun fichier envoyé
     */
    public static function saveFromUpload(array $file, int $userId): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'path' => null, 'error' => null];
        }
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'path' => null, 'error' => 'Erreur lors de l’envoi du fichier.'];
        }
        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > self::MAX_BYTES) {
            return ['ok' => false, 'path' => null, 'error' => 'Image trop volumineuse (max. 2 Mo).'];
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return ['ok' => false, 'path' => null, 'error' => 'Fichier invalide.'];
        }

        $mime = self::detectMime($tmp);
        $extMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        if (!isset($extMap[$mime])) {
            return ['ok' => false, 'path' => null, 'error' => 'Format non supporté (JPEG, PNG ou WebP uniquement).'];
        }
        $ext = $extMap[$mime];

        $dir = self::storageDir();
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            return ['ok' => false, 'path' => null, 'error' => 'Impossible de créer le dossier de stockage.'];
        }

        self::deleteAllForUser($userId);

        $destPhysical = $dir . DIRECTORY_SEPARATOR . 'user_' . $userId . '.' . $ext;
        $relative = self::viewRelativePathFor($userId, $ext);

        if (self::tryResizeAndSave($tmp, $destPhysical, $mime)) {
            return ['ok' => true, 'path' => $relative, 'error' => null];
        }
        if (@move_uploaded_file($tmp, $destPhysical)) {
            return ['ok' => true, 'path' => $relative, 'error' => null];
        }
        return ['ok' => false, 'path' => null, 'error' => 'Impossible d’enregistrer l’image.'];
    }

    private static function detectMime(string $path): string
    {
        if (function_exists('finfo_open')) {
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            if ($fi) {
                $m = finfo_file($fi, $path) ?: '';
                finfo_close($fi);
                if ($m !== '') {
                    return $m;
                }
            }
        }
        $info = @getimagesize($path);
        return is_array($info) && !empty($info['mime']) ? (string) $info['mime'] : '';
    }

    private static function tryResizeAndSave(string $src, string $dest, string $mime): bool
    {
        if (!function_exists('imagecreatetruecolor')) {
            return false;
        }
        $img = null;
        if ($mime === 'image/jpeg') {
            $img = @imagecreatefromjpeg($src);
        } elseif ($mime === 'image/png') {
            $img = @imagecreatefrompng($src);
        } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
            $img = @imagecreatefromwebp($src);
        }
        if (!$img) {
            return false;
        }
        $w = imagesx($img);
        $h = imagesy($img);
        if ($w < 1 || $h < 1) {
            imagedestroy($img);
            return false;
        }
        $max = 720;
        if ($w > $max || $h > $max) {
            if ($w >= $h) {
                $nw = $max;
                $nh = (int) round($h * ($max / $w));
            } else {
                $nh = $max;
                $nw = (int) round($w * ($max / $h));
            }
        } else {
            $nw = $w;
            $nh = $h;
        }
        $out = imagecreatetruecolor($nw, $nh);
        if (!$out) {
            imagedestroy($img);
            return false;
        }
        if ($mime === 'image/png') {
            imagealphablending($out, false);
            imagesavealpha($out, true);
        }
        imagecopyresampled($out, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        $ok = false;
        if ($mime === 'image/png') {
            $ok = @imagepng($out, $dest, 6);
        } elseif ($mime === 'image/webp' && function_exists('imagewebp')) {
            $ok = @imagewebp($out, $dest, 82);
        } else {
            $ok = @imagejpeg($out, $dest, 88);
        }
        imagedestroy($out);
        return $ok;
    }
}
