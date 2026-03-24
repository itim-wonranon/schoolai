<?php
require_once __DIR__ . '/../includes/session_check.php';
requireRole('admin');
$page_title = 'จัดการตารางเรียน - โรงเรียนสาธิตวิทยา';
$current_page = 'admin_schedules';

require_once __DIR__ . '/../includes/layout_header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">จัดการตารางเรียน (Advanced Scheduling)</h2>
            <p class="text-muted">จัดสรรรายวิชา ครู และห้องเรียน พร้อมระบบตรวจสอบการทับซ้อน</p>
        </div>
        <div class="d-flex gap-2">
            <select class="form-select w-auto" id="filterClass" onchange="loadTimetable()">
                <option value="">-- กรองตามชั้นเรียน --</option>
            </select>
            <button class="btn btn-primary" onclick="openScheduleModal()">
                <i class="bi bi-calendar-plus me-2"></i> เพิ่มคาบเรียน
            </button>
        </div>
    </div>

    <!-- Timetable Grid -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 timetable-grid">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center bg-white" style="width: 100px;">เวลา / วัน</th>
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
                        <!-- Periodic rows will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitle">เพิ่มข้อมูลตารางเรียน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">
                    <input type="hidden" id="edit_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">รายวิชา</label>
                            <select class="form-select" name="subject_id" id="sch_subject" required></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ครูผู้สอน</label>
                            <select class="form-select" name="teacher_id" id="sch_teacher" required></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ชั้นเรียน</label>
                            <select class="form-select" name="class_id" id="sch_class" required></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ห้องเรียน</label>
                            <select class="form-select" name="classroom_id" id="sch_classroom" required></select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">วัน</label>
                            <select class="form-select" name="day_of_week" id="sch_day" required>
                                <option value="Monday">จันทร์</option>
                                <option value="Tuesday">อังคาร</option>
                                <option value="Wednesday">พุธ</option>
                                <option value="Thursday">พฤหัสบดี</option>
                                <option value="Friday">ศุกร์</option>
                                <option value="Saturday">เสาร์</option>
                                <option value="Sunday">อาทิตย์</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">คาบเรียน (Period)</label>
                            <select class="form-select" name="period_id" id="sch_period" onchange="autoFillTimes()">
                                <option value="">กำหนดเอง</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">เริ่ม</label>
                            <input type="time" class="form-control" name="start_time" id="sch_start" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">สิ้นสุด</label>
                            <input type="time" class="form-control" name="end_time" id="sch_end" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-danger me-auto" id="btnDelete" onclick="deleteSchedule()" style="display:none;"><i class="bi bi-trash me-2"></i>ลบ</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary px-4" onclick="submitSchedule()">บันทึกตารางเรียน</button>
            </div>
        </div>
    </div>
</div>

