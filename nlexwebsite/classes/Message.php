<?php
namespace App\Classes;

use PDO;

class Message extends Model
{
    /**
     * Create a message. If $userId is provided and the table supports user_id column,
     * associate the message with that user. If the column doesn't exist, fall back.
     */
    public function create(string $name, string $email, string $message, ?int $userId = null): int
    {
        // Try to insert with user_id if provided
        if ($userId !== null) {
            try {
                $sql = 'INSERT INTO messages (user_id, name, email, message, created_at) VALUES (:user_id, :name, :email, :message, NOW())';
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':user_id' => $userId, ':name' => $name, ':email' => $email, ':message' => $message]);
                return (int)$this->db->lastInsertId();
            } catch (\PDOException $e) {
                // If insertion fails because the column doesn't exist, fall through to legacy insert
            }
        }

        $sql = 'INSERT INTO messages (name, email, message, created_at) VALUES (:name, :email, :message, NOW())';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':name' => $name, ':email' => $email, ':message' => $message]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Retrieve all messages. If messages.user_id exists, left join with users and return user info.
     */
    public function getAllWithUsers(): array
    {
        // Check if messages table has user_id column
        $hasUserId = false;
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM messages LIKE 'user_id'");
            $hasUserId = (bool)$stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $hasUserId = false;
        }

        if ($hasUserId) {
            // Check for optional columns and build select list dynamically
            $optional = [];
            try {
                $r = $this->db->query("SHOW COLUMNS FROM messages LIKE 'user_reply'")->fetch(PDO::FETCH_ASSOC);
                if ($r) $optional[] = 'm.user_reply';
            } catch (\Exception $e) { }
            try {
                $r = $this->db->query("SHOW COLUMNS FROM messages LIKE 'user_replied_at'")->fetch(PDO::FETCH_ASSOC);
                if ($r) $optional[] = 'm.user_replied_at';
            } catch (\Exception $e) { }

            $select = 'm.id, m.name, m.email, m.message, m.reply, m.replied_at, m.replied_by, m.status';
            if (!empty($optional)) $select .= ', ' . implode(', ', $optional);
            $select .= ', m.created_at, u.id AS user_id, u.full_name, u.email AS user_email';

            $sql = "SELECT $select FROM messages m LEFT JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $sql = 'SELECT id, name, email, message, created_at FROM messages ORDER BY created_at DESC';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reply to a message (admin action). Sets reply text, replied_at and replied_by and updates status.
     */
    public function replyToMessage(int $messageId, string $replyText, int $adminId): bool
    {
        $sql = 'UPDATE messages SET reply = :reply, replied_at = NOW(), replied_by = :admin, status = :status WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':reply' => $replyText, ':admin' => $adminId, ':status' => 'replied', ':id' => $messageId]);
    }

    /**
     * Get messages for a user (their inbox), most recent first.
     */
    public function getByUser(int $userId): array
    {
        $sql = 'SELECT id, name, email, message, reply, replied_at, replied_by, status, created_at FROM messages WHERE user_id = :uid ORDER BY created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Save a reply from the user to an admin reply. Stores in user_reply and updates status if column exists.
     */
    public function userReplyToMessage(int $messageId, int $userId, string $replyText): bool
    {
        // Try to update a user_reply column if it exists
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM messages LIKE 'user_reply'");
            $has = (bool)$stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $has = false;
        }

        if ($has) {
            $sql = 'UPDATE messages SET user_reply = :ureply, user_replied_at = NOW(), status = :status WHERE id = :id AND user_id = :uid';
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':ureply' => $replyText, ':status' => 'open', ':id' => $messageId, ':uid' => $userId]);
        }

        // Fallback: append to message thread in a simple way by concatenating to message column (not ideal but safe)
        $sql = 'UPDATE messages SET message = CONCAT(message, "\n\n--- User reply ---\n", :ureply) WHERE id = :id AND user_id = :uid';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':ureply' => $replyText, ':id' => $messageId, ':uid' => $userId]);
    }
}
