<?php
require_once __DIR__ . '/../includes/session_check.php';
requireRole('admin');
$page_title = 'จัดการสิทธิ์ผู้ใช้งาน - Super Admin';
$current_page = 'admin_users';

require_once __DIR__ . '/../includes/layout_header.php';
?>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item text-muted">Super Admin</li>
                    <li class="breadcrumb-item active">จัดการสิทธิ์ผู้ใช้งาน</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0 text-dark">รายชื่อผู้ใช้งานและสิทธิ์</h2>
            <p class="text-muted mb-0">บริหารจัดการบัญชีผู้ใช้และกำหนดบทบาทเข้าถึงระบบ</p>
        </div>
        <button class="btn btn-primary btn-lg shadow-sm rounded-pill px-4" onclick="openUserModal()">
            <i class="bi bi-person-plus-fill me-2"></i> เพิ่มผู้ใช้งานใหม่
        </button>
    </div>

    <!-- Stats Dashboard -->
    <div class="row g-3 mb-4" id="statsContainer">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                        <h6 class="text-muted mb-0">ผู้ใช้งานทั้งหมด</h6>
                    </div>
                    <h3 class="fw-bold mb-0" id="stat_total">--</h3>
                    <div class="position-absolute end-0 bottom-0 p-3 opacity-10">
                        <i class="bi bi-people-fill display-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-4">
                    <h6 class="text-muted mb-2">Admin</h6>
                    <h3 class="fw-bold mb-0 text-primary" id="stat_admins">--</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-4">
                    <h6 class="text-muted mb-2">ครู (Teacher)</h6>
                    <h3 class="fw-bold mb-0 text-success" id="stat_teachers">--</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-4">
                    <h6 class="text-muted mb-2">นักเรียน (Student)</h6>
                    <h3 class="fw-bold mb-0 text-info" id="stat_students">--</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-danger-subtle border-start border-danger border-4">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-danger mb-1">ระงับการใช้งาน</h6>
                        <h3 class="fw-bold mb-0" id="stat_suspended">--</h3>
                    </div>
                    <i class="bi bi-exclamation-octagon-fill fs-1 text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4 rounded-3">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="userSearch" class="form-control border-start-0 ps-0" placeholder="ค้นหาด้วย ชื่อผู้ใช้งาน หรือ ชื่อที่แสดง..." onkeyup="if(event.key==='Enter') loadUsers()">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="roleFilter" class="form-select border-1" onchange="loadUsers()">
                        <option value="">ทุกระดับสิทธิ์</option>
                        <option value="admin">ผู้ดูแลระบบ (Admin)</option>
                        <option value="teacher">ครู (Teacher)</option>
                        <option value="student">นักเรียน (Student)</option>
                        <option value="registrar">นายทะเบียน</option>
                        <option value="discipline">ฝ่ายปกครอง</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="statusFilter" class="form-select border-1" onchange="loadUsers()">
                        <option value="">ทุกสถานะ</option>
                        <option value="active">ปกติ</option>
                        <option value="suspended">ระงับการใช้งาน</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-light border w-100 fw-bold" onclick="loadUsers()">
                        <i class="bi bi-filter me-2"></i> กรองข้อมูล
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- User Table -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="usersTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-muted fw-bold small text-uppercase">ผู้ใช้งาน</th>
                        <th class="py-3 text-muted fw-bold small text-uppercase">ระดับสิทธิ์ (Role)</th>
                        <th class="py-3 text-muted fw-bold small text-uppercase">เข้าใชล่าสุด</th>
                        <th class="py-3 text-muted fw-bold small text-uppercase">วันที่สมัคร</th>
                        <th class="py-3 text-muted fw-bold small text-uppercase">สถานะ</th>
                        <th class="text-end pe-4 py-3 text-muted fw-bold small text-uppercase">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <!-- Data loaded via Ajax -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add/Edit User -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom p-4">
                <h5 class="modal-title fw-bold" id="userModalLabel">ข้อมูลผู้ใช้งาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="userForm">
                    <input type="hidden" id="admin_user_id">
                    
                    <div class="row mb-3">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold small">ชื่อที่แสดง (Display Name)</label>
                            <input type="text" class="form-control shadow-none" id="admin_display_name" placeholder="เช่น นายสมชาย ใจดี" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">ชื่อผู้ใช้ (Username)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">@</span>
                                <input type="text" class="form-control shadow-none border-start-0" id="admin_username" placeholder="username" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">รหัสผ่าน (Password)</label>
                            <input type="password" class="form-control shadow-none" id="admin_password" placeholder="อย่างน้อย 6 ตัวอักษร">
                            <small class="text-muted" id="passHelp">เว้นว่างไว้เพื่อใช้รหัสเดิม</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">ระดับสิทธิ์ (Role)</label>
                            <select class="form-select shadow-none" id="admin_role_key" required>
                                <option value="admin">Admin</option>
                                <option value="teacher">ครู (Teacher)</option>
                                <option value="student">นักเรียน (Student)</option>
                                <option value="registrar">นายทะเบียน</option>
                                <option value="discipline">ฝ่ายปกครอง</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">สถานะการใช้งาน</label>
                            <select class="form-select shadow-none" id="admin_is_suspended">
                                <option value="0">ปกติ (Active)</option>
                                <option value="1">ระงับ (Suspended)</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary px-4 fw-bold" onclick="saveUser()">
                    <i class="bi bi-save me-2"></i> บันทึกข้อมูล
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>

<style>
.avatar-role {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
}
.role-badge {
    padding: 0.4em 0.8em;
    font-size: 0.75rem;
    font-weight: 600;
}
</style>