<style>
.timetable-grid th { font-size: 0.85rem; vertical-align: middle; }
.timetable-grid td { height: 80px; vertical-align: top; padding: 4px; border-color: #eee !important; }
.slot-item {
    font-size: 0.72rem;
    padding: 6px;
    border-radius: 6px;
    background: #f0f7ff;
    border-left: 3px solid #0d6efd;
    cursor: pointer;
    transition: all 0.2s;
    line-height: 1.2;
}
.slot-item:hover { transform: scale(1.02); filter: brightness(0.95); z-index: 10; position: relative; }
.bg-light-warning { background-color: #fffbeb !important; }
.bg-light-danger { background-color: #fef2f2 !important; }
.truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>

<script>
/**
 * Advanced Scheduling Logic
 * Renamed functions to avoid conflict with global script.js
 */
let periods = [];
let allSchedules = [];

$(document).ready(function() {
    loadMetaData();
    loadTimetable();
});

function loadMetaData() {
    // Load Classes
    $.get(BASE_URL + '/api/admin/users.php?type=classes', function(data) {
        let html = '<option value="">-- ทุกชั้นเรียน --</option>';
        data.forEach(c => html += `<option value="${c.id}">${c.class_name}</option>`);
        $('#filterClass, #sch_class').html(html);
    });

    // Load Subjects
    $.get(BASE_URL + '/api/admin/users.php?type=subjects', function(data) {
        let html = '';
        data.forEach(s => html += `<option value="${s.id}">${s.subject_code} - ${s.subject_name}</option>`);
        $('#sch_subject').html(html);
    });

    // Load Teachers
    $.get(BASE_URL + '/api/admin/users.php?type=teachers', function(data) {
        let html = '';
        data.forEach(t => html += `<option value="${t.id}">${t.first_name} ${t.last_name}</option>`);
        $('#sch_teacher').html(html);
    });

    // Load Classrooms
    $.get(BASE_URL + '/api/admin/users.php?type=classrooms', function(data) {
        let html = '';
        data.forEach(r => html += `<option value="${r.id}">${r.room_name}</option>`);
        $('#sch_classroom').html(html);
    });

    // Load Periods
    $.get(BASE_URL + '/api/schedules.php?periods=1', function(data) {
        periods = data;
        let phtml = '<option value="">กำหนดเอง</option>';
        data.forEach(p => phtml += `<option value="${p.id}" data-start="${p.start_time}" data-end="${p.end_time}">คาบที่ ${p.period_no} (${p.start_time.substring(0,5)})</option>`);
        $('#sch_period').html(phtml);
        renderGridSkeleton();
    });
}

function renderGridSkeleton() {
    let html = '';
    if (periods.length === 0) {
        html = '<tr><td colspan="8" class="text-center py-4">กำลังโหลดข้อมูลคาบเรียน...</td></tr>';
    } else {
        periods.forEach(p => {
            html += `<tr data-period-id="${p.id}">
                <td class="text-center small bg-light align-middle" style="min-width:110px">
                    <strong class="d-block">คาบที่ ${p.period_no}</strong>
                    <span class="text-muted" style="font-size:0.7rem">${p.start_time.substring(0,5)} - ${p.end_time.substring(0,5)}</span>
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
    }
    $('#timetableBody').html(html);
}

function loadTimetable() {
    const classId = $('#filterClass').val();
    $.get(BASE_URL + '/api/schedules.php' + (classId ? '?class_id=' + classId : ''), function(data) {
        allSchedules = data;
        renderDataOnGrid();
    });
}

function renderDataOnGrid() {
    renderGridSkeleton();
    if (allSchedules && allSchedules.length > 0) {
        allSchedules.forEach(s => {
            const target = $(`#timetableBody tr[data-period-id="${s.period_id}"] td[data-day="${s.day_of_week}"]`);
            if (target.length) {
                target.append(`
                    <div class="slot-item shadow-sm mb-1" onclick="editScheduleEntry(${s.id})" title="แก้ไข: ${s.subject_name}">
                        <div class="fw-bold text-primary truncate">${s.subject_name}</div>
                        <div class="text-muted truncate"><i class="bi bi-person small me-1"></i>${s.teacher_name}</div>
                        <div class="text-muted truncate"><i class="bi bi-geo-alt small me-1"></i>${s.room_name}</div>
                    </div>
                `);
            }
        });
    }
}

function autoFillTimes() {
    const opt = $('#sch_period option:selected');
    if (opt.val()) {
        $('#sch_start').val(opt.data('start'));
        $('#sch_end').val(opt.data('end'));
    }
}

function openScheduleModal() {
    $('#scheduleForm')[0].reset();
    $('#edit_id').val('');
    $('#btnDelete').hide();
    $('#modalTitle').text('เพิ่มข้อมูลตารางเรียน');
    const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
    modal.show();
}

function editScheduleEntry(id) {
    $.get(BASE_URL + '/api/schedules.php?id=' + id, function(s) {
        $('#edit_id').val(s.id);
        $('#sch_subject').val(s.subject_id);
        $('#sch_teacher').val(s.teacher_id);
        $('#sch_class').val(s.class_id);
        $('#sch_classroom').val(s.classroom_id);
        $('#sch_day').val(s.day_of_week);
        $('#sch_period').val(s.period_id);
        $('#sch_start').val(s.start_time);
        $('#sch_end').val(s.end_time);
        $('#btnDelete').show();
        $('#modalTitle').text('แก้ไขข้อมูลตารางเรียน');
        const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
        modal.show();
    });
}

function deleteSchedule() {
    const id = $('#edit_id').val();
    if (!id || !confirm('ยืนยันการลบตารางเรียนนี้?')) return;

    $.ajax({
        url: BASE_URL + '/api/schedules.php?id=' + id,
        method: 'DELETE',
        success: function(res) {
            if (res.success) {
                showToast(res.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('scheduleModal')).hide();
                loadTimetable();
            } else {
                showToast(res.message, 'error');
            }
        }
    });
}

function submitSchedule() {
    const id = $('#edit_id').val();
    const data = {
        id: id,
        subject_id: $('#sch_subject').val(),
        teacher_id: $('#sch_teacher').val(),
        class_id: $('#sch_class').val(),
        classroom_id: $('#sch_classroom').val(),
        day_of_week: $('#sch_day').val(),
        period_id: $('#sch_period').val(),
        start_time: $('#sch_start').val(),
        end_time: $('#sch_end').val()
    };

    // Validation
    if (!data.subject_id || !data.teacher_id || !data.class_id || !data.classroom_id || !data.day_of_week || !data.start_time || !data.end_time) {
        showToast('กรุณากรอกข้อมูลให้ครบถ้วน', 'warning');
        return;
    }

    $.ajax({
        url: BASE_URL + '/api/schedules.php',
        method: id ? 'PUT' : 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        success: function(res) {
            if (res.success) {
                showToast(res.message, 'success');
                const modalEl = document.getElementById('scheduleModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                loadTimetable();
            } else {
                showToast(res.message, 'error');
            }
        },
        error: function(xhr) {
            let msg = 'เกิดข้อผิดพลาดในการบันทึก';
            try {
                const res = JSON.parse(xhr.responseText);
                msg = res.message || msg;
            } catch(e) {}
            showToast(msg, 'error');
        }
    });
}
</script>
</script>
