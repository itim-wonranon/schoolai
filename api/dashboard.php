<?php
ob_start();
require_once __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../config/db.php';

// Reliable AJAX JSON response header
header('Content-Type: application/json');

// Check login without redirect for AJAX
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthenticated', 'message' => 'Session expired. Please log in again.']);
    exit;
}
session_write_close();

$pdo = getDB();

try {
    $result = [];
    $result['timestamp'] = date('Y-m-d H:i:s');

    // Stats counts
    $result['stats'] = [
        'teachers'   => (int)$pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn(),
        'students'   => (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn(),
        'subjects'   => (int)$pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn(),
        'classrooms' => (int)$pdo->query("SELECT COUNT(*) FROM classrooms")->fetchColumn(),
    ];

    // Attendance summary (Aggregated)
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

    // Detailed Attendance
    $attDetailed = $pdo->query("SELECT a.attend_date, s.first_name, s.last_name, s.student_code, a.status
        FROM attendance a
        LEFT JOIN students s ON a.student_id = s.id
        ORDER BY a.id DESC LIMIT 5");
    $result['attendance_details'] = $attDetailed->fetchAll(PDO::FETCH_ASSOC);

    // Detailed Grade List
    $gradeDetailed = $pdo->query("SELECT s.first_name, s.last_name, s.student_code, c.class_name, sub.subject_name, g.grade
        FROM grades g
        LEFT JOIN students s ON g.student_id = s.id
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN subjects sub ON g.subject_id = sub.id
        ORDER BY g.id DESC LIMIT 5");
    $result['grade_details'] = $gradeDetailed->fetchAll(PDO::FETCH_ASSOC);

    // Clean any prior output and send JSON
    ob_clean();
    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => $e->getMessage()
    ]);
}
?>
