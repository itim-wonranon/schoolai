<?php
require_once __DIR__ . '/../includes/session_check.php';
requireRole('admin');
$page_title = 'ปีการศึกษาและเทอม - Super Admin';
$current_page = 'admin_academic';

require_once __DIR__ . '/../includes/layout_header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">จัดการปีการศึกษาและเทอม</h2>
            <p class="text-muted">กำหนดปีการศึกษาปัจจุบันและจัดการช่วงเวลาการเรียนการสอน</p>
        </div>
        <button class="btn btn-primary shadow-sm" onclick="openYearModal()">
            <i class="bi bi-calendar-plus me-2"></i> เพิ่มปีการศึกษาใหม่
        </button>
    </div>

    <div class="row">
        <!-- Academic Years List -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">รายการปีการศึกษา</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="yearsTable">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="ps-4">ปีการศึกษา</th>
                                <th>เทอม</th>
                                <th>สถานะ</th>
                                <th class="text-end pe-4">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- System Settings Summary -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-gradient-info text-white mb-4">
                <div class="card-body p-4">
                    <h6 class="opacity-75 mb-3">ปีการศึกษาปัจจุบันในระบบ</h6>
                    <h2 class="fw-bold mb-2" id="currentYearDisplay">--</h2>
                    <p class="mb-0" id="currentTermDisplay">เทอม: --</p>
                    <hr class="my-3 opacity-25">
                    <small>* ข้อมูลนี้จะถูกใช้เป็นค่าเริ่มต้นในทุกโมดูล</small>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i> คำแนะนำ</h6>
                    <ul class="text-muted small ps-3">
                        <li class="mb-2">การเปลี่ยนปีการศึกษาหลักจะส่งผลต่อการลงทะเบียนเรียนและผลการเรียน</li>
                        <li class="mb-2">ควรตรวจสอบข้อมูลให้ถูกต้องก่อนกดบันทึก</li>
                        <li>ปีการศึกษาที่ปิดไปแล้วจะไม่สามารถใช้บันทึกข้อมูลใหม่ได้ แต่ยังดูย้อนหลังได้</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Year Modal -->
<div class="modal fade" id="yearModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="yearModalLabel">ข้อมูลปีการศึกษา</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="yearForm">
                    <input type="hidden" id="year_id">
                    <div class="mb-3">
                        <label class="form-label">ปีการศึกษา (พ.ศ.)</label>
                        <input type="number" class="form-control" id="year_name" placeholder="เช่น 2567" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เทอม</label>
                        <select class="form-control" id="term_name" required>
                            <option value="1">เทอม 1</option>
                            <option value="2">เทอม 2</option>
                            <option value="ฤดูร้อน">ภาคฤดูร้อน</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_current" value="1">
                            <label class="form-check-label" for="is_current">กำหนดเป็นปีการศึกษา/เทอมปัจจุบัน</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary px-4" onclick="saveYear()">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>

<script>
$(document).ready(function() {
    loadYears();
});

function loadYears() {
    $.get(BASE_URL + '/api/admin/academic_years.php', function(data) {
        let rows = '';
        data.forEach(function(y) {
            const badge = y.is_current == 1 
                ? '<span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle-fill me-1"></i> Current</span>' 
                : '<span class="badge bg-light text-muted">Inactive</span>';
            
            if (y.is_current == 1) {
                $('#currentYearDisplay').text(y.year);
                $('#currentTermDisplay').text('เทอม: ' + y.term);
            }

            rows += `
            <tr>
                <td class="ps-4 fw-bold">${y.year}</td>
                <td>${y.term}</td>
                <td>${badge}</td>
                <td class="text-end pe-4">
                    <button class="btn btn-sm btn-light border" onclick="editYear(${y.id})" title="แก้ไข">
                        <i class="bi bi-pencil"></i>
                    </button>
                    ${y.is_current == 0 ? `<button class="btn btn-sm btn-outline-primary" onclick="setCurrent(${y.id})" title="ตั้งเป็นปัจจุบัน">Set Active</button>` : ''}
                </td>
            </tr>`;
        });
        $('#yearsTable tbody').html(rows);
    });
}

function openYearModal(id = null) {
    $('#yearForm')[0].reset();
    $('#year_id').val('');
    $('#yearModalLabel').text(id ? 'แก้ไขปีการศึกษา' : 'เพิ่มปีการศึกษาใหม่');
    
    if (id) {
        $.get(BASE_URL + '/api/admin/academic_years.php?id=' + id, function(y) {
            $('#year_id').val(y.id);
            $('#year_name').val(y.year);
            $('#term_name').val(y.term);
            $('#is_current').prop('checked', y.is_current == 1);
            new bootstrap.Modal($('#yearModal')).show();
        });
    } else {
        new bootstrap.Modal($('#yearModal')).show();
    }
}

function saveYear() {
    const data = {
        id: $('#year_id').val(),
        year: $('#year_name').val(),
        term: $('#term_name').val(),
        is_current: $('#is_current').is(':checked') ? 1 : 0
    };
    
    const method = data.id ? 'PUT' : 'POST';
    
    $.ajax({
        url: BASE_URL + '/api/admin/academic_years.php',
        method: method,
        data: JSON.stringify(data),
        contentType: 'application/json',
        success: function(res) {
            if (res.success) {
                showToast(res.message, 'success');
                bootstrap.Modal.getInstance($('#yearModal')).hide();
                loadYears();
            } else {
                showToast(res.message, 'error');
            }
        }
    });
}

function setCurrent(id) {
    if (!confirm('ยืนยันการเปลี่ยนปีการศึกษาหลักของระบบ?')) return;
    $.ajax({
        url: BASE_URL + '/api/admin/academic_years.php',
        method: 'PATCH',
        data: JSON.stringify({ id: id }),
        contentType: 'application/json',
        success: function(res) {
            if (res.success) {
                showToast('เปลี่ยนปีการศึกษาหลักสำเร็จ', 'success');
                loadYears();
            }
        }
    });
}

function editYear(id) { openYearModal(id); }
</script>
