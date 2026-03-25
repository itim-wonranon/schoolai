<?php
require_once __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();

header('Content-Type: application/json');
$pdo = getDB();

try {
    $result = [];
    $result['timestamp'] = date('Y-m-d H:i:s');

    // Stats counts
    $result['stats'] = [
        'teachers'   => $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn(),
        'students'   => $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn(),
        'subjects'   => $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn(),
        'classrooms' => $pdo->query("SELECT COUNT(*) FROM classrooms")->fetchColumn(),
    ];

    // Attendance summary (Total aggregated)
    $attStmt = $pdo->query("SELECT
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS present,
        SUM(CASE WHEN status = 'absent'  THEN 1 ELSE 0 END) AS absent,
        SUM(CASE WHEN status = 'late'    THEN 1 ELSE 0 END) AS late,
        SUM(CASE WHEN status = 'leave'   THEN 1 ELSE 0 END) AS `leave`
        FROM attendance");
    $att = $attStmt->fetch();
    $result['attendance'] = [
        'present' => (int)($att['present'] ?? 0),
        'absent'  => (int)($att['absent'] ?? 0),
        'late'    => (int)($att['late'] ?? 0),
        'leave'   => (int)($att['leave'] ?? 0),
    ];

    // Grade distribution
    $gradeStmt = $pdo->query("SELECT
        SUM(CASE WHEN grade = '4' THEN 1 ELSE 0 END) AS grade4,
        SUM(CASE WHEN grade = '3' THEN 1 ELSE 0 END) AS grade3,
        SUM(CASE WHEN grade = '2' THEN 1 ELSE 0 END) AS grade2,
        SUM(CASE WHEN grade = '1' THEN 1 ELSE 0 END) AS grade1,
        SUM(CASE WHEN grade = '0' THEN 1 ELSE 0 END) AS grade0
        FROM grades");
    $gr = $gradeStmt->fetch();
    $result['grades'] = [
        'grade4' => (int)($gr['grade4'] ?? 0),
        'grade3' => (int)($gr['grade3'] ?? 0),
        'grade2' => (int)($gr['grade2'] ?? 0),
        'grade1' => (int)($gr['grade1'] ?? 0),
        'grade0' => (int)($gr['grade0'] ?? 0),
    ];

    echo json_encode($result);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
