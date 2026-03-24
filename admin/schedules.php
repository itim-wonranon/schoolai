<?php
require_once __DIR__ . '/../includes/session_check.php';
requireRole('admin');
$page_title = 'จัดการตารางเรียน - โรงเรียนสาธิตวิทยา';
$current_page = 'admin_schedules';

require_once __DIR__ . '/../includes/layout_header.php';
?>

<div class="container-fluid py-4">
    <div class="row align-items-end mb-4 g-3">
        <div class="col">
            <h2 class="fw-bold mb-1"><i class="bi bi-calendar3-event text-primary me-2"></i>จัดการตารางเรียน</h2>
            <p class="text-muted mb-0">ระบบจัดตารางเรียนอัจฉริยะ แยกตามระดับชั้นมัธยมต้นและมัธยมปลาย</p>
        </div>
        <div class="col-auto">
            <div class="d-flex gap-2">
                <div class="input-group" style="width: 280px;">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-filter"></i></span>
                    <select class="form-select border-start-0" id="filterClass" onchange="onFilterClassChange()">
                        <option value="">-- เลือกชั้นเรียนเพื่อดูตาราง --</option>
                    </select>
                </div>
                <button class="btn btn-dark fw-bold px-4 rounded-3 shadow-sm" onclick="openScheduleModal()">
                    <i class="bi bi-plus-lg me-2"></i>เพิ่มวิชาเรียน
                </button>
            </div>
        </div>
    </div>

    <!-- Timetable View -->
    <div id="timetableWrapper" style="display: none;">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 animate-fade-in">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark" id="currentClassName">ตารางเรียน</h5>
                <div class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill" id="levelBadge">ระดับชั้น</div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered mb-0 timetable-grid">
                    <thead>
                        <tr class="bg-light text-center">
                            <th style="width: 140px;" class="py-3 border-0">เวลา / คาบ</th>
                            <th class="py-3 border-0" style="width: 20%;">จันทร์</th>
                            <th class="py-3 border-0" style="width: 20%;">อังคาร</th>
                            <th class="py-3 border-0" style="width: 20%;">พุธ</th>
                            <th class="py-3 border-0" style="width: 20%;">พฤหัสบดี</th>
                            <th class="py-3 border-0" style="width: 20%;">ศุกร์</th>
                        </tr>
                    </thead>
                    <tbody id="timetableBody">
                        <!-- Periods will be injected here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="noClassSelected" class="text-center py-5">
        <div class="py-5">
            <i class="bi bi-calendar2-range display-1 text-light"></i>
            <h4 class="text-muted mt-3">กรุณาเลือกชั้นเรียนเพื่อแสดงตารางเรียน</h4>
        </div>
    </div>
</div>

<!-- Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitle">เพิ่มข้อมูลตารางเรียน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="scheduleForm">
                    <input type="hidden" id="edit_id">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="p-3 bg-light rounded-3 mb-2">
                                <label class="form-label small fw-bold text-uppercase text-muted">เป้าหมายการสอน</label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <select class="form-select border-0 shadow-sm" name="class_id" id="sch_class" required onchange="onModalClassChange()">
                                            <option value="">-- เลือกชั้นเรียน --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <select class="form-select border-0 shadow-sm" name="classroom_id" id="sch_classroom" required>
                                            <option value="">-- เลือกห้องเรียน --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">รายวิชา</label>
                            <select class="form-select rounded-3 border-1" name="subject_id" id="sch_subject" required></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">ครูผู้สอน</label>
                            <select class="form-select rounded-3 border-1" name="teacher_id" id="sch_teacher" required></select>
                        </div>
                        <div class="col-md-12">
                            <hr class="opacity-10 my-1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">วัน</label>
                            <select class="form-select rounded-3 border-1" name="day_of_week" id="sch_day" required>
                                <option value="Monday">จันทร์</option>
                                <option value="Tuesday">อังคาร</option>
                                <option value="Wednesday">พุธ</option>
                                <option value="Thursday">พฤหัสบดี</option>
                                <option value="Friday">ศุกร์</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-muted">คาบเรียน (Period)</label>
                            <select class="form-select rounded-3 border-1" name="period_id" id="sch_period" onchange="autoFillTimes()" required>
                                <option value="">-- เลือกชั้นเรียนก่อน --</option>
                            </select>
                        </div>
                        <input type="hidden" name="start_time" id="sch_start">
                        <input type="hidden" name="end_time" id="sch_end">
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-outline-danger me-auto fw-bold" id="btnDelete" onclick="deleteSchedule()" style="display:none;"><i class="bi bi-trash me-2"></i>ลบคาบเรียนนี้</button>
                <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary px-4 fw-bold shadow" onclick="submitSchedule()">บันทึกตารางเรียน</button>
            </div>
        </div>
    </div>
