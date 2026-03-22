<?php
require_once __DIR__ . '/../includes/session_check.php';
requireRole('admin');
$page_title = 'จัดการกลุ่มสาระและหลักสูตร - Super Admin';
$current_page = 'admin_curriculum';

require_once __DIR__ . '/../includes/layout_header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">จัดการกลุ่มสาระและหลักสูตร</h2>
            <p class="text-muted">บริหารจัดการโครงสร้างกลุ่มสาระวิชาและหลักสูตรการศึกษา</p>
        </div>
        <button class="btn btn-primary shadow-sm" onclick="openGroupModal()">
            <i class="bi bi-folder-plus me-2"></i> เพิ่มกลุ่มสาระ
        </button>
    </div>

    <!-- Groups Grid -->
    <div class="row" id="groupsContainer">
        <!-- Loaded via JS -->
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">กำลังโหลดข้อมูลกลุ่มสาระ...</p>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="groupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="groupModalLabel">ข้อมูลกลุ่มสาระ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="groupForm">
                    <input type="hidden" id="group_id">
                    <div class="mb-3">
                        <label class="form-label">ชื่อกลุ่มสาระ</label>
                        <input type="text" class="form-control" id="group_name" placeholder="เช่น วิทยาศาสตร์และเทคโนโลยี" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">คำอธิบาย</label>
                        <textarea class="form-control" id="group_description" rows="3" placeholder="ระบุรายละเอียดเพิ่มเติม..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary px-4" onclick="saveGroup()">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>

<script>
$(document).ready(function() {
    loadGroups();
});

function loadGroups() {
    $.get(BASE_URL + '/api/admin/curriculum.php', function(data) {
        let html = '';
        if (data.length === 0) {
            html = '<div class="col-12 text-center py-5 text-muted">ไม่พบข้อมูลกลุ่มสาระ</div>';
        }
        data.forEach(function(g) {
            html += `
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100 user-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-sm bg-primary-subtle text-primary rounded d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
                                <i class="bi bi-folder-fill fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">${g.name}</h5>
                                <small class="text-muted">วิชาทั้งหมด: ${g.subject_count || 0}</small>
                            </div>
                        </div>
                        <p class="text-muted small mb-3 text-truncate-2">${g.description || 'ไม่มีคำอธิบาย'}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <button class="btn btn-sm btn-link text-decoration-none px-0" onclick="editGroup(${g.id})">แก้ไขรายละเอียด</button>
                            <button class="btn btn-sm btn-outline-danger border-0" onclick="deleteGroup(${g.id})"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        $('#groupsContainer').html(html);
    });
}

function openGroupModal(id = null) {
    $('#groupForm')[0].reset();
    $('#group_id').val('');
    $('#groupModalLabel').text(id ? 'แก้ไขกลุ่มสาระ' : 'เพิ่มกลุ่มสาระใหม่');
    
    if (id) {
        $.get(BASE_URL + '/api/admin/curriculum.php?id=' + id, function(g) {
            $('#group_id').val(g.id);
            $('#group_name').val(g.name);
            $('#group_description').val(g.description);
            new bootstrap.Modal($('#groupModal')).show();
        });
    } else {
        new bootstrap.Modal($('#groupModal')).show();
    }
}

function saveGroup() {
    const data = {
        id: $('#group_id').val(),
        name: $('#group_name').val(),
        description: $('#group_description').val()
    };
    const method = data.id ? 'PUT' : 'POST';
    
    $.ajax({
        url: BASE_URL + '/api/admin/curriculum.php',
        method: method,
        data: JSON.stringify(data),
        contentType: 'application/json',
        success: function(res) {
            if (res.success) {
                showToast(res.message, 'success');
                bootstrap.Modal.getInstance($('#groupModal')).hide();
                loadGroups();
            } else {
                showToast(res.message, 'error');
            }
        }
    });
}

function deleteGroup(id) {
    if (!confirm('ยืนยันระบบกลุ่มสาระ? (กลุ่มสาระที่มีวิชาอยู่จะไม่สามารถลบได้)')) return;
    $.ajax({
        url: BASE_URL + '/api/admin/curriculum.php',
        method: 'DELETE',
        data: JSON.stringify({ id: id }),
        contentType: 'application/json',
        success: function(res) {
            if (res.success) {
                showToast('ลบข้อมูลสำเร็จ', 'success');
                loadGroups();
            } else {
                showToast(res.message, 'error');
            }
        }
    });
}

function editGroup(id) { openGroupModal(id); }
</script>
