<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\Jwt;
use Config\Database;

class AuthController extends BaseController
{
    public function login()
    {
        $data = $this->request->getJSON(true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (empty($email) || empty($password)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 400,
                'message' => 'Email and password are required',
            ]);
        }

        $db = Database::connect();
        $user = $db->table('users')->where('email', $email)->get()->getRowArray();

        if (!$user || $password !== $user['password']) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 401,
                'message' => 'Invalid credentials',
            ]);
        }

        $issuedAt = time();
        $expiresAt = $issuedAt + 3600; // 1 hour

        $token = Jwt::encode([
            'sub' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'team_id' => $user['team_id'],
            'iat' => $issuedAt,
            'exp' => $expiresAt,
        ]);

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 200,
            'message' => 'Login successful',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'expires_at' => date('Y-m-d\TH:i:sP', $expiresAt),
        ]);
    }
}