</div>

<style>
.timetable-grid th { font-size: 0.8rem; letter-spacing: 0.05em; font-weight: 700; color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; }
.timetable-grid td { height: 110px; vertical-align: top; padding: 8px; border: 1px solid #e2e8f0; background: #fff; }
.slot-item {
    font-size: 0.75rem;
    padding: 10px;
    border-radius: 10px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #3b82f6;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}
.slot-item:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.08); border-color: #3b82f6; z-index: 5; }
.lunch-row { background-color: #fffbeb !important; height: 50px !important; }
.lunch-text { font-size: 0.8rem; font-weight: 800; color: #b45309; text-transform: uppercase; letter-spacing: 0.1em; }
.animate-fade-in { animation: fadeIn 0.5s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.avatar-tag { width: 20px; height: 20px; background: #f1f5f9; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.6rem; color: #475569; }
</style>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>

<script>
let currentPeriods = [];
let allSchedules = [];

$(document).ready(function() {
    loadMetaData();
});

function loadMetaData() {
    $.get(BASE_URL + '/api/admin/users.php?type=classes', function(data) {
        let html = '<option value="">-- เลือกชั้นเรียน --</option>';
        data.forEach(c => html += `<option value="${c.id}" data-name="${c.class_name}">${c.class_name}</option>`);
        $('#filterClass, #sch_class').html(html);
    });

    $.get(BASE_URL + '/api/admin/users.php?type=subjects', function(data) {
        let html = '';
        data.forEach(s => html += `<option value="${s.id}">${s.subject_code} - ${s.subject_name}</option>`);
        $('#sch_subject').html(html);
    });

    $.get(BASE_URL + '/api/admin/users.php?type=teachers', function(data) {
        let html = '';
        data.forEach(t => html += `<option value="${t.id}">${t.first_name} ${t.last_name}</option>`);
        $('#sch_teacher').html(html);
    });

    $.get(BASE_URL + '/api/admin/users.php?type=classrooms', function(data) {
        let html = '';
        data.forEach(r => html += `<option value="${r.id}">${r.room_name}</option>`);
        $('#sch_classroom').html(html);
    });
}

function onFilterClassChange() {
    const classId = $('#filterClass').val();
    if (!classId) {
        $('#timetableWrapper').hide();
        $('#noClassSelected').show();
        return;
    }

    const className = $('#filterClass option:selected').data('name');
    $('#currentClassName').text('ตารางเรียน ' + className);
    $('#noClassSelected').hide();
    $('#timetableWrapper').show();

    // Determine level from name (Thai characters)
    const level = className.indexOf('ม.1') !== -1 || className.indexOf('ม.2') !== -1 || className.indexOf('ม.3') !== -1 ? 'middle' : 'high';
    $('#levelBadge').text(level === 'middle' ? 'มัธยมศึกษาตอนต้น' : 'มัธยมศึกษาตอนปลาย')
                   .removeClass('bg-primary-subtle bg-info-subtle text-primary text-info')
                   .addClass(level === 'middle' ? 'bg-primary-subtle text-primary' : 'bg-info-subtle text-info');

    // Load periods for this level
    $.get(`${BASE_URL}/api/schedules.php?periods=1&level=${level}`, function(data) {
        currentPeriods = data;
        renderGridSkeleton();
        loadTimetableData(classId);
    });
}

function renderGridSkeleton() {
    let html = '';
    currentPeriods.forEach(p => {
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
    $('#timetableBody').html(html);
}

function loadTimetableData(classId) {
    $.get(BASE_URL + '/api/schedules.php?class_id=' + classId, function(data) {
        data.forEach(s => {
            const target = $(`#timetableBody tr[data-period-id="${s.period_id}"] td[data-day="${s.day_of_week}"]`);
            if (target.length) {
                target.append(`
                    <div class="slot-item mb-2" onclick="editScheduleEntry(${s.id})">
                        <div class="fw-bold text-dark truncate mb-1">${s.subject_name}</div>
                        <div class="d-flex align-items-center text-muted x-small gap-1 truncate">
                            <i class="bi bi-person"></i> ${s.teacher_name}
                        </div>
                        <div class="d-flex align-items-center text-muted x-small gap-1">
                            <i class="bi bi-geo-alt"></i> ${s.room_name}
                        </div>
                    </div>
                `);
            }
        });
    });
}

function onModalClassChange() {
    const classId = $('#sch_class').val();
    if (!classId) {
        $('#sch_period').html('<option value="">-- เลือกชั้นเรียนก่อน --</option>');
        return;
    }
    
    $.get(`${BASE_URL}/api/schedules.php?periods=1&class_id=${classId}`, function(data) {
        let phtml = '<option value="">-- เลือกคาบเรียน --</option>';
        data.forEach(p => {
            if (p.is_lunch == 0) {
                phtml += `<option value="${p.id}" data-start="${p.start_time}" data-end="${p.end_time}">คาบที่ ${p.period_no} (${p.start_time.substring(0,5)} - ${p.end_time.substring(0,5)})</option>`;
            }
        });
        $('#sch_period').html(phtml);
    });
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
    $('#modalTitle').text('เพิ่มตารางเรียนใหม่');
    $('#sch_period').html('<option value="">-- เลือกชั้นเรียนก่อน --</option>');
    
    // Auto-select class if filter is active
    const activeFilter = $('#filterClass').val();
    if (activeFilter) {
        $('#sch_class').val(activeFilter);
        onModalClassChange();
    }

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
        
        // Load periods for this class before setting period_id
        $.get(`${BASE_URL}/api/schedules.php?periods=1&class_id=${s.class_id}`, function(pdata) {
            let phtml = '<option value="">-- เลือกคาบเรียน --</option>';
            pdata.forEach(p => {
                if (p.is_lunch == 0) {
                    phtml += `<option value="${p.id}" data-start="${p.start_time}" data-end="${p.end_time}">คาบที่ ${p.period_no} (${p.start_time.substring(0,5)} - ${p.end_time.substring(0,5)})</option>`;
                }
            });
            $('#sch_period').html(phtml).val(s.period_id);
            $('#sch_start').val(s.start_time);
            $('#sch_end').val(s.end_time);
        });

        $('#btnDelete').show();
        $('#modalTitle').text('แก้ไขตารางเรียน');
        const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
        modal.show();
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

    if (!data.subject_id || !data.class_id || !data.period_id) {
        showToast('กรุณากรอกข้อมูลสำคัญให้ครบถ้วน', 'warning');
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
                bootstrap.Modal.getInstance(document.getElementById('scheduleModal')).hide();
                onFilterClassChange();
            } else {
                showToast(res.message, 'error');
            }
        }
    });
}

function deleteSchedule() {
    const id = $('#edit_id').val();
    if (!id || !confirm('ต้องการลบคาบเรียนนี้ใช่หรือไม่?')) return;

    $.ajax({
        url: BASE_URL + '/api/schedules.php?id=' + id,
        method: 'DELETE',
        success: function(res) {
            if (res.success) {
                showToast(res.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('scheduleModal')).hide();
                onFilterClassChange();
            }
        }
    });
}
</script>
