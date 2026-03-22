<?php
require_once __DIR__ . '/includes/session_check.php';
requireLogin();
$page_title = 'แดชบอร์ด - โรงเรียนสาธิตวิทยา';
$role = getUserRole();
require_once __DIR__ . '/includes/layout_header.php';
?>

<?php if ($role === 'admin'): ?>
<!-- Admin Dashboard -->
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

<?php elseif ($role === 'teacher'): ?>
<!-- Teacher Dashboard -->
<div class="page-header">
    <h1><i class="bi bi-speedometer2"></i> แดชบอร์ดครูผู้สอน</h1>
</div>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-calendar-week"></i> ตารางสอนของฉัน</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table" id="myScheduleTable">
                <thead>
                    <tr><th>วัน</th><th>เวลา</th><th>วิชา</th><th>ครู</th><th>ห้อง</th><th>ชั้นเรียน</th></tr>
                </thead>
                <tbody>
                    <tr><td colspan="6" class="text-center py-4"><div class="loading-spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Student Dashboard -->
<div class="page-header">
    <h1><i class="bi bi-speedometer2"></i> แดชบอร์ดนักเรียน</h1>
</div>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-calendar-week"></i> ตารางเรียนของฉัน</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table" id="myScheduleTable">
                <thead>
                    <tr><th>วัน</th><th>เวลา</th><th>วิชา</th><th>ครู</th><th>ห้อง</th><th>ชั้นเรียน</th></tr>
                </thead>
                <tbody>
                    <tr><td colspan="6" class="text-center py-4"><div class="loading-spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/layout_footer.php'; ?>
