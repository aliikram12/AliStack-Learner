<?php
declare(strict_types=1);

/**
 * AliStack Learner - Super Admin Seeder
 * Powered by AliStack
 * 
 * Usage via CLI:
 *   php database/seed-super-admin.php
 * or with custom arguments:
 *   php database/seed-super-admin.php "Super Administrator" "admin@alistack.com" "superadmin" "Admin@AliStack2026!"
 */

require_once __DIR__ . '/../config/database.php';

$fullName = $argv[1] ?? 'Super Administrator';
$email    = strtolower(trim($argv[2] ?? 'admin@alistack.com'));
$username = trim($argv[3] ?? 'superadmin');
$password = $argv[4] ?? 'Admin@AliStack2026!';

echo "=== AliStack Learner - Super Admin Seeder ===\n";
echo "Target Email: {$email}\n";
echo "Target Username: {$username}\n";

try {
    $pdo = getDbConnection();
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, email, username, role FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    if ($existing) {
        echo "User already exists (ID: {$existing['id']}, Role: {$existing['role']}). Updating to super_admin and updating credentials...\n";
        $update = $pdo->prepare("
            UPDATE users 
            SET full_name = ?, email = ?, username = ?, password_hash = ?, role = 'super_admin', status = 'active'
            WHERE id = ?
        ");
        $update->execute([$fullName, $email, $username, $hash, $existing['id']]);
        $userId = $existing['id'];
        echo "Successfully updated user ID {$userId} to Super Admin!\n";
    } else {
        $insert = $pdo->prepare("
            INSERT INTO users (full_name, email, username, password_hash, role, status, created_at)
            VALUES (?, ?, ?, ?, 'super_admin', 'active', NOW())
        ");
        $insert->execute([$fullName, $email, $username, $hash]);
        $userId = (int)$pdo->lastInsertId();
        echo "Successfully created Super Admin user ID {$userId}!\n";
    }

    // Insert an initial audit log
    $audit = $pdo->prepare("
        INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address, created_at)
        VALUES (?, 'SEED_SUPER_ADMIN', 'users', ?, 'Seeded initial Super Admin account via CLI', '127.0.0.1', NOW())
    ");
    $audit->execute([$userId, $userId]);

    echo "\n----------------------------------------\n";
    echo "Super Admin Account Ready:\n";
    echo "Email:    {$email}\n";
    echo "Username: {$username}\n";
    echo "Password: {$password}\n";
    echo "Role:     super_admin\n";
    echo "----------------------------------------\n";
    echo "Keep these credentials safe or change the password after first login.\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Throwable $t) {
    echo "Error: " . $t->getMessage() . "\n";
    exit(1);
}
