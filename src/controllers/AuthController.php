<?php
namespace App\Controllers;

use App\Config\Config;
use App\Middleware\AuthMiddleware;
use App\Models\User;
use App\Utils\Email;
use App\Utils\Validator;

class AuthController {
    public function login($data = null) {
        if (!empty($data)) {
            // Validate input data
            $validator = new Validator($data, [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header('Location: /login');
                exit;
            }

            // Authenticate the user
            $userModel = new User();
            $user = $userModel->findByEmail($data['email']);
            if (!$user || !password_verify($data['password'], $user->password_hash)) {
                $_SESSION['error'] = 'Invalid email or password.';
                header('Location: /login');
                exit;
            }

            if (!$user->is_active) {
                $_SESSION['error'] = 'Your account is not active. Please check your email for activation instructions.';
                header('Location: /login');
                exit;
            }

            // Fetch roles and permissions
            $rolesAndPermissions = (new User())->getRolesAndPermissions($user->id);

            // Save session data
            \App\Middleware\SessionMiddleware::saveSession($user->id, [
                'profile' => [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'company_id' => $user->company_id,
                    'is_active' => $user->is_active
                ],
                'roles' => $rolesAndPermissions['roles'],
                'permissions' => $rolesAndPermissions['permissions'],
                'config' => Config::$app
            ]);

            header('Location: /dashboard');
            exit;
        }
        // Display the login form
        include_once __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Log out the user.
     */
    public function logout() {
        // Destroy the session
        \App\Middleware\SessionMiddleware::destroySession();

        // Redirect to the login page
        header('Location: /login');
        exit;
    }

    public function register($data = null) {
        if (!empty($data)) {
            // Validate input data
            $validator = new Validator($data, [
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'confirm_password' => 'required|string|same:password',
            ]);

            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header('Location: /register');
                exit;
            }

            // Create the user
            $userModel = new User();
            $userModel->first_name = htmlspecialchars($data['first_name']);
            $userModel->last_name = htmlspecialchars($data['last_name']);
            $userModel->email = htmlspecialchars($data['email']);
            $userModel->password_hash = password_hash($data['password'], PASSWORD_ARGON2ID);
            
            $userModel->generateActivationToken();
            $userModel->save();

            // Send activation email
            Email::sendActivationEmail($userModel);

            $_SESSION['success'] = 'Registration successful. Please check your email to activate your account.';
            header('Location: /login');
            exit;
        }

        // Display the register form
        include_once __DIR__ . '/../views/auth/register.php';
    }

    public function resetPassword($data = null) {
        $token = $data['token'] ?? null;
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
            $validator = new Validator($data, [
                'password' => 'required|string|min:8',
                'confirm_password' => 'required|string|same:password',
                'token' => 'required|string',
            ]);

            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header('Location: /reset-password');
                exit;
            }

            // Update the user's password
            $user->password_hash = password_hash($data['password'], PASSWORD_ARGON2ID);
            $user->reset_password_token = null;
            $user->save();

            $_SESSION['success'] = 'Your password has been reset successfully.';
            header('Location: /login');
            exit;
        }

        // Display the reset password form
        include_once __DIR__ . '/../views/auth/reset-password.php';
    }

    /**
     * Activate the users account
     */
    public function activate() {
        if (isset($_GET['token'])) {
            // Validate input data
            $validator = new Validator($_GET, [
                'token' => 'required|string',
            ]);

            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header('Location: /login');
                exit;
            }

            // Find the user with a valid activation token
            $user = (new User())->findByActivationToken($_GET['token']);
            if (!$user || strtotime($user->activation_token_expires_at) < time()) {
                $_SESSION['error'] = 'Invalid or expired activation token.';
                header('Location: /login');
                exit;
            }

            // Activate the account
            $user->is_active = true;
            $user->activation_token = null; // Clear the activation token
            $user->save();
        
            $_SESSION['success'] = 'Account activated successfully. You can now log in.';
        }

        // Display the login form
        include_once __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Process the "Forgot Password" form submission.
     */
    public function forgotPassword($data = null) {
        if (!empty($data)) {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error'] = 'Invalid CSRF token.';
                header('Location: /forgot-password');
                exit;
            }

            // Validate input data
            $validator = new Validator($_POST, [
                'email' => 'required|email',
            ]);
            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header('Location: /forgot-password');
                exit;
            }

            // Check if the user exists
            $user = (new User())->findByEmail($_POST['email']);
            if (!$user) {
                $_SESSION['error'] = 'No account found with that email address.';
                header('Location: /forgot-password');
                exit;
            }

            // Generate a password reset token
            $user->generateActivationToken();
            $user->save();

            // Send the password reset email
            if (Email::sendPasswordResetEmail($user)) {
                $_SESSION['success'] = 'A password reset link has been sent to your email.';
            } else {
                $_SESSION['error'] = 'Failed to send the password reset email. Please try again later.';
            }
        }

        // Display the forgot password form
        include_once __DIR__ . '/../views/auth/forgot-password.php';
    }
}