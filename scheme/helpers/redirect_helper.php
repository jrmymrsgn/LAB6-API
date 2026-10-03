<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

function app_redirect($path) {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    // If running through /public/index.php, strip that segment too
    $base = preg_replace('#/public$#', '', $base);
    header('Location: ' . $base . '/' . ltrim($path, '/'));
    exit;
}

function app_url($path = '') {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $base = preg_replace('#/public$#', '', $base);
    return $base . '/' . ltrim($path, '/');
}