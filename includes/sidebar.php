<?php
$role = getUserRole();
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="bi bi-mortarboard-fill"></i>
            <span class="sidebar-logo-text">SchoolAI</span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <ul class="sidebar-menu">
            <!-- Dashboard -->
            <li class="menu-item <?php echo $current_page === 'index' ? 'active' : ''; ?>">
                <a href="<?php echo $base_url; ?>/index.php">
                    <i class="bi bi-speedometer2"></i>
                    <span>แดชบอร์ด</span>
                </a>
            </li>

            <!-- Super Admin Section -->
            <?php if ($role === 'admin'): ?>
            <li class="menu-divider" style="padding:15px 15px 5px;font-size:0.75rem;color:#64748B;font-weight:bold;text-transform:uppercase;">Super Admin</li>
            
            <li class="menu-item has-submenu <?php echo in_array($current_page, ['admin_users','admin_roles','admin_impersonate']) ? 'open' : ''; ?>">
                <a href="#" class="submenu-toggle">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>จัดการสิทธิ์ & ผู้ใช้</span>
                    <i class="bi bi-chevron-down submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="<?php echo $current_page === 'admin_users' ? 'active' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/admin/users.php"><i class="bi bi-people-fill"></i> ผู้ใช้งานทั้งหมด</a>
                    </li>
                    <li class="<?php echo $current_page === 'admin_roles' ? 'active' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/admin/roles.php"><i class="bi bi-key-fill"></i> กำหนดสิทธิ์ (RBAC)</a>
                    </li>
                </ul>
            </li>

            <li class="menu-item has-submenu <?php echo in_array($current_page, ['admin_academic','admin_curriculum']) ? 'open' : ''; ?>">
                <a href="#" class="submenu-toggle">
                    <i class="bi bi-gear-wide-connected"></i>
                    <span>โครงสร้างข้อมูลหลัก</span>
                    <i class="bi bi-chevron-down submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="<?php echo $current_page === 'admin_academic' ? 'active' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/admin/academic_years.php"><i class="bi bi-calendar-event"></i> ปีการศึกษา/เทอม</a>
                    </li>
                    <li class="<?php echo $current_page === 'admin_curriculum' ? 'active' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/admin/curriculum.php"><i class="bi bi-journal-check"></i> หลักสูตร/กลุ่มสาระ</a>
                    </li>
                </ul>
            </li>

            <li class="menu-item has-submenu <?php echo in_array($current_page, ['admin_logs','admin_sessions','admin_history']) ? 'open' : ''; ?>">
                <a href="#" class="submenu-toggle">
                    <i class="bi bi-fingerprint"></i>
                    <span>ความปลอดภัย & Log</span>
                    <i class="bi bi-chevron-down submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="<?php echo $current_page === 'admin_logs' ? 'active' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/admin/audit_logs.php"><i class="bi bi-list-columns"></i> Activity Logs</a>
                    </li>
                    <li class="<?php echo $current_page === 'admin_sessions' ? 'active' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/admin/sessions.php"><i class="bi bi-broadcast"></i> เซสชั่นที่ออนไลน์</a>
                    </li>
                </ul>
            </li>

            <li class="menu-item <?php echo $current_page === 'admin_announcements' ? 'active' : ''; ?>">
                <a href="<?php echo $base_url; ?>/admin/announcements.php">
                    <i class="bi bi-megaphone"></i>
                    <span>ประกาศข่าวสาร</span>
                </a>
            </li>

            <li class="menu-item <?php echo $current_page === 'admin_settings' ? 'active' : ''; ?>">
                <a href="<?php echo $base_url; ?>/admin/settings.php">
                    <i class="bi bi-sliders"></i>
                    <span>ตั้งค่าระบบ (CMS)</span>
                </a>
            </li>
            <div style="margin-bottom: 20px;"></div>
            <?php endif; ?>
            
            <?php if ($role === 'admin'): ?>
            <li class="menu-divider" style="padding:15px 15px 5px;font-size:0.75rem;color:#64748B;font-weight:bold;text-transform:uppercase;">จัดการข้อมูลโรงเรียน</li>
            
            <li class="menu-item has-submenu <?php echo in_array($current_page, ['teachers','students','subjects','classes','classrooms']) ? 'open' : ''; ?>">
                <a href="#" class="submenu-toggle">
                    <i class="bi bi-database-fill"></i>
                    <span>จัดการข้อมูลตั้งต้น</span>
                    <i class="bi bi-chevron-down submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="<?php echo $current_page === 'teachers' ? 'active' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/teachers.php"><i class="bi bi-person-badge"></i> ข้อมูลครู</a>
                    </li>
                    <li class="<?php echo $current_page === 'students' ? 'active' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/students.php"><i class="bi bi-people"></i> ข้อมูลนักเรียน</a>
                    </li>
                    <li class="<?php echo $current_page === 'subjects' ? 'active' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/subjects.php"><i class="bi bi-book"></i> ข้อมูลรายวิชา</a>
                    </li>
                    <li class="<?php echo $current_page === 'classes' ? 'active' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/classes.php"><i class="bi bi-diagram-3"></i> ข้อมูลชั้นเรียน</a>
                    </li>
                    <li class="<?php echo $current_page === 'classrooms' ? 'active' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/classrooms.php"><i class="bi bi-door-open"></i> ข้อมูลห้องเรียน</a>
                    </li>
                </ul>
            </li>

            <li class="menu-item <?php echo $current_page === 'admin_schedules' ? 'active' : ''; ?>">
                <a href="<?php echo $base_url; ?>/admin/schedules.php">
                    <i class="bi bi-calendar-week"></i>
                    <span>จัดการตารางเรียน</span>
                </a>
            </li>

            <li class="menu-item <?php echo $current_page === 'grades' ? 'active' : ''; ?>">
                <a href="<?php echo $base_url; ?>/grades.php">
                    <i class="bi bi-clipboard-data"></i>
                    <span>บันทึกผลการเรียน</span>
                </a>
            </li>

            <li class="menu-item <?php echo $current_page === 'attendance' ? 'active' : ''; ?>">
                <a href="<?php echo $base_url; ?>/attendance.php">
                    <i class="bi bi-check2-square"></i>
                    <span>เช็คชื่อนักเรียน</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($role === 'teacher'): ?>
            <!-- Teacher menus -->
            <li class="menu-item <?php echo $current_page === 'my_schedule' ? 'active' : ''; ?>">
                <a href="<?php echo $base_url; ?>/my_schedule.php">
                    <i class="bi bi-calendar-week"></i>
                    <span>ตารางสอนของฉัน</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page === 'attendance' ? 'active' : ''; ?>">
                <a href="<?php echo $base_url; ?>/attendance.php">
                    <i class="bi bi-check2-square"></i>
                    <span>เช็คชื่อนักเรียน</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page === 'grades' ? 'active' : ''; ?>">
                <a href="<?php echo $base_url; ?>/grades.php">
                    <i class="bi bi-clipboard-data"></i>
                    <span>บันทึกผลการเรียน</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($role === 'student'): ?>
            <!-- Student menus -->
            <li class="menu-item <?php echo $current_page === 'my_schedule' ? 'active' : ''; ?>">
                <a href="<?php echo $base_url; ?>/my_schedule.php">
                    <i class="bi bi-calendar-week"></i>
                    <span>ตารางเรียนของฉัน</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page === 'my_grades' ? 'active' : ''; ?>">
                <a href="<?php echo $base_url; ?>/my_grades.php">
                    <i class="bi bi-clipboard-data"></i>
                    <span>ผลการเรียนของฉัน</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>
<!-- Sidebar Overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
