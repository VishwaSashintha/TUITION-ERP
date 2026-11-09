<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Request;
use App\Services\AuthService;
use App\Services\TenantManager;

class AuthController extends Controller
{
    private AuthService $auth;
    private TenantManager $tenantManager;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->tenantManager = new TenantManager();
    }

    public function showLogin(Request $request): void
    {
        $this->render('auth/login', [
            'title' => 'Sign In',
        ], 'layouts/auth');
    }

    public function login(Request $request): void
    {
        $login       = (string) $request->input('login', '');
        $password    = (string) $request->input('password', '');
        $tenantCode  = (string) $request->input('tenant_code', '');

        if (empty($login) || empty($password)) {
            $this->back('error', 'Please provide both your email/username and password.', $request->all());
            return;
        }

        try {
            $user = $this->auth->attempt($login, $password, !empty($tenantCode) ? $tenantCode : null);

            if (!$user) {
                $this->back('error', 'Invalid login credentials. Please verify your details.', ['login' => $login, 'tenant_code' => $tenantCode]);
                return;
            }

            //Redirect
            $role = $user['role_name'] ?? ($user['role'] ?? 'guest');
            if (in_array($role, ['super_admin', 'support_admin'], true)) {
                $this->redirect(url('platform/dashboard'), 'success', 'Welcome back, Platform Administrator.');
                return;
            }

            if ($role === 'student') {
                $this->redirect(url('portal/student/dashboard'), 'success', 'Welcome to your student portal.');
                return;
            }

            if ($role === 'parent') {
                $this->redirect(url('portal/parent/dashboard'), 'success', 'Welcome to the parent portal.');
                return;
            }

            $this->redirect(url('dashboard'), 'success', "Welcome back, {$user['name']}!");
        } catch (\RuntimeException $e) {
            $this->back('error', $e->getMessage(), ['login' => $login, 'tenant_code' => $tenantCode]);
        }
    }

    public function logout(Request $request): void
    {
        $this->auth->logout();
        $this->redirect(url('login'), 'success', 'You have been signed out safely.');
    }
}
