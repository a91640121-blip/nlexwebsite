<?php
// Safe migration: adds user_reply and user_replied_at to messages table if missing
require_once __DIR__ . '/../vendor_autoload.php';
$config = require __DIR__ . '/../config/config.php';
use App\Classes\Database;

try {
    $db = Database::getConnection($config);
    // Check columns
    $check1 = $db->prepare("SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'messages' AND COLUMN_NAME = 'user_reply'");
    $check1->execute([':db' => $config['db_name']]);
    $has1 = (int)$check1->fetchColumn() > 0;

    $check2 = $db->prepare("SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'messages' AND COLUMN_NAME = 'user_replied_at'");
    $check2->execute([':db' => $config['db_name']]);
    $has2 = (int)$check2->fetchColumn() > 0;

    $queries = [];
    if (!$has1) $queries[] = "ALTER TABLE messages ADD COLUMN user_reply TEXT NULL";
    if (!$has2) $queries[] = "ALTER TABLE messages ADD COLUMN user_replied_at DATETIME NULL";

    if (empty($queries)) {
        echo "Nothing to do. Columns already exist.\n";
        exit(0);
    }

    foreach ($queries as $q) {
        echo "Running: $q\n";
        $db->exec($q);
    }

    echo "Migration complete.\n";
    // Show result
    $res = $db->query("DESCRIBE messages");
    $cols = $res->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) echo $c['Field'] . "\n";
    exit(0);
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
