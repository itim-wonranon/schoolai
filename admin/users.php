<?php
require_once __DIR__ . '/../includes/session_check.php';
requireRole('admin');
$page_title = 'จัดการผู้ใช้งาน - Super Admin';
$current_page = 'admin_users';

require_once __DIR__ . '/../includes/layout_header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">จัดการผู้ใช้งานและสิทธิ์</h2>
            <p class="text-muted">บริหารจัดการบัญชีผู้ใช้ครู นักเรียน และกำหนดบทบาทในระบบ</p>
        </div>
        <button class="btn btn-primary shadow-sm" onclick="openUserModal()">
            <i class="bi bi-person-plus-fill me-2"></i> เพิ่มผู้ใช้งานใหม่
        </button>
    </div>

    <!-- Filters & Stats Area -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input type="text" id="userSearch" class="form-control" placeholder="ค้นหาชื่อหรือรหัสผู้ใช้...">
                        </div>
                        <div class="col-md-3">
                            <select id="roleFilter" class="form-control">
                                <option value="">ทุกระดับสิทธิ์</option>
                                <option value="admin">ผู้ดูแลระบบ</option>
                                <option value="registrar">นายทะเบียน</option>
                                <option value="discipline">ฝ่ายปกครอง</option>
                                <option value="teacher">ครู</option>
                                <option value="student">นักเรียน</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="statusFilter" class="form-control">
                                <option value="">ทุกสถานะ</option>
                                <option value="active">ปกติ</option>
                                <option value="suspended">ระงับการใช้งาน</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-secondary w-100" onclick="loadUsers()">กรอง</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
             <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="opacity-75">ผู้ใช้ที่ออนไลน์อยู่ขณะนี้</small>
                        <h3 class="mb-0 fw-bold" id="onlineCount">--</h3>
                    </div>
                    <i class="bi bi-broadcast fs-1 opacity-25"></i>
                </div>
             </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="usersTable">
                <thead class="bg-light text-muted">
                    <tr>
                        <th class="ps-4">ผู้ใช้งาน</th>
                        <th>ระดับสิทธิ์</th>
                        <th>เข้าใช้งานล่าสุด</th>
                        <th>สถานะ</th>
                        <th class="text-end pe-4">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">กำลังโหลดข้อมูลผู้ใช้...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="userModalLabel">ข้อมูลผู้ใช้งาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="userForm">
                    <input type="hidden" id="admin_user_id">
                    <div class="mb-3">
                        <label class="form-label">ชื่อผู้ใช้ (Username)</label>
                        <input type="text" class="form-control" id="admin_username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รหัสผ่าน (เว้นว่างไว้หากไม่ต้องการเปลี่ยน)</label>
                        <input type="password" class="form-control" id="admin_password" placeholder="รหัสผ่านใหม่">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อที่แสดง</label>
                        <input type="text" class="form-control" id="admin_display_name" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">ระดับสิทธิ์ (Role)</label>
                            <select class="form-control" id="admin_role_key" required>
                                <option value="admin">Admin</option>
                                <option value="registrar">นายทะเบียน</option>
                                <option value="discipline">ฝ่ายปกครอง</option>
                                <option value="teacher">ครู</option>
                                <option value="student">นักเรียน</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">สถานะ</label>
                            <select class="form-control" id="admin_is_suspended">
                                <option value="0">ปกติ (Active)</option>
                                <option value="1">ระงับ (Suspended)</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary px-4" onclick="saveUser()">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>

<script>
$(document).ready(function() {
    loadUsers();
    loadOnlineCount();
});

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
                rows = '<tr><td colspan="5" class="text-center py-5 text-muted">ไม่พบข้อมูลผู้ใช้</td></tr>';
            }
            data.forEach(function(u) {
                const statusBadge = u.is_suspended == 1 
                    ? '<span class="badge bg-danger-subtle text-danger">Suspended</span>' 
                    : '<span class="badge bg-success-subtle text-success">Active</span>';
                
                const lastLogin = u.last_login ? u.last_login : 'ยังไม่เคยเข้าใช้';
                
                rows += `
                <tr class="animate-fade-in">
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-light text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
                                <i class="bi bi-person-fill fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">${u.display_name || u.username}</div>
                                <small class="text-muted">@${u.username}</small>
                            </div>
                        </div>
                    </td>
                    <td><span class="text-capitalize">${u.role}</span></td>
                    <td><small class="text-muted">${lastLogin}</small></td>
                    <td>${statusBadge}</td>
                    <td class="text-end pe-4">
                        <div class="btn-group shadow-sm">
                            <button class="btn btn-sm btn-light" onclick="editUser(${u.id})" title="แก้ไขหรือระงับ"><i class="bi bi-pencil-square"></i></button>
                            ${u.role !== 'admin' ? `<button class="btn btn-sm btn-info text-white" onclick="startImpersonating(${u.id})" title="ล็อกอินในนามของ..."><i class="bi bi-eye"></i></button>` : ''}
                        </div>
                    </td>
                </tr>`;
            });
            $('#usersTable tbody').html(rows);
        }
    });
}

function loadOnlineCount() {
    $.get(BASE_URL + '/api/admin/sessions.php?query=count', function(res) {
        $('#onlineCount').text(res.count || 0);
    });
}

function openUserModal(id = null) {
    $('#userForm')[0].reset();
    $('#admin_user_id').val('');
    $('#userModalLabel').text(id ? 'แก้ไขข้อมูลผู้ใช้งาน' : 'เพิ่มผู้ใช้งานใหม่');
    
    if (id) {
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
            } else {
                showToast(res.message || 'เกิดข้อผิดพลาด', 'error');
            }
        }
    });
}

function startImpersonating(id) {
    if (!confirm('คุณต้องการล็อกอินในนามของผู้ใช้นี้ใช่หรือไม่? คุณจะเข้าสู่โหมดจำลองสิทธิ์ทันที')) return;
    
    $.post(BASE_URL + '/api/admin/impersonate.php', { user_id: id, action: 'start' }, function(res) {
        if (res.success) {
            window.location.href = BASE_URL + '/index.php';
        } else {
            showToast(res.message || 'ไม่สามารถจำลองสิทธิ์ได้', 'error');
        }
    });
}

function editUser(id) { openUserModal(id); }
</script>