<script>
$(document).ready(function() {
    loadUsers();
    loadStats();
});

function loadStats() {
    $.get(BASE_URL + '/api/admin/users.php?type=stats', function(res) {
        $('#stat_total').text(res.total || 0);
        $('#stat_admins').text(res.admins || 0);
        $('#stat_teachers').text(res.teachers || 0);
        $('#stat_students').text(res.students || 0);
        $('#stat_suspended').text(res.suspended || 0);
    });
}

function loadUsers() {
    const search = $('#userSearch').val();
    const role = $('#roleFilter').val();
    const status = $('#statusFilter').val();

    $.ajax({
        url: BASE_URL + '/api/admin/users.php',
        method: 'GET',
        data: { search, role, status },
        dataType: 'json',
        success: function(data) {
            let rows = '';
            if (data.length === 0) {
                rows = '<tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-inbox display-4 d-block mb-3 opacity-25"></i>ไม่พบข้อมูลผู้ใช้ที่ค้นหา</td></tr>';
            }
            data.forEach(function(u) {
                let roleColor = 'secondary';
                let roleIcon = 'person-fill';
                
                if (u.role === 'admin') { roleColor = 'primary'; roleIcon = 'shield-fill-check'; }
                else if (u.role === 'teacher') { roleColor = 'success'; roleIcon = 'person-badge-fill'; }
                else if (u.role === 'student') { roleColor = 'info'; roleIcon = 'mortarboard-fill'; }
                
                const statusBadge = u.is_suspended == 1 
                    ? '<span class="badge bg-danger-subtle text-danger role-badge"><i class="bi bi-x-circle me-1"></i>Suspended</span>' 
                    : '<span class="badge bg-success-subtle text-success role-badge"><i class="bi bi-check-circle me-1"></i>Active</span>';
                
                const lastLogin = u.last_login ? moment(u.last_login).calendar() : 'ยังไม่เคยเข้าใช้';
                const joinDate = u.created_at ? moment(u.created_at).format('DD MMM YYYY') : '--';
                
                rows += `
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-role bg-${roleColor}-subtle text-${roleColor} me-3 shadow-sm border border-${roleColor}">
                                <i class="bi bi-${roleIcon} fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">${u.display_name || u.username}</div>
                                <small class="text-muted"><i class="bi bi-at"></i>${u.username}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="text-capitalize small fw-bold text-${roleColor}">
                            ${u.role}
                        </div>
                    </td>
                    <td><small class="text-muted">${lastLogin}</small></td>
                    <td><small class="text-muted text-nowrap">${joinDate}</small></td>
                    <td>${statusBadge}</td>
                    <td class="text-end pe-4">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary" onclick="editUser(${u.id})" title="แก้ไข">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            ${u.role !== 'admin' ? `
                            <button class="btn btn-sm btn-outline-info" onclick="startImpersonating(${u.id})" title="สวมรอยสิทธิ์ (Login as)">
                                <i class="bi bi-incognito"></i>
                            </button>` : ''}
                        </div>
                    </td>
                </tr>`;
            });
            $('#usersTable tbody').html(rows);
        }
    });
}

function openUserModal(id = null) {
    $('#userForm')[0].reset();
    $('#admin_user_id').val('');
    $('#passHelp').hide();
    $('#userModalLabel').text(id ? 'แก้ไขข้อมูลผู้ใช้งาน' : 'เพิ่มผู้ใช้งานใหม่');
    
    if (id) {
        $('#passHelp').show();
        $.get(BASE_URL + '/api/admin/users.php?id=' + id, function(u) {
            $('#admin_user_id').val(u.id);
            $('#admin_username').val(u.username);
            $('#admin_display_name').val(u.display_name);
            $('#admin_role_key').val(u.role);
            $('#admin_is_suspended').val(u.is_suspended);
            new bootstrap.Modal($('#userModal')).show();
        });
    } else {
        new bootstrap.Modal($('#userModal')).show();
    }
}

function saveUser() {
    const data = {
        id: $('#admin_user_id').val(),
        username: $('#admin_username').val(),
        password: $('#admin_password').val(),
        display_name: $('#admin_display_name').val(),
        role: $('#admin_role_key').val(),
        is_suspended: $('#admin_is_suspended').val()
    };
    
    if (!data.id && !data.password) {
        showToast('กรุณาระบุรหัสผ่านสำหรับผู้ใช้ใหม่', 'warning');
        return;
    }
    
    const method = data.id ? 'PUT' : 'POST';
    
    $.ajax({
        url: BASE_URL + '/api/admin/users.php',
        method: method,
        data: JSON.stringify(data),
        contentType: 'application/json',
        success: function(res) {
            if (res.success) {
                showToast(res.message || 'สำเร็จ', 'success');
                bootstrap.Modal.getInstance($('#userModal')).hide();
                loadUsers();
                loadStats();
            } else {
                showToast(res.message || 'เกิดข้อผิดพลาด', 'error');
            }
        }
    });
}

function startImpersonating(id) {
    if (!confirm('คุณต้องการล็อกอินในนามของผู้นี้หรือไม่?')) return;
    $.post(BASE_URL + '/api/admin/impersonate.php', { user_id: id, action: 'start' }, function(res) {
        if (res.success) {
            window.location.href = BASE_URL + '/index.php';
        } else {
            showToast(res.message || 'ไม่สามารถสวมรอยสิทธิ์ได้', 'error');
        }
    });
}

function editUser(id) { openUserModal(id); }
</script>
