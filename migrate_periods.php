<?php
require 'config/db.php';
$pdo = getDB();
try {
    $pdo->exec("ALTER TABLE school_periods ADD COLUMN level ENUM('middle', 'high') NOT NULL AFTER end_time");
} catch (Exception $e) {}
try {
    $pdo->exec("ALTER TABLE school_periods ADD COLUMN is_lunch TINYINT(1) DEFAULT 0 AFTER level");
} catch (Exception $e) {}
echo "Columns checked/added successfully.";
?>
