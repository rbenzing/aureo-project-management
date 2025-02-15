<?php
namespace App\Controllers;

use App\Utils\Validator;
use App\Models\User;
use App\Middleware\AuthMiddleware;

class AuthController {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate input data
            $validator = new Validator($_POST, [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                include __DIR__ . '/../views/auth/login.php';
                exit;
            }

            // Authenticate the user
            $userModel = new User();
            $user = $userModel->findByEmail($_POST['email']);
            if (!$user || !password_verify($_POST['password'], $user->password_hash)) {
                $_SESSION['error'] = 'Invalid email or password.';
                include __DIR__ . '/../views/auth/login.php';
                exit;
            }

            if (!$user->is_active) {
                $_SESSION['error'] = 'Your account is not active. Please check your email for activation instructions.';
                include __DIR__ . '/../views/auth/login.php';
                exit;
            }

            // Log the user in
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user'] = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'company_id' => $user->company_id,
            ];

            header('Location: /dashboard');
            exit;
        }

        // Display the login form
        include __DIR__ . '/../views/auth/login.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate input data
            $validator = new Validator($_POST, [
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'confirm_password' => 'required|string|same:password',
            ]);

            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                include __DIR__ . '/../views/auth/register.php';
                exit;
            }

            // Create the user
            $userModel = new User();
            $userModel->first_name = htmlspecialchars($_POST['first_name']);
            $userModel->last_name = htmlspecialchars($_POST['last_name']);
            $userModel->email = htmlspecialchars($_POST['email']);
            $userModel->password_hash = password_hash($_POST['password'], PASSWORD_ARGON2ID);
            $userModel->activation_token = bin2hex(random_bytes(16));
            $userModel->is_active = false;
            $userModel->save();

            // Send activation email
            \App\Utils\Email::sendActivationEmail($userModel);

            $_SESSION['success'] = 'Registration successful. Please check your email to activate your account.';
            header('Location: /login');
            exit;
        }

        // Display the registration form
        include __DIR__ . '/../views/auth/register.php';
    }

    public function resetPassword() {
        $token = $_GET['token'] ?? null;
        if (!$token) {
            $_SESSION['error'] = 'Invalid or missing token.';
            header('Location: /login');
            exit;
        }

        $userModel = new User();
        $user = $userModel->findByResetToken($token);
        if (!$user || strtotime($user->reset_password_token_expires_at) < time()) {
            $_SESSION['error'] = 'Invalid or expired token.';
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate input data
            $validator = new Validator($_POST, [
                'password' => 'required|string|min:8',
                'confirm_password' => 'required|string|same:password',
                'token' => 'required|string',
            ]);

            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                include __DIR__ . '/../views/auth/reset-password.php';
                exit;
            }

            // Update the user's password
            $user->password_hash = password_hash($_POST['password'], PASSWORD_ARGON2ID);
            $user->reset_password_token = null;
            $user->reset_password_token_expires_at = null;
            $user->save();

            $_SESSION['success'] = 'Your password has been reset successfully.';
            header('Location: /login');
            exit;
        }

        // Display the reset password form
        include __DIR__ . '/../views/auth/reset-password.php';
    }
}