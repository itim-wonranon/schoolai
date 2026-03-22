<?php
require_once __DIR__ . '/../includes/session_check.php';
requireRole('admin');
$page_title = 'กำหนดสิทธิ์ (RBAC) - Super Admin';
$current_page = 'admin_roles';

require_once __DIR__ . '/../includes/layout_header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">จัดการระดับสิทธิ์และหน้าที่ (RBAC)</h2>
            <p class="text-muted">กำหนดขอบเขตการเข้าถึงข้อมูลและฟังก์ชันต่างๆ ของแต่ละกลุ่มผู้ใช้งาน</p>
        </div>
    </div>

    <div class="row" id="rolesGrid">
        <!-- Roles loaded via JS -->
    </div>
</div>

<!-- Permissions Modal -->
<div class="modal fade" id="permissionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="roleTitle">แก้ไขสิทธิ์การใช้งาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="permissionsForm">
                    <input type="hidden" id="edit_role_id">
                    
                    <div id="permissionsGroups">
                        <!-- Grouped permissions checkboxes -->
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary px-4" onclick="savePermissions()">บันทึกสิทธิ์ที่เลือก</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>

<script>
const PERMISSION_MAP = {
    'System Control': [
        { key: 'manage_settings', label: 'ตั้งค่าระบบทั่วไป' },
        { key: 'view_audit_logs', label: 'ดูบันทึกกิจกรรม (Audit Logs)' },
        { key: 'broadcast_announcements', label: 'ส่งประกาศข่าวสาร' }
    ],
    'User Management': [
        { key: 'manage_users', label: 'จัดการรายชื่อผู้ใช้' },
        { key: 'manage_roles', label: 'จัดการสิทธิ์ (RBAC)' },
        { key: 'impersonate_users', label: 'สวมรอยเป็นผู้ใช้ (Impersonation)' }
    ],
    'Academic Data': [
        { key: 'manage_academic_year', label: 'จัดการปีการศึกษา' },
        { key: 'manage_curriculum', label: 'จัดการหลักสูตรและกลุ่มสาระ' },
        { key: 'manage_teachers', label: 'จัดการข้อมูลครู' },
        { key: 'manage_students', label: 'จัดการข้อมูลนักเรียน' },
        { key: 'manage_subjects', label: 'จัดการรายวิชา' }
    ],
    'Operations': [
        { key: 'record_attendance', label: 'เช็คชื่อนักเรียน' },
        { key: 'record_grades', label: 'บันทึกเกรด/คะแนน' },
        { key: 'view_reports', label: 'ดูรายงานสรุปผล' }
    ]
};

$(document).ready(function() {
    loadRoles();
});

function loadRoles() {
    $.get(BASE_URL + '/api/admin/roles.php', function(data) {
        let html = '';
        data.forEach(function(r) {
            const permCount = r.permissions ? Object.keys(JSON.parse(r.permissions)).length : 0;
            html += `
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 text-center">
                        <div class="avatar-lg bg-light text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:64px;height:64px;">
                            <i class="bi bi-shield-check fs-2"></i>
                        </div>
                        <h5 class="fw-bold mb-1">${r.role_name}</h5>
                        <code class="text-primary small">KEY_ID: ${r.role_key}</code>
                        <hr class="my-3 opacity-25">
                        <p class="text-muted small">อนุญาตแล้ว ${permCount} ฟังก์ชัน</p>
                        <button class="btn btn-sm btn-outline-primary px-4" onclick="editPermissions(${r.id})">
                            <i class="bi bi-pencil-square me-2"></i> กำหนดสิทธิ์
                        </button>
                    </div>
                </div>
            </div>`;
        });
        $('#rolesGrid').html(html);
    });
}

function editPermissions(id) {
    $.get(BASE_URL + '/api/admin/roles.php?id=' + id, function(role) {
        $('#edit_role_id').val(role.id);
        $('#roleTitle').text('แก้ไขสิทธิ์การใช้งาน: ' + role.role_name);
        
        const existingPerms = role.permissions ? JSON.parse(role.permissions) : {};
        let html = '';
        
        for (const [group, items] of Object.entries(PERMISSION_MAP)) {
            html += `
            <div class="mb-4">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">${group}</h6>
                <div class="row">`;
            
            items.forEach(function(p) {
                const checked = existingPerms[p.key] ? 'checked' : '';
                html += `
                <div class="col-md-6 mb-2">
                    <div class="form-check form-switch p-2 bg-light rounded-2 border">
                        <input class="form-check-input ms-0 me-2" type="checkbox" id="perm_${p.key}" name="perms[]" value="${p.key}" ${checked}>
                        <label class="form-check-label small" for="perm_${p.key}">${p.label}</label>
                    </div>
                </div>`;
            });
            
            html += `</div></div>`;
        }
        
        $('#permissionsGroups').html(html);
        new bootstrap.Modal($('#permissionsModal')).show();
    });
}

function savePermissions() {
    const roleId = $('#edit_role_id').val();
    const perms = {};
    $('#permissionsForm input[type=checkbox]:checked').each(function() {
        perms[$(this).val()] = true;
    });

    $.ajax({
        url: BASE_URL + '/api/admin/roles.php',
        method: 'PUT',
        data: JSON.stringify({ id: roleId, permissions: perms }),
        contentType: 'application/json',
        success: function(res) {
            if (res.success) {
                showToast('อัปเดตสิทธิ์สำเร็จ', 'success');
                bootstrap.Modal.getInstance($('#permissionsModal')).hide();
                loadRoles();
            }
        }
    });
}
</script>
