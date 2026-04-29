<?php
declare(strict_types=1);

/**
 * URL path prefix ending with "BackOffice/" (from SCRIPT_NAME), for correct links from any subfolder.
 */
function bo_web_base(): string
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $sn = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($sn !== '' && preg_match('#^(.*BackOffice/)#', $sn, $m)) {
        $cached = $m[1];
        return $cached;
    }
    $cached = '';
    return $cached;
}

/** Path under BackOffice/, e.g. "user/listUsers.php" */
function bo_url(string $path): string
{
    $path = ltrim(str_replace('\\', '/', $path), '/');
    return bo_web_base() . $path;
}

/** Parent of BackOffice/ (typically ".../view/") for assets and logout. */
function view_web_base(): string
{
    $bo = rtrim(bo_web_base(), '/');
    if ($bo === '') {
        return '';
    }
    return dirname($bo) . '/';
}
