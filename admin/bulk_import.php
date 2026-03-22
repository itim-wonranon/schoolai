<?php
require_once __DIR__ . '/../includes/session_check.php';
requireRole('admin');
$page_title = 'นำเข้าข้อมูลจำนวนมาก - Super Admin';
$current_page = 'admin_import';

require_once __DIR__ . '/../includes/layout_header.php';
?>

<div class="container-fluid">
    <div class="mb-4">
        <h2 class="fw-bold mb-1">นำเข้าข้อมูลจำนวนมาก (Bulk Import)</h2>
        <p class="text-muted">อัปโหลดไฟล์ Excel/CSV เพื่อเพิ่มข้อมูลครูหรือนักเรียนเข้าสู่ระบบพร้อมกัน</p>
    </div>

    <div class="row">
        <!-- Import Form -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <form id="importForm" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-bold">ประเภทข้อมูลที่ต้องการนำเข้า</label>
                            <div class="row g-3">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="import_type" id="import_teachers" value="teachers" checked>
                                    <label class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center" for="import_teachers">
                                        <i class="bi bi-person-badge fs-2 mb-2"></i>
                                        <span>ข้อมูลคุณครู</span>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="import_type" id="import_students" value="students">
                                    <label class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center" for="import_students">
                                        <i class="bi bi-people fs-2 mb-2"></i>
                                        <span>ข้อมูลนักเรียน</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">อัปโหลดไฟล์ (เฉพาะ .csv)</label>
                            <div class="upload-area border-dashed border-2 rounded-3 p-5 text-center bg-light" id="dropArea">
                                <i class="bi bi-cloud-arrow-up fs-1 text-primary mb-3"></i>
                                <h5>เลือกไฟล์หรือลากมาวางที่นี่</h5>
                                <p class="text-muted small">รองรับไฟล์ CSV เท่านั้น (UTF-8)</p>
                                <input type="file" name="import_file" id="fileInput" class="d-none" accept=".csv">
                                <button type="button" class="btn btn-primary px-4" onclick="$('#fileInput').click()">เลือกไฟล์จากเครื่อง</button>
                                <div id="selectedFileName" class="mt-3 fw-bold text-success d-none"></div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 shadow-sm" id="btnSubmit" disabled>
                            <i class="bi bi-check-circle-fill me-2"></i> เริ่มการนำเข้าข้อมูล
                        </button>
                    </form>
                </div>
            </div>

            <!-- Import Results (Hidden by default) -->
            <div id="importResults" class="card border-0 shadow-sm d-none">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">ผลการนำเข้า</h5>
                    <span id="resultSummary" class="badge bg-info"></span>
                </div>
                <div class="card-body">
                    <div class="alert id="resultAlert" class="mb-3 d-none"></div>
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-sm table-hover" id="resultTable">
                            <thead>
                                <tr>
                                    <th>แถวที่</th>
                                    <th>หัวข้อ</th>
                                    <th>สถานะ</th>
                                    <th>รายละเอียด</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar / Tips -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 text-center">
                    <h5 class="fw-bold mb-3"><i class="bi bi-file-earmark-arrow-down me-2"></i> ดาวน์โหลดเทมเพลต</h5>
                    <p class="text-muted small">กรุณาใช้ไฟล์เทมเพลตด้านล่างในการกรอกข้อมูลเพื่อให้ระบบตรวจสอบได้ถูกต้อง</p>
                    <div class="d-grid gap-2">
                        <a href="<?php echo $base_url; ?>/assets/templates/teacher_import_example.csv" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-filetype-csv me-2"></i> Template สำหรับคุณครู
                        </a>
                        <a href="<?php echo $base_url; ?>/assets/templates/student_import_example.csv" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-filetype-csv me-2"></i> Template สำหรับนักเรียน
                        </a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm border-start border-4 border-warning">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-warning mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i> ข้อควรระวัง</h6>
                    <ul class="text-muted small ps-3">
                        <li class="mb-2"><strong>รหัสประจำตัว:</strong> ต้องไม่ซ้ำกับข้อมูลที่มีอยู่ในระบบ</li>
                        <li class="mb-2"><strong>รูปแบบไฟล์:</strong> ต้องบันทึกเป็น CSV UTF-8 เพื่อรองรับภาษาไทย</li>
                        <li class="mb-2"><strong>ข้อมูลบังคับ:</strong> ชื่อ-นามสกุล และรหัสผ่านพื้นฐานต้องมีครบ</li>
                        <li><strong>ขนาดไฟล์:</strong> ไม่ควรเกิน 5MB ต่อการอัปโหลด 1 ครั้ง</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>

<style>
.upload-area {
    border-style: dashed !important;
    transition: all 0.2s ease;
}
.upload-area.dragover {
    background-color: #eef2ff !important;
    border-color: #4f46e5 !important;
}
</style>

<script>
$(document).ready(function() {
    const dropArea = $('#dropArea');
    const fileInput = $('#fileInput');

    fileInput.on('change', function() {
        if (this.files.length > 0) {
            $('#selectedFileName').text('ไฟล์ที่เลือก: ' + this.files[0].name).removeClass('d-none');
            $('#btnSubmit').prop('disabled', false);
        }
    });

    dropArea.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });

    dropArea.on('dragleave', function() {
        $(this).removeClass('dragover');
    });

    dropArea.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            fileInput[0].files = files;
            fileInput.trigger('change');
        }
    });

    $('#importForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const btn = $('#btnSubmit');
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> กำลังประมวลผล...');
        $('#importResults').addClass('d-none');
        
        $.ajax({
            url: BASE_URL + '/api/admin/bulk_import.php',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-2"></i> เริ่มการนำเข้าข้อมูล');
                $('#importResults').removeClass('d-none');
                
                let rows = '';
                let successCount = 0;
                let failCount = 0;

                res.details.forEach(function(item) {
                    if (item.status === 'success') successCount++; else failCount++;
                    const statusClass = item.status === 'success' ? 'badge bg-success' : 'badge bg-danger';
                    rows += `
                    <tr>
                        <td>${item.row}</td>
                        <td>${item.identifier}</td>
                        <td><span class="${statusClass}">${item.status}</span></td>
                        <td class="small">${item.message}</td>
                    </tr>`;
                });

                $('#resultTable tbody').html(rows);
                $('#resultSummary').text(`สำเร็จ: ${successCount} / ล้มเหลว: ${failCount}`);
                
                if (failCount === 0) {
                    showToast('นำเข้าข้อมูลสำเร็จทั้งหมด', 'success');
                } else {
                    showToast(`พบข้อผิดพลาด ${failCount} รายการ`, 'warning');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-2"></i> เริ่มการนำเข้าข้อมูล');
                showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
            }
        });
    });
});
</script>
