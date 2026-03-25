<?php
require_once __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();

header('Content-Type: application/json');
$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$role = getUserRole();
$refId = getRefId();

try {
    if ($method === 'GET') {
        // Fetch school periods
        if (isset($_GET['periods'])) {
            $level = $_GET['level'] ?? '';
            $class_id = $_GET['class_id'] ?? '';
            
            // Auto-detect level if not provided
            if (!$level && !$class_id) {
                if ($role === 'student') {
                    $stmtS = $pdo->prepare("SELECT c.class_name FROM students s JOIN classes c ON s.class_id = c.id WHERE s.id = ?");
                    $stmtS->execute([$refId]);
                    $className = $stmtS->fetchColumn();
                    if ($className) $level = (preg_match('/ม\.[1-3]/u', $className)) ? 'middle' : 'high';
                } elseif ($role === 'teacher') {
                    $stmtT = $pdo->prepare("SELECT teaching_level FROM teachers WHERE id = ?");
                    $stmtT->execute([$refId]);
                    $level = $stmtT->fetchColumn() ?: 'middle';
                }
            }
            
            if (!$level && $class_id) {
                $stmtCls = $pdo->prepare("SELECT class_name FROM classes WHERE id = ?");
                $stmtCls->execute([$class_id]);
                $className = $stmtCls->fetchColumn();
                if ($className) {
                    $level = (preg_match('/ม\.[1-3]/u', $className)) ? 'middle' : 'high';
                }
            }
            
            $sql = "SELECT * FROM school_periods WHERE is_active = 1";
            $params = [];
            if ($level) {
                $sql .= " AND level = ?";
                $params[] = $level;
            }
            $sql .= " ORDER BY level, start_time";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
            exit;
        }

        // Personal schedule for teacher/student
        if (isset($_GET['my'])) {
            $where = ($role === 'teacher') ? "s.teacher_id = ?" : "s.class_id = (SELECT class_id FROM students WHERE id = ?)";
            $stmt = $pdo->prepare("SELECT s.*, sub.subject_name, sub.subject_code,
                CONCAT(t.first_name, ' ', t.last_name) AS teacher_name,
                c.class_name, cr.room_name, p.period_no, p.is_lunch
                FROM schedules s
                JOIN subjects sub ON s.subject_id = sub.id
                JOIN teachers t ON s.teacher_id = t.id
                JOIN classes c ON s.class_id = c.id
                JOIN classrooms cr ON s.classroom_id = cr.id
                LEFT JOIN school_periods p ON s.period_id = p.id
                WHERE $where
                ORDER BY FIELD(s.day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday'), s.start_time");
            $stmt->execute([$refId]);
            echo json_encode($stmt->fetchAll());
            exit;
        }

        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM schedules WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch());
        } else {
            // Filter by class or teacher if provided
            $where = "TRUE";
            $params = [];
            if (isset($_GET['class_id'])) { $where .= " AND s.class_id = ?"; $params[] = $_GET['class_id']; }
            if (isset($_GET['teacher_id'])) { $where .= " AND s.teacher_id = ?"; $params[] = $_GET['teacher_id']; }

            $stmt = $pdo->prepare("SELECT s.*, sub.subject_name, sub.subject_code,
                CONCAT(t.first_name, ' ', t.last_name) AS teacher_name,
                c.class_name, cr.room_name, p.period_no, p.is_lunch
                FROM schedules s
                JOIN subjects sub ON s.subject_id = sub.id
                JOIN teachers t ON s.teacher_id = t.id
                JOIN classes c ON s.class_id = c.id
                JOIN classrooms cr ON s.classroom_id = cr.id
                LEFT JOIN school_periods p ON s.period_id = p.id
                WHERE $where
                ORDER BY FIELD(s.day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday'), s.start_time");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        }
    } 
    elseif ($method === 'POST' || $method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? 0;
        
        // Conflict Detection (Robust overlap check)
        $sTime = $data['start_time'];
        $eTime = $data['end_time'];
        $day = $data['day_of_week'];

        // 1. Teacher conflict
        $stmtConflict = $pdo->prepare("SELECT COUNT(*) FROM schedules WHERE teacher_id = ? AND day_of_week = ? AND id != ? AND NOT (end_time <= ? OR start_time >= ?)");
        $stmtConflict->execute([$data['teacher_id'], $day, $id, $sTime, $eTime]);
        if ($stmtConflict->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'ครูผู้สอนท่านนี้มีตารางสอนอื่นทับซ้อนในช่วงเวลาดังกล่าว']);
            exit;
        }

        // 2. Classroom conflict
        $stmtRoomConflict = $pdo->prepare("SELECT COUNT(*) FROM schedules WHERE classroom_id = ? AND day_of_week = ? AND id != ? AND NOT (end_time <= ? OR start_time >= ?)");
        $stmtRoomConflict->execute([$data['classroom_id'], $day, $id, $sTime, $eTime]);
        if ($stmtRoomConflict->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'ห้องเรียนนี้ไม่ว่างในวันและเวลาดังกล่าว']);
            exit;
        }

        // 3. Class conflict
        $stmtClassConflict = $pdo->prepare("SELECT COUNT(*) FROM schedules WHERE class_id = ? AND day_of_week = ? AND id != ? AND NOT (end_time <= ? OR start_time >= ?)");
        $stmtClassConflict->execute([$data['class_id'], $day, $id, $sTime, $eTime]);
        if ($stmtClassConflict->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'ชั้นเรียนนี้มีตารางเรียนอื่นทับซ้อนในช่วงเวลาดังกล่าว']);
            exit;
        }

        if ($method === 'POST') {
            $stmt = $pdo->prepare("INSERT INTO schedules (subject_id, teacher_id, class_id, classroom_id, day_of_week, period_id, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['subject_id'], $data['teacher_id'], $data['class_id'], $data['classroom_id'], $day, $data['period_id'] ?? null, $sTime, $eTime]);
            logActivity('schedule_create', "เพิ่มตารางเรียนสำหรับ วัน" . $day);
            echo json_encode(['success' => true, 'message' => 'เพิ่มตารางเรียนสำเร็จ']);
        } else {
            $stmt = $pdo->prepare("UPDATE schedules SET subject_id=?, teacher_id=?, class_id=?, classroom_id=?, day_of_week=?, period_id=?, start_time=?, end_time=? WHERE id=?");
            $stmt->execute([$data['subject_id'], $data['teacher_id'], $data['class_id'], $data['classroom_id'], $day, $data['period_id'] ?? null, $sTime, $eTime, $id]);
            logActivity('schedule_update', "อัปเดตตารางเรียน ID: $id");
            echo json_encode(['success' => true, 'message' => 'แก้ไขตารางเรียนสำเร็จ']);
        }
    }
    elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
        $stmt->execute([$id]);
        logActivity('schedule_delete', "ลบตารางเรียน ID: $id");
        echo json_encode(['success' => true, 'message' => 'ลบตารางเรียนสำเร็จ']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
