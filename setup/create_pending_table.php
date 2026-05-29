<?php
require_once '../includes/db.php';
$sql = "
CREATE TABLE IF NOT EXISTS pending_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    target_user_id INTEGER NOT NULL,
    action_type VARCHAR(20) NOT NULL,
    new_data JSON,
    requested_by INTEGER,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL
);
";
try {
    $conn->exec($sql);
    echo "Table created successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
