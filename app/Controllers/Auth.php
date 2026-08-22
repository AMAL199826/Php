<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function authenticate()
    {
        $username = trim((string) $this->request->getPost('username'));
        $password = trim((string) $this->request->getPost('password'));

        if (empty($username) || empty($password)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Username and password are required.');
        }

        $db = \Config\Database::connect();

        $user = $db->table('users')
            ->where('username', $username)
            ->get()
            ->getRowArray();

        if (!$user) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Invalid credentials');
        }

        // Temporary password check
        if ($password !== $user['password']) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Invalid credentials');
        }

        // Login successful
        session()->set([
            'user_id'   => $user['id'],
            'username'  => $user['username'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'role'      => $user['role'],
            'team_id'   => $user['team_id'],
            'logged_in' => true
        ]);

        return redirect()
            ->to('/dashboard')
            ->with('success', 'Welcome back!');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()
            ->to('/login')
            ->with('success', 'Logged out successfully');
    }
}