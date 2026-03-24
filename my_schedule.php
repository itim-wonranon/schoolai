<?php
require_once __DIR__ . '/includes/session_check.php';
requireRole(['teacher', 'student']);
$role = getUserRole();
$page_title = ($role === 'teacher' ? 'ตารางสอนของฉัน' : 'ตารางเรียนของฉัน') . ' - โรงเรียนสาธิตวิทยา';
$current_page = 'my_schedule';

require_once __DIR__ . '/includes/layout_header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-calendar-week text-primary me-2"></i><?php echo $role === 'teacher' ? 'ตารางสอนของฉัน' : 'ตารางเรียนของฉัน'; ?></h2>
            <p class="text-muted mb-0">แสดงคาบเรียนทั้งหมดในสัปดาห์ปัจจุบัน (จันทร์ - ศุกร์)</p>
        </div>
        <div>
            <button class="btn btn-outline-dark fw-bold rounded-3 px-3 shadow-sm" onclick="window.print()">
                <i class="bi bi-printer me-2"></i>พิมพ์ตารางเรียน
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 animate-fade-in">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 timetable-grid">
                <thead>
                    <tr class="bg-light text-center">
                        <th style="width: 140px;" class="py-3 border-0">เวลา / คาบ</th>
                        <th class="py-3 border-0">จันทร์</th>
                        <th class="py-3 border-0">อังคาร</th>
                        <th class="py-3 border-0">พุธ</th>
                        <th class="py-3 border-0">พฤหัสบดี</th>
                        <th class="py-3 border-0">ศุกร์</th>
                    </tr>
                </thead>
                <tbody id="timetableBody">
                    <tr><td colspan="6" class="text-center py-5">กำลังโหลดข้อมูลตารางเรียน...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.timetable-grid th { font-size: 0.8rem; letter-spacing: 0.05em; font-weight: 700; color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; }
.timetable-grid td { height: 110px; vertical-align: top; padding: 10px; border: 1px solid #e2e8f0; background: #fff; min-width: 160px; }
.slot-card {
    font-size: 0.75rem;
    padding: 12px;
    border-radius: 12px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #3b82f6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    transition: all 0.2s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.slot-card:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(0,0,0,0.06); border-color: #3b82f6; }
.lunch-row { background-color: #fffbeb !important; height: 50px !important; }
.lunch-text { font-size: 0.8rem; font-weight: 800; color: #b45309; text-transform: uppercase; letter-spacing: 0.1em; }
.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>

<?php require_once __DIR__ . '/includes/layout_footer.php'; ?>

<script>
let timetablePeriods = [];

$(document).ready(function() {
    loadMyTimetable();
});

async function loadMyTimetable() {
    try {
        // Load Periods (API will auto-detect level based on role & class)
        const resPeriods = await $.get(BASE_URL + '/api/schedules.php?periods=1');
        timetablePeriods = resPeriods;
        
        // Load My Schedule Data
        const resMy = await $.get(BASE_URL + '/api/schedules.php?my=1');
        
        renderGrid(resMy);
    } catch (e) {
        console.error('Failed to load timetable:', e);
        $('#timetableBody').html('<tr><td colspan="6" class="text-center py-5 text-danger">ไม่สามารถโหลดข้อมูลได้ หรือคุณยังไม่มีชั้นเรียนสังกัด</td></tr>');
    }
}

function renderGrid(data) {
    const tableBody = $('#timetableBody');
    if (timetablePeriods.length === 0) {
        tableBody.html('<tr><td colspan="6" class="text-center py-5 text-muted">ไม่มีข้อมูลตารางเรียนสำหรับระดับชั้นของคุณ</td></tr>');
        return;
    }

    let html = '';
    timetablePeriods.forEach(p => {
        if (p.is_lunch == 1) {
            html += `
            <tr class="lunch-row">
                <td class="text-center align-middle py-0">
                    <span class="lunch-text"><i class="bi bi-cup-hot me-2"></i>พักกลางวัน</span>
                </td>
                <td colspan="5" class="text-center align-middle py-0">
                    <span class="text-muted small">${p.start_time.substring(0,5)} - ${p.end_time.substring(0,5)}</span>
                </td>
            </tr>`;
        } else {
            html += `
            <tr data-period-id="${p.id}">
                <td class="text-center align-middle bg-light">
                    <div class="fw-bold text-dark">คาบที่ ${p.period_no}</div>
                    <div class="text-muted small" style="font-size:0.7rem">${p.start_time.substring(0,5)} - ${p.end_time.substring(0,5)}</div>
                </td>
                <td data-day="Monday"></td>
                <td data-day="Tuesday"></td>
                <td data-day="Wednesday"></td>
                <td data-day="Thursday"></td>
                <td data-day="Friday"></td>
            </tr>`;
        }
    });
    tableBody.html(html);

    // Plot data
    if (Array.isArray(data)) {
        data.forEach(s => {
            const target = tableBody.find(`tr[data-period-id="${s.period_id}"] td[data-day="${s.day_of_week}"]`);
            if (target.length) {
                const subLabel = '<?php echo $role; ?>' === 'teacher' ? `กลุ่มเรียน: ${s.class_name}` : `ครู: ${s.teacher_name}`;
                target.html(`
                    <div class="slot-card animate-fade-in shadow-sm">
                        <div class="fw-bold text-primary mb-1">${s.subject_code}</div>
                        <div class="fw-bold text-dark mb-2 truncate" title="${s.subject_name}">${s.subject_name}</div>
                        <div class="mt-auto">
                            <div class="text-muted x-small mb-1"><i class="bi bi-geo-alt me-1"></i>${s.room_name}</div>
                            <div class="text-muted x-small"><i class="bi bi-person me-1"></i>${subLabel}</div>
                        </div>
                    </div>
                `);
            }
        });
    }
}
</script>
