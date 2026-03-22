<?php
require_once __DIR__ . '/../includes/session_check.php';
requireRole('admin');
$page_title = 'ตั้งค่าระบบ - Super Admin';
$current_page = 'admin_settings';

require_once __DIR__ . '/../includes/layout_header.php';
?>

<div class="container-fluid">
    <div class="mb-4">
        <h2 class="fw-bold mb-1">ตั้งค่าระบบ (System Settings)</h2>
        <p class="text-muted">กำหนดค่าพื้นฐานและโหมดการทำงานของระบบบริหารจัดการ</p>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">ข้อมูลทั่วไป</h5>
                </div>
                <div class="card-body p-4">
                    <form id="settingsForm">
                        <div class="mb-3">
                            <label class="form-label">ชื่อโรงเรียน (School Name)</label>
                            <input type="text" class="form-control" name="school_name" id="set_school_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">โหมดปิดปรับปรุง (Maintenance Mode)</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="maintenance_mode" id="set_maintenance_mode">
                                <label class="form-check-label" for="set_maintenance_mode">เปิดใช้งาน (ผู้ใช้ทั่วไปจะไม่สามารถเข้าถึงระบบได้)</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">อนุญาตให้เข้าสู่ระบบ (Allow Login)</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="allow_login" id="set_allow_login" checked>
                                <label class="form-check-label" for="set_allow_login">คุณครูและนักเรียนสามารถ Login ได้ตามปกติ</label>
                            </div>
                        </div>
                        <hr class="my-4">
                        <button type="submit" class="btn btn-primary px-5 py-2">
                            <i class="bi bi-save me-2"></i> บันทึกการตั้งค่าทั้งหมด
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 bg-light border-start border-4 border-info">
                    <h6 class="fw-bold mb-2">Note:</h6>
                    <small class="text-muted">การตั้งค่าเหล่านี้จะมีผลกับผู้ใช้งานทุกคนในระบบทันทีที่กดบันทึก</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>

<script>
$(document).ready(function() {
    loadSettings();

    $('#settingsForm').on('submit', function(e) {
        e.preventDefault();
        const data = {
            school_name: $('#set_school_name').val(),
            maintenance_mode: $('#set_maintenance_mode').is(':checked') ? '1' : '0',
            allow_login: $('#set_allow_login').is(':checked') ? '1' : '0'
        };
        saveSettings(data);
    });
});

function loadSettings() {
    $.get(BASE_URL + '/api/admin/settings.php', function(data) {
        $('#set_school_name').val(data.school_name || '');
        $('#set_maintenance_mode').prop('checked', data.maintenance_mode === '1');
        $('#set_allow_login').prop('checked', data.allow_login === '1');
    });
}

function saveSettings(data) {
    $.ajax({
        url: BASE_URL + '/api/admin/settings.php',
        method: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        success: function(res) {
            if (res.success) {
                showToast('บันทึกการตั้งค่าสำเร็จ', 'success');
                // Optional: Update page title or logo locally
                $('.navbar-brand-text').html('<i class="bi bi-mortarboard-fill"></i> ' + data.school_name);
            } else {
                showToast(res.message, 'error');
            }
        }
    });
}
</script>
