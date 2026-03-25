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
<div class="page-header d-flex justify-content-between align-items-center">
    <h1><i class="bi bi-speedometer2"></i> แดชบอร์ดผู้บริหาร</h1>
    <div class="last-updated-badge" id="lastUpdatedContainer" style="display: none;">
        <span class="badge bg-light text-dark border">
            <i class="bi bi-clock-history me-1"></i> อัปเดตล่าสุด: <span id="lastUpdatedTime">-</span>
        </span>
    </div>
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

<div class="row g-4" id="dashboardStats">
    <div class="col-lg-6">
        <div class="card animate-fade-in animate-delay-2 h-100">
            <div class="card-header"><i class="bi bi-pie-chart-fill"></i> สถิติการมาเรียน (สัดส่วน)</div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card animate-fade-in animate-delay-3 h-100">
            <div class="card-header"><i class="bi bi-bar-chart-fill"></i> ภาพรวมผลการเรียน (Grade Distribution)</div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="gradeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <!-- Dashboard Attendance List -->
    <div class="col-lg-6">
        <div class="card animate-fade-in animate-delay-4 shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-people-fill text-primary me-2"></i> รายชื่อเช็คชื่อล่าสุด (อ้างอิงฐานข้อมูล)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 400px; min-height: 200px;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr class="small text-uppercase fw-bold">
                                <th class="ps-4">ชื่อ-นามสกุล</th>
                                <th>วันที่</th>
                                <th class="text-center">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody id="dashboardAttendanceList">
                            <tr><td colspan="3" class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>กำลังดึงข้อมูลจากระบบ...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Grade List -->
    <div class="col-lg-6">
        <div class="card animate-fade-in animate-delay-5 shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-journal-text text-success me-2"></i> รายชื่อผลการเรียนล่าสุด (อ้างอิงฐานข้อมูล)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 400px; min-height: 200px;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr class="small text-uppercase fw-bold">
                                <th class="ps-4">ชื่อ-นามสกุล</th>
                                <th>รายวิชา</th>
                                <th class="text-center">เกรด</th>
                            </tr>
                        </thead>
                        <tbody id="dashboardGradeList">
                            <tr><td colspan="3" class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>กำลังดึงข้อมูลจากระบบ...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_footer.php'; ?>
