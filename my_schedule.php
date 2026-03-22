<?php
require_once __DIR__ . '/includes/session_check.php';
requireRole(['teacher', 'student']);
$role = getUserRole();
$page_title = ($role === 'teacher' ? 'ตารางสอนของฉัน' : 'ตารางเรียนของฉัน') . ' - โรงเรียนสาธิตวิทยา';
$current_page = 'my_schedule';

require_once __DIR__ . '/includes/layout_header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-calendar-week me-2"></i><?php echo $role === 'teacher' ? 'ตารางสอนของฉัน' : 'ตารางเรียนของฉัน'; ?></h2>
            <p class="text-muted small">แสดงคาบเรียนทั้งหมดในสัปดาห์ปัจจุบัน</p>
        </div>
        <div>
            <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> พิมพ์ตาราง
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 timetable-grid">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center bg-white" style="width: 120px;">เวลา / วัน</th>
                            <th class="text-center">จันทร์ (Mon)</th>
                            <th class="text-center">อังคาร (Tue)</th>
                            <th class="text-center">พุธ (Wed)</th>
                            <th class="text-center">พฤหัสบดี (Thu)</th>
                            <th class="text-center">ศุกร์ (Fri)</th>
                            <th class="text-center bg-light-warning">เสาร์ (Sat)</th>
                            <th class="text-center bg-light-danger">อาทิตย์ (Sun)</th>
                        </tr>
                    </thead>
                    <tbody id="timetableBody">
                        <!-- Periods will be injected here -->
                        <tr><td colspan="8" class="text-center py-5">กำลังโหลดข้อมูล...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.timetable-grid th { font-size: 0.85rem; vertical-align: middle; padding: 12px; }
.timetable-grid td { height: 100px; vertical-align: top; padding: 6px; border-color: #eee !important; min-width: 140px; }
.slot-card {
    font-size: 0.75rem;
    padding: 10px;
    border-radius: 8px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-left: 4px solid #0d6efd;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.2s;
    height: 100%;
}
.slot-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-color: #0d6efd; }
.bg-light-warning { background-color: #fffbeb !important; }
.bg-light-danger { background-color: #fef2f2 !important; }
.truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
@media print {
    .app-sidebar, .app-header, .btn { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    .card { border: 1px solid #ddd !important; box-shadow: none !important; }
}
</style>

<?php require_once __DIR__ . '/includes/layout_footer.php'; ?>

<script>
/**
 * Personal Timetable Logic
 */
let timetablePeriods = [];
let mySchedulesData = [];

$(document).ready(function() {
    initPersonalTimetable();
});

async function initPersonalTimetable() {
    const tableBody = $('#timetableBody');
    try {
        // Ensure BASE_URL is defined (it should be from layout_footer.php)
        const apiBase = (typeof BASE_URL !== 'undefined') ? BASE_URL : '/schoolai';
        
        // Load Periods
        const resPeriods = await $.get(apiBase + '/api/schedules.php?periods=1');
        timetablePeriods = resPeriods;
        
        // Load My Schedule
        const resMy = await $.get(apiBase + '/api/schedules.php?my=1');
        mySchedulesData = resMy;

        renderPersonalGrid();
    } catch (e) {
        console.error('Failed to load timetable:', e);
        tableBody.html('<tr><td colspan="8" class="text-center py-5 text-danger">ไม่สามารถโหลดข้อมูลได้ หรือเกิดข้อผิดพลาดในการเชื่อมต่อ</td></tr>');
    }
}

function renderPersonalGrid() {
    const tableBody = $('#timetableBody');
    if (timetablePeriods.length === 0) {
        tableBody.html('<tr><td colspan="8" class="text-center py-5">ไม่มีข้อมูลคาบเรียน</td></tr>');
        return;
    }

    let html = '';
    timetablePeriods.forEach(p => {
        html += `<tr data-period-id="${p.id}">
            <td class="text-center align-middle bg-light">
                <div class="fw-bold">คาบที่ ${p.period_no}</div>
                <div class="text-muted small">${p.start_time.substring(0,5)} - ${p.end_time.substring(0,5)}</div>
            </td>
            <td data-day="Monday"></td>
            <td data-day="Tuesday"></td>
            <td data-day="Wednesday"></td>
            <td data-day="Thursday"></td>
            <td data-day="Friday"></td>
            <td data-day="Saturday" class="bg-light-warning"></td>
            <td data-day="Sunday" class="bg-light-danger"></td>
        </tr>`;
    });
    tableBody.html(html);

    // Plot data
    if (Array.isArray(mySchedulesData)) {
        mySchedulesData.forEach(s => {
            const target = tableBody.find(`tr[data-period-id="${s.period_id}"] td[data-day="${s.day_of_week}"]`);
            if (target.length) {
                const roleData = '<?php echo $role; ?>' === 'teacher' ? s.class_name : s.teacher_name;
                
                target.html(`
                    <div class="slot-card animate-fade-in shadow-sm">
                        <div class="fw-bold text-primary mb-1">${s.subject_code}</div>
                        <div class="fw-bold mb-1 truncate" title="${s.subject_name}">${s.subject_name}</div>
                        <div class="text-muted small mb-1"><i class="bi bi-geo-alt me-1"></i>${s.room_name}</div>
                        <div class="text-muted small"><i class="bi bi-person me-1"></i>${roleData}</div>
                    </div>
                `);
            }
        });
    }
}
</script>
