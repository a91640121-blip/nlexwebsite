<?php
namespace App\Classes;

use PDO;
use Exception;

class User extends Model
{
    public function create(string $fullName, string $email, string $password): int
    {
        $sql = 'INSERT INTO users (full_name, email, password_hash, created_at) VALUES (:full_name, :email, :password_hash, NOW())';
        $stmt = $this->db->prepare($sql);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute([':full_name' => $fullName, ':email' => $email, ':password_hash' => $hash]);
        return (int)$this->db->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT id, full_name, email, password_hash, created_at FROM users WHERE email = :email LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function getById(int $id): ?array
    {
        $sql = 'SELECT id, full_name, email, role, created_at FROM users WHERE id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Return user including password_hash for internal verification (protected use).
     */
    public function getByIdWithHash(int $id): ?array
    {
        $sql = 'SELECT id, full_name, email, password_hash, created_at FROM users WHERE id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Update user's password hash. Returns true on success.
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = 'UPDATE users SET password_hash = :hash WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':hash' => $hash, ':id' => $id]);
    }
}
