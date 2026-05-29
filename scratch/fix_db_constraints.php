<?php
require_once 'includes/db.php';
try {
    // 1. Drop existing constraints if they exist (to avoid duplicates or conflicts)
    $conn->exec("ALTER TABLE pending_actions DROP CONSTRAINT IF EXISTS pending_actions_target_user_id_fkey");
    $conn->exec("ALTER TABLE pending_actions DROP CONSTRAINT IF EXISTS pending_actions_requested_by_fkey");
    $conn->exec("ALTER TABLE pending_actions DROP CONSTRAINT IF EXISTS fk_target_user");
    $conn->exec("ALTER TABLE pending_actions DROP CONSTRAINT IF EXISTS fk_requested_by");

    // 2. Add corrected constraints with ON DELETE CASCADE / SET NULL
    $conn->exec("ALTER TABLE pending_actions ADD CONSTRAINT fk_target_user FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE");
    $conn->exec("ALTER TABLE pending_actions ADD CONSTRAINT fk_requested_by FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL");

    echo "Successfully updated 'pending_actions' constraints with cascading deletes.\n";
} catch (Exception $e) {
    echo "Error updating constraints: " . $e->getMessage() . "\n";
}
?>
