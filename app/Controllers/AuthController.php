<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use function App\Core\flash;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->view('auth/login', ['title' => 'Login']);
    }

    public function login(): void
    {
        if (Auth::attempt(trim($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''))) {
            $this->redirect('/');
        }
        flash('error', 'Invalid email or password.');
        $this->redirect('/login');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
