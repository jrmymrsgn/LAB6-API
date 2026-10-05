<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

function handle_cors() {
    $allow_origin = config_item('allow_origin') ?? '*';

    header("Access-Control-Allow-Origin: {$allow_origin}");
    header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}