<?php
// ============================================
// Database Setup Script - Run Once
// ============================================
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'school_db';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");

    // ---- TABLES ----

    $pdo->exec("CREATE TABLE IF NOT EXISTS `teachers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `teacher_code` VARCHAR(20) NOT NULL UNIQUE,
        `first_name` VARCHAR(100) NOT NULL,
        `last_name` VARCHAR(100) NOT NULL,
        `phone` VARCHAR(20) DEFAULT NULL,
        `department` VARCHAR(100) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `classes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `class_code` VARCHAR(20) NOT NULL UNIQUE,
        `class_name` VARCHAR(100) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `students` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_code` VARCHAR(20) NOT NULL UNIQUE,
        `first_name` VARCHAR(100) NOT NULL,
        `last_name` VARCHAR(100) NOT NULL,
        `birthdate` DATE DEFAULT NULL,
        `class_id` INT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `subjects` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `subject_code` VARCHAR(20) NOT NULL UNIQUE,
        `subject_name` VARCHAR(150) NOT NULL,
        `credits` INT NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `classrooms` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `room_code` VARCHAR(20) NOT NULL UNIQUE,
        `room_name` VARCHAR(100) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `password_hash` VARCHAR(255) NOT NULL,
        `role` ENUM('admin','teacher','student') NOT NULL DEFAULT 'student',
        `ref_id` INT DEFAULT NULL COMMENT 'References teacher.id or student.id',
        `display_name` VARCHAR(200) DEFAULT NULL,
        `last_login` DATETIME DEFAULT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Ensure missing columns exist in users table (for existing installations)
    $cols = $pdo->query("DESCRIBE `users`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('last_login', $cols)) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `last_login` DATETIME DEFAULT NULL AFTER `display_name` ");
    }
    if (!in_array('is_active', $cols)) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `is_active` TINYINT(1) DEFAULT 1 AFTER `last_login` ");
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS `login_attempts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `ip_address` VARCHAR(45) NOT NULL,
        `username` VARCHAR(50) NOT NULL,
        `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ip_time (ip_address, attempted_at),
        INDEX idx_user_time (username, attempted_at)
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `school_periods` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `period_no` INT NOT NULL,
        `start_time` TIME NOT NULL,
        `end_time` TIME NOT NULL,
        `is_active` TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `schedules` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `subject_id` INT NOT NULL,
        `teacher_id` INT NOT NULL,
        `class_id` INT NOT NULL,
        `classroom_id` INT NOT NULL,
        `day_of_week` ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
        `period_id` INT DEFAULT NULL,
        `start_time` TIME NOT NULL,
        `end_time` TIME NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`period_id`) REFERENCES `school_periods`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`classroom_id`) REFERENCES `classrooms`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `grades` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_id` INT NOT NULL,
        `subject_id` INT NOT NULL,
        `score` DECIMAL(5,2) DEFAULT NULL,
        `grade` VARCHAR(5) DEFAULT NULL,
        `semester` VARCHAR(20) DEFAULT '1',
        `academic_year` VARCHAR(10) DEFAULT '2568',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_grade` (`student_id`, `subject_id`, `semester`, `academic_year`)
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `attendance` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `schedule_id` INT NOT NULL,
        `student_id` INT NOT NULL,
        `attend_date` DATE NOT NULL,
        `status` ENUM('present','absent','late','leave') NOT NULL DEFAULT 'present',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`schedule_id`) REFERENCES `schedules`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_attendance` (`schedule_id`, `student_id`, `attend_date`)
    ) ENGINE=InnoDB");

    // ---- SAMPLE DATA ----

    // Admin user (password: admin123)
    $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO `users` (`username`, `password_hash`, `role`, `display_name`) VALUES ('admin', '$adminHash', 'admin', 'ผู้ดูแลระบบ')");

    // Classes
    $classes = [
        ['M1-1','ม.1/1'],['M1-2','ม.1/2'],
        ['M2-1','ม.2/1'],['M2-2','ม.2/2'],
        ['M3-1','ม.3/1'],['M3-2','ม.3/2'],
        ['M4-1','ม.4/1'],['M4-2','ม.4/2'],
        ['M5-1','ม.5/1'],['M6-1','ม.6/1']
    ];
    $stmtClass = $pdo->prepare("INSERT IGNORE INTO `classes` (`class_code`,`class_name`) VALUES (?,?)");
    foreach ($classes as $c) $stmtClass->execute($c);

    // Teachers
    $teachers = [
        ['T001','สมชาย','ใจดี','0812345671','คณิตศาสตร์'],
        ['T002','สมหญิง','รักเรียน','0812345672','วิทยาศาสตร์'],
        ['T003','ประเสริฐ','มั่นคง','0812345673','ภาษาไทย'],
        ['T004','วิไล','สว่างจิต','0812345674','ภาษาอังกฤษ'],
        ['T005','กิตติ','พัฒนา','0812345675','สังคมศึกษา']
    ];
    $stmtTeacher = $pdo->prepare("INSERT IGNORE INTO `teachers` (`teacher_code`,`first_name`,`last_name`,`phone`,`department`) VALUES (?,?,?,?,?)");
    foreach ($teachers as $t) $stmtTeacher->execute($t);

    // Create teacher user accounts (password: teacher123)
    $teacherHash = password_hash('teacher123', PASSWORD_DEFAULT);
    $stmtTUser = $pdo->prepare("INSERT IGNORE INTO `users` (`username`,`password_hash`,`role`,`ref_id`,`display_name`) VALUES (?,?,'teacher',?,?)");
    foreach ($teachers as $i => $t) {
        $tid = $i + 1;
        $stmtTUser->execute([$t[0], $teacherHash, $tid, $t[1].' '.$t[2]]);
    }

    // Students
    $students = [
        ['S001','กานต์','สุขใจ','2012-05-15',1],
        ['S002','ปิยะ','รุ่งเรือง','2012-08-22',1],
        ['S003','นภา','ชัยชนะ','2012-03-10',2],
        ['S004','ธนา','ศรีสุข','2011-11-05',3],
        ['S005','พิมพ์','ดวงแก้ว','2011-07-18',3],
        ['S006','อรุณ','ทองดี','2011-01-25',4],
        ['S007','สุรีย์','แสงเดือน','2010-09-12',5],
        ['S008','ชาญ','ฤทธิ์เดช','2010-04-30',6],
        ['S009','มณี','ประเสริฐ','2009-12-08',7],
        ['S010','วรรณ','สมบูรณ์','2009-06-20',8]
    ];
    $stmtStudent = $pdo->prepare("INSERT IGNORE INTO `students` (`student_code`,`first_name`,`last_name`,`birthdate`,`class_id`) VALUES (?,?,?,?,?)");
    foreach ($students as $s) $stmtStudent->execute($s);

    // Create student user accounts (password: student123)
    $studentHash = password_hash('student123', PASSWORD_DEFAULT);
    $stmtSUser = $pdo->prepare("INSERT IGNORE INTO `users` (`username`,`password_hash`,`role`,`ref_id`,`display_name`) VALUES (?,?,'student',?,?)");
    foreach ($students as $i => $s) {
        $sid = $i + 1;
        $stmtSUser->execute([$s[0], $studentHash, $sid, $s[1].' '.$s[2]]);
    }

    // Subjects
    $subjects = [
        ['SUB001','คณิตศาสตร์พื้นฐาน',2],
        ['SUB002','วิทยาศาสตร์พื้นฐาน',2],
        ['SUB003','ภาษาไทย',1],
        ['SUB004','ภาษาอังกฤษ',2],
        ['SUB005','สังคมศึกษา',1],
        ['SUB006','พลศึกษา',1],
        ['SUB007','ศิลปะ',1],
        ['SUB008','การงานอาชีพ',1],
        ['SUB009','คณิตศาสตร์เพิ่มเติม',2],
        ['SUB010','วิทยาศาสตร์เพิ่มเติม',2]
    ];
    $stmtSubject = $pdo->prepare("INSERT IGNORE INTO `subjects` (`subject_code`,`subject_name`,`credits`) VALUES (?,?,?)");
    foreach ($subjects as $s) $stmtSubject->execute($s);

    // Classrooms
    $classrooms = [
        ['R101','ห้อง 101'],['R102','ห้อง 102'],
        ['R201','ห้อง 201'],['R202','ห้อง 202'],
        ['R301','ห้อง 301'],['R302','ห้อง 302'],
        ['SCI1','วิทยาศาสตร์ 1'],['SCI2','วิทยาศาสตร์ 2'],
        ['COM1','คอมพิวเตอร์ 1'],['ART1','ห้องศิลปะ']
    ];
    $stmtRoom = $pdo->prepare("INSERT IGNORE INTO `classrooms` (`room_code`,`room_name`) VALUES (?,?)");
    foreach ($classrooms as $r) $stmtRoom->execute($r);

    // Sample schedules
    $schedules = [
        [1,1,1,1,'Monday','08:30:00','09:20:00'],
        [2,2,1,7,'Monday','09:20:00','10:10:00'],
        [3,3,2,2,'Monday','08:30:00','09:20:00'],
        [4,4,2,3,'Tuesday','10:30:00','11:20:00'],
        [5,5,3,4,'Tuesday','08:30:00','09:20:00'],
        [1,1,3,1,'Wednesday','08:30:00','09:20:00'],
        [2,2,4,7,'Wednesday','09:20:00','10:10:00'],
        [3,3,1,2,'Thursday','08:30:00','09:20:00'],
        [4,4,5,3,'Thursday','10:30:00','11:20:00'],
        [5,5,6,4,'Friday','08:30:00','09:20:00']
    ];
    $stmtSch = $pdo->prepare("INSERT IGNORE INTO `schedules` (`subject_id`,`teacher_id`,`class_id`,`classroom_id`,`day_of_week`,`start_time`,`end_time`) VALUES (?,?,?,?,?,?,?)");
    foreach ($schedules as $s) $stmtSch->execute($s);

    // Sample grades
    $gradeData = [
        [1,1,85,'4'],[2,1,72,'3'],[3,2,65,'2'],[4,3,55,'1'],[5,3,45,'0'],
        [6,4,90,'4'],[7,5,78,'3'],[8,1,60,'2'],[9,2,88,'4'],[10,4,50,'1']
    ];
    $stmtGrade = $pdo->prepare("INSERT IGNORE INTO `grades` (`student_id`,`subject_id`,`score`,`grade`) VALUES (?,?,?,?)");
    foreach ($gradeData as $g) $stmtGrade->execute($g);

    // Sample attendance
    $attendData = [
        [1,1,'2026-03-17','present'],[1,2,'2026-03-17','present'],
        [2,1,'2026-03-17','absent'],[2,2,'2026-03-17','late'],
        [3,3,'2026-03-17','present'],[4,4,'2026-03-18','leave'],
        [5,5,'2026-03-18','present'],[6,1,'2026-03-19','present'],
        [7,7,'2026-03-19','absent'],[8,8,'2026-03-20','present'],
        [1,1,'2026-03-18','present'],[1,2,'2026-03-18','late'],
        [2,1,'2026-03-18','present'],[3,3,'2026-03-18','present'],
        [4,5,'2026-03-18','present'],[5,5,'2026-03-19','absent']
    ];
    $stmtAtt = $pdo->prepare("INSERT IGNORE INTO `attendance` (`schedule_id`,`student_id`,`attend_date`,`status`) VALUES (?,?,?,?)");
    foreach ($attendData as $a) $stmtAtt->execute($a);

    // Create roles table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `roles` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `role_name` VARCHAR(100) NOT NULL,
        `role_key` VARCHAR(50) NOT NULL UNIQUE,
        `permissions` TEXT COMMENT 'JSON permissions'
    ) ENGINE=InnoDB");

    // Create academic_years table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `academic_years` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `year` INT NOT NULL,
        `term` VARCHAR(50) NOT NULL,
        `is_current` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Migrate academic_years schema if it already exists with old columns
    $colsYear = $pdo->query("DESCRIBE `academic_years`")->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($colsYear, 'Field');
    
    if (in_array('is_active', $columnNames) && !in_array('is_current', $columnNames)) {
        $pdo->exec("ALTER TABLE `academic_years` CHANGE `is_active` `is_current` TINYINT(1) DEFAULT 0");
    }
    
    // Check term column type - change to VARCHAR if it was INT
    foreach ($colsYear as $col) {
        if ($col['Field'] === 'term' && strpos($col['Type'], 'int') !== false) {
            $pdo->exec("ALTER TABLE `academic_years` MODIFY `term` VARCHAR(50) NOT NULL");
        }
    }

    // Create subject_groups table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `subject_groups` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL UNIQUE,
        `description` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Create system_settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `system_settings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `setting_key` VARCHAR(100) NOT NULL UNIQUE,
        `setting_value` TEXT,
        `description` VARCHAR(255)
    ) ENGINE=InnoDB");

    // Create activity_logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `activity_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT,
        `action` VARCHAR(100),
        `description` TEXT,
        `ip_address` VARCHAR(45),
        `details` JSON,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB");

    // Create announcements table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `announcements` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `content` TEXT,
        `target_role` VARCHAR(50) DEFAULT 'all',
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Add role_id and is_suspended to users table if they don't exist
    $cols = $pdo->query("DESCRIBE `users`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('role_id', $cols)) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `role_id` INT AFTER `role` ");
    }
    if (!in_array('is_suspended', $cols)) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `is_suspended` TINYINT(1) DEFAULT 0 AFTER `is_active` ");
    }

    // Migrate schedules table
    $colsSch = $pdo->query("DESCRIBE `schedules`")->fetchAll(PDO::FETCH_ASSOC);
    $schColsList = array_column($colsSch, 'Field');
    
    // Check if ENUM needs expansion
    foreach ($colsSch as $col) {
        if ($col['Field'] === 'day_of_week' && strpos($col['Type'], 'Saturday') === false) {
            $pdo->exec("ALTER TABLE `schedules` MODIFY `day_of_week` ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL");
        }
    }

    if (!in_array('period_id', $schColsList)) {
        $pdo->exec("ALTER TABLE `schedules` ADD COLUMN `period_id` INT AFTER `day_of_week` ");
    }

    // Populate initial roles
    $roles = [
        ['Admin', 'admin'],
        ['นายทะเบียน (Registrar)', 'registrar'],
        ['ฝ่ายปกครอง (Discipline)', 'discipline'],
        ['ครู (Teacher)', 'teacher'],
        ['นักเรียน (Student)', 'student']
    ];
    $stmtRole = $pdo->prepare("INSERT IGNORE INTO `roles` (role_name, role_key) VALUES (?, ?)");
    foreach ($roles as $r) $stmtRole->execute($r);

    // Link users to roles
    $pdo->exec("UPDATE `users` SET `role_id` = (SELECT id FROM roles WHERE role_key = 'admin') WHERE `role` = 'admin'");
    $pdo->exec("UPDATE `users` SET `role_id` = (SELECT id FROM roles WHERE role_key = 'teacher') WHERE `role` = 'teacher'");
    $pdo->exec("UPDATE `users` SET `role_id` = (SELECT id FROM roles WHERE role_key = 'student') WHERE `role` = 'student'");

    // Initial settings
    $settingsData = [
        ['school_name', 'โรงเรียนสาธิตวิทยา', 'ชื่อโรงเรียน'],
        ['maintenance_mode', '0', 'โหมดปิดปรับปรุง'],
        ['allow_login', '1', 'อนุญาตให้เข้าสู่ระบบ']
    ];
    $stmtSet = $pdo->prepare("INSERT IGNORE INTO `system_settings` (setting_key, setting_value, description) VALUES (?, ?, ?)");
    foreach ($settingsData as $s) $stmtSet->execute($s);

    // Initial Subject Groups
    $groupsData = [
        ['วิทยาศาสตร์และเทคโนโลยี', 'เน้นการทดลองและการเขียนโปรแกรม'],
        ['คณิตศาสตร์', 'เน้นทักษะการคำนวณและโลจิก'],
        ['ภาษาไทย', 'ทักษะการสื่อสารภาษาหลัก'],
        ['ภาษาต่างประเทศ', 'เน้นภาษาอังกฤษและภาษาที่สอง'],
        ['สังคมศึกษา ศาสนา และวัฒนธรรม', 'ความเข้าใจในสังคมและพลเมือง']
    ];
    $stmtGrp = $pdo->prepare("INSERT IGNORE INTO `subject_groups` (name, description) VALUES (?, ?)");
    foreach ($groupsData as $g) $stmtGrp->execute($g);

    // Initial School Periods (8 periods common in Thai schools)
    $periodsData = [
        [1, '08:30:00', '09:20:00'],
        [2, '09:20:00', '10:10:00'],
        [3, '10:10:00', '11:00:00'],
        [4, '11:00:00', '11:50:00'],
        [5, '12:50:00', '13:40:00'],
        [6, '13:40:00', '14:30:00'],
        [7, '14:30:00', '15:20:00'],
        [8, '15:20:00', '16:10:00']
    ];
    $stmtPer = $pdo->prepare("INSERT IGNORE INTO `school_periods` (period_no, start_time, end_time) VALUES (?, ?, ?)");
    foreach ($periodsData as $p) $stmtPer->execute($p);

    // Initial Academic Year
    $pdo->exec("INSERT IGNORE INTO `academic_years` (year, term, is_current) VALUES (2567, '1', 1)");

    echo "<div style='font-family:sans-serif;max-width:600px;margin:50px auto;padding:30px;background:#d4edda;border-radius:12px;text-align:center;'>";
    echo "<h2 style='color:#155724;'>✅ ติดตั้งฐานข้อมูลสำเร็จ! (รวมระบบ Super Admin)</h2>";
    echo "<p>สร้างฐานข้อมูล <strong>school_db</strong> และตารางระบบบริหารจัดการระดับสูงเรียบร้อยแล้ว</p>";
    echo "<p>ข้อมูลตัวอย่างถูกเพิ่มเรียบร้อยแล้ว</p>";
    echo "<hr>";
    echo "<p><strong>Admin Login:</strong> admin / admin123</p>";
    echo "<p><strong>Teacher Login:</strong> T001-T005 / teacher123</p>";
    echo "<p><strong>Student Login:</strong> S001-S010 / student123</p>";
    echo "<hr>";
    echo "<a href='login.php' style='display:inline-block;padding:12px 30px;background:#F5E7C6;color:#333;border-radius:8px;text-decoration:none;font-weight:bold;'>ไปหน้า Login →</a>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='font-family:sans-serif;max-width:600px;margin:50px auto;padding:30px;background:#f8d7da;border-radius:12px;'>";
    echo "<h2 style='color:#721c24;'>❌ เกิดข้อผิดพลาด</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
