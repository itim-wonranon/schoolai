<?php
require_once __DIR__ . '/includes/session_check.php';
requireLogin();

$role = getUserRole();
// Restrict dashboard to admin only
if ($role !== 'admin') {
    header('Location: my_schedule.php');
    exit;
}

$page_title = 'แดชบอร์ด - โรงเรียนสาธิตวิทยา';
require_once __DIR__ . '/includes/layout_header.php';
?>

<!-- Admin Dashboard Stats -->
<div class="page-header">
    <h1><i class="bi bi-speedometer2"></i> แดชบอร์ดผู้บริหาร</h1>
</div>

<div class="row g-4 mb-4" id="dashboardStats">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-teachers animate-fade-in animate-delay-1">
            <div class="stat-icon"><i class="bi bi-person-badge"></i></div>
            <div class="stat-value" id="statTeachers">0</div>
            <div class="stat-label">ครูผู้สอน</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-students animate-fade-in animate-delay-2">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div class="stat-value" id="statStudents">0</div>
            <div class="stat-label">นักเรียน</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-subjects animate-fade-in animate-delay-3">
            <div class="stat-icon"><i class="bi bi-book"></i></div>
            <div class="stat-value" id="statSubjects">0</div>
            <div class="stat-label">รายวิชา</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-classrooms animate-fade-in animate-delay-4">
            <div class="stat-icon"><i class="bi bi-door-open"></i></div>
            <div class="stat-value" id="statClassrooms">0</div>
            <div class="stat-label">ห้องเรียน</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card animate-fade-in animate-delay-2">
            <div class="card-header"><i class="bi bi-pie-chart-fill"></i> สถิติการมาเรียน</div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card animate-fade-in animate-delay-3">
            <div class="card-header"><i class="bi bi-bar-chart-fill"></i> ภาพรวมผลการเรียน (Grade Distribution)</div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="gradeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_footer.php'; ?>
