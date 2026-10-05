<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AuthController extends Controller {
    public function __construct()
    {
        parent::__construct();
        $this->call->database();
        $this->call->library('api');
    }

    // POST /api/login
    public function login() {
        $this->api->require_method('POST');
        $this->api->rate_limit();

        $data = $this->api->body();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (!$username || !$password) {
            $this->api->respond_error('Username and password are required.', 422);
        }

        $stmt = $this->db->raw(
            "SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1",
            [$username]
        );
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->api->respond_error('Invalid username or password.', 401);
        }

        $tokens = $this->api->issue_tokens([
            'id'   => $user['id'],
            'role' => $user['role'],
        ]);

        $this->api->respond([
            'message' => 'Login successful.',
            'user'    => ['id' => $user['id'], 'username' => $user['username'], 'role' => $user['role']],
            'tokens'  => $tokens,
        ]);
    }

    // POST /api/refresh
    public function refresh() {
        $this->api->require_method('POST');

        $data = $this->api->body();
        $refresh_token = $data['refresh_token'] ?? '';

        if (!$refresh_token) {
            $this->api->respond_error('Refresh token is required.', 422);
        }

        $this->api->refresh_access_token($refresh_token);
    }

    // POST /api/logout
    public function logout() {
        $this->api->require_method('POST');

        $data = $this->api->body();
        $refresh_token = $data['refresh_token'] ?? '';

        if ($refresh_token) {
            $this->api->revoke_refresh_token($refresh_token);
        }

        $this->api->respond(['message' => 'Logged out successfully.']);
    }
}