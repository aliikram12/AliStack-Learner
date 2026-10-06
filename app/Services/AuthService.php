<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Helpers\Auth;
use App\Repositories\SettingRepository;

class AuthService {
    protected UserRepository $userRepo;
    protected SettingRepository $settingRepo;

    public function __construct() {
        $this->userRepo = new UserRepository();
        $this->settingRepo = new SettingRepository();
    }

    public function register(array $data): array {
        $fullName = trim($data['full_name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['password_confirm'] ?? '';
        $terms = !empty($data['terms']);

        $errors = [];

        if (empty($fullName) || strlen($fullName) < 3) {
            $errors['full_name'] = 'Full name must be at least 3 characters long.';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please provide a valid email address.';
        } elseif ($this->userRepo->findByEmail($email)) {
            $errors['email'] = 'An account with this email address already exists.';
        }

        if (empty($username) || !preg_match('/^[a-zA-Z0-9_-]{3,30}$/', $username)) {
            $errors['username'] = 'Username must be 3-30 characters and alphanumeric.';
        } elseif ($this->userRepo->findByUsername($username)) {
            $errors['username'] = 'This username is already taken.';
        }

        if (empty($password) || strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long.';
        } elseif ($password !== $confirmPassword) {
            $errors['password_confirm'] = 'Passwords do not match.';
        }

        if (!$terms) {
            $errors['terms'] = 'You must accept the Terms of Service to register.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $userId = $this->userRepo->create([
            'full_name' => $fullName,
            'email' => $email,
            'username' => $username,
            'password_hash' => $passwordHash,
            'role' => 'student',
            'status' => 'active'
        ]);

        $user = $this->userRepo->findById($userId);
        Auth::login($user);

        // Audit log
        $this->settingRepo->logAudit($userId, 'USER_REGISTER', 'users', $userId, "New student registration: {$email}");

        return ['success' => true, 'user' => $user];
    }

    public function login(string $loginIdentifier, string $password): array {
        $loginIdentifier = trim($loginIdentifier);

        if (empty($loginIdentifier) || empty($password)) {
            return ['success' => false, 'error' => 'Please enter both your email/username and password.'];
        }

        // Check if input is email or username
        if (filter_var($loginIdentifier, FILTER_VALIDATE_EMAIL)) {
            $user = $this->userRepo->findByEmail($loginIdentifier);
        } else {
            $user = $this->userRepo->findByUsername($loginIdentifier);
        }

        // Generic error message to prevent account enumeration
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Invalid email/username or password.'];
        }

        if ($user['status'] === 'suspended') {
            return ['success' => false, 'error' => 'Your account has been suspended. Please contact platform support.'];
        }

        Auth::login($user);

        $this->settingRepo->logAudit((int)$user['id'], 'USER_LOGIN', 'users', (int)$user['id'], "User login successful");

        return ['success' => true, 'user' => $user];
    }

    public function forgotPassword(string $email): array {
        $email = strtolower(trim($email));
        $user = $this->userRepo->findByEmail($email);

        // Always return success to prevent account enumeration
        if (!$user) {
            return [
                'success' => true, 
                'message' => 'If an account matches that email address, password reset instructions have been created.'
            ];
        }

        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        $this->userRepo->createPasswordResetToken((int)$user['id'], $tokenHash, $expiresAt);

        // Log audit
        $this->settingRepo->logAudit((int)$user['id'], 'PASSWORD_RESET_REQUEST', 'users', (int)$user['id'], "Password reset requested");

        return [
            'success' => true,
            'message' => 'If an account matches that email address, password reset instructions have been created.',
            'reset_token' => $rawToken // in development/local mode, can be provided or used in reset link
        ];
    }

    public function resetPassword(string $rawToken, string $newPassword, string $confirmPassword): array {
        if (strlen($newPassword) < 8) {
            return ['success' => false, 'error' => 'Password must be at least 8 characters long.'];
        }
        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'error' => 'Passwords do not match.'];
        }

        $tokenHash = hash('sha256', $rawToken);
        $record = $this->userRepo->findValidPasswordResetToken($tokenHash);

        if (!$record) {
            return ['success' => false, 'error' => 'This password reset link is invalid or has expired.'];
        }

        $userId = (int)$record['user_id'];
        $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->userRepo->updatePassword($userId, $newHash);
        $this->userRepo->deletePasswordResetToken($userId);

        $this->settingRepo->logAudit($userId, 'PASSWORD_RESET_COMPLETE', 'users', $userId, "Password reset completed successfully");

        return ['success' => true, 'message' => 'Your password has been reset successfully. You may now log in.'];
    }

    public function updateProfile(int $userId, array $data, ?array $file = null): array {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found.'];
        }

        $fullName = trim($data['full_name'] ?? $user['full_name']);
        $bio = trim($data['bio'] ?? ($user['bio'] ?? ''));

        if (empty($fullName)) {
            return ['success' => false, 'error' => 'Full name cannot be empty.'];
        }

        $updateFields = [
            'full_name' => $fullName,
            'bio' => $bio
        ];

        // Avatar upload handling
        if ($file && !empty($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK) {
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            $maxBytes = 2 * 1024 * 1024; // 2MB

            if ($file['size'] > $maxBytes) {
                return ['success' => false, 'error' => 'Profile image must be 2MB or smaller.'];
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowedMimes, true)) {
                return ['success' => false, 'error' => 'Allowed image formats are JPG, PNG, and WebP.'];
            }

            $ext = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg'
            };

            $fileName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
            $destPath = dirname(__DIR__, 2) . '/public/assets/images/avatars/' . $fileName;
            
            // Ensure folder exists
            if (!is_dir(dirname($destPath))) {
                mkdir(dirname($destPath), 0775, true);
            }

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $updateFields['avatar_url'] = 'assets/images/avatars/' . $fileName;
            }
        }

        $this->userRepo->update($userId, $updateFields);

        // Update session
        $_SESSION['user']['full_name'] = $fullName;
        if (!empty($updateFields['avatar_url'])) {
            $_SESSION['user']['avatar_url'] = $updateFields['avatar_url'];
        }

        return ['success' => true, 'message' => 'Profile updated successfully.'];
    }
}
