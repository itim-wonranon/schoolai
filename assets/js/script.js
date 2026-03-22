/* ============================================
   School Management System - Master JavaScript
   ============================================ */

$(document).ready(function () {

    // ========== SIDEBAR ==========
    // Toggle sidebar on mobile
    $('#sidebarToggleBtn').on('click', function () {
        $('#sidebar').toggleClass('show');
        $('#sidebarOverlay').toggleClass('show');
    });

    $('#sidebarOverlay').on('click', function () {
        $('#sidebar').removeClass('show');
        $(this).removeClass('show');
    });

    // Submenu toggle
    $('.submenu-toggle').on('click', function (e) {
        e.preventDefault();
        $(this).closest('.has-submenu').toggleClass('open');
    });

    // ========== TOAST NOTIFICATIONS ==========
    window.showToast = function (message, type = 'success') {
        const icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill' };
        const toast = $(`<div class="alert-toast ${type}"><i class="bi ${icons[type] || icons.success}"></i> ${message}</div>`);
        $('body').append(toast);
        setTimeout(() => toast.fadeOut(400, function(){ $(this).remove(); }), 3000);
    };

    // ========== GENERIC CRUD HELPERS ==========
    window.loadData = function (url, callback) {
        $.ajax({
            url: BASE_URL + '/api/' + url,
            method: 'GET',
            dataType: 'json',
            success: callback,
            error: function (xhr) {
                console.error('Load error:', xhr.responseText);
                showToast('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
            }
        });
    };

    window.saveData = function (url, data, method, callback) {
        $.ajax({
            url: BASE_URL + '/api/' + url,
            method: method,
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    showToast(res.message || 'สำเร็จ!', 'success');
                    if (callback) callback(res);
                } else {
                    showToast(res.message || 'เกิดข้อผิดพลาด', 'error');
                }
            },
            error: function (xhr) {
                console.error('Save error:', xhr.responseText);
                showToast('เกิดข้อผิดพลาดในการบันทึก', 'error');
            }
        });
    };

    window.deleteData = function (url, id, callback) {
        if (!confirm('คุณต้องการลบข้อมูลนี้ใช่หรือไม่?')) return;
        $.ajax({
            url: BASE_URL + '/api/' + url + '?id=' + id,
            method: 'DELETE',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    showToast(res.message || 'ลบสำเร็จ!', 'success');
                    if (callback) callback(res);
                } else {
                    showToast(res.message || 'ไม่สามารถลบได้', 'error');
                }
            },
            error: function (xhr) {
                console.error('Delete error:', xhr.responseText);
                showToast('เกิดข้อผิดพลาดในการลบ', 'error');
            }
        });
    };

    // Definitions are moved up...

    window.loadTeachers = function () {
        loadData('teachers.php', function (data) {
            let rows = '';
            if (data.length === 0) {
                rows = '<tr><td colspan="6" class="text-center py-4 text-muted">ไม่มีข้อมูล</td></tr>';
            }
            data.forEach(function (t, i) {
                rows += `<tr class="animate-fade-in">
                    <td>${i + 1}</td>
                    <td><strong>${t.teacher_code}</strong></td>
                    <td>${t.first_name} ${t.last_name}</td>
                    <td>${t.phone || '-'}</td>
                    <td>${t.department || '-'}</td>
                    <td>
                        <button class="btn-icon btn-edit" onclick="editTeacher(${t.id})" title="แก้ไข"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn-icon btn-delete" onclick="removeTeacher(${t.id})" title="ลบ"><i class="bi bi-trash3"></i></button>
                    </td>
                </tr>`;
            });
            $('#teachersTable tbody').html(rows);
        });
    };

    window.openTeacherModal = function (id = null) {
        $('#teacherForm')[0].reset();
        $('#teacher_id').val('');
        $('#teacherModalLabel').text(id ? 'แก้ไขข้อมูลครู' : 'เพิ่มข้อมูลครู');
        if (id) {
            loadData('teachers.php?id=' + id, function (t) {
                $('#teacher_id').val(t.id);
                $('#teacher_code').val(t.teacher_code);
                $('#teacher_first_name').val(t.first_name);
                $('#teacher_last_name').val(t.last_name);
                $('#teacher_phone').val(t.phone);
                $('#teacher_department').val(t.department);
            });
        }
        new bootstrap.Modal($('#teacherModal')).show();
    };

    window.editTeacher = function (id) { openTeacherModal(id); };

    window.saveTeacher = function () {
        const data = {
            id: $('#teacher_id').val(),
            teacher_code: $('#teacher_code').val(),
            first_name: $('#teacher_first_name').val(),
            last_name: $('#teacher_last_name').val(),
            phone: $('#teacher_phone').val(),
            department: $('#teacher_department').val()
        };
        const method = data.id ? 'PUT' : 'POST';
        saveData('teachers.php', data, method, function () {
            bootstrap.Modal.getInstance($('#teacherModal')).hide();
            loadTeachers();
        });
    };

    window.removeTeacher = function (id) {
        deleteData('teachers.php', id, loadTeachers);
    };


    window.loadStudents = function () {
        loadData('students.php', function (data) {
            let rows = '';
            if (data.length === 0) {
                rows = '<tr><td colspan="7" class="text-center py-4 text-muted">ไม่มีข้อมูล</td></tr>';
            }
            data.forEach(function (s, i) {
                rows += `<tr class="animate-fade-in">
                    <td>${i + 1}</td>
                    <td><strong>${s.student_code}</strong></td>
                    <td>${s.first_name} ${s.last_name}</td>
                    <td>${s.birthdate || '-'}</td>
                    <td>${s.class_name || '-'}</td>
                    <td>
                        <button class="btn-icon btn-edit" onclick="editStudent(${s.id})" title="แก้ไข"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn-icon btn-delete" onclick="removeStudent(${s.id})" title="ลบ"><i class="bi bi-trash3"></i></button>
                    </td>
                </tr>`;
            });
            $('#studentsTable tbody').html(rows);
        });
    };

    window.loadClassOptions = function (selector) {
        loadData('classes.php', function (data) {
            let opts = '<option value="">-- เลือกชั้นเรียน --</option>';
            data.forEach(function (c) {
                opts += `<option value="${c.id}">${c.class_name}</option>`;
            });
            $(selector).html(opts);
        });
    };

    window.openStudentModal = function (id = null) {
        $('#studentForm')[0].reset();
        $('#student_id').val('');
        $('#studentModalLabel').text(id ? 'แก้ไขข้อมูลนักเรียน' : 'เพิ่มข้อมูลนักเรียน');
        if (id) {
            loadData('students.php?id=' + id, function (s) {
                $('#student_id').val(s.id);
                $('#student_code').val(s.student_code);
                $('#student_first_name').val(s.first_name);
                $('#student_last_name').val(s.last_name);
                $('#student_birthdate').val(s.birthdate);
                $('#student_class_id').val(s.class_id);
            });
        }
        new bootstrap.Modal($('#studentModal')).show();
    };

    window.editStudent = function (id) { openStudentModal(id); };

    window.saveStudent = function () {
        const data = {
            id: $('#student_id').val(),
            student_code: $('#student_code').val(),
            first_name: $('#student_first_name').val(),
            last_name: $('#student_last_name').val(),
            birthdate: $('#student_birthdate').val(),
            class_id: $('#student_class_id').val()
        };
        const method = data.id ? 'PUT' : 'POST';
        saveData('students.php', data, method, function () {
            bootstrap.Modal.getInstance($('#studentModal')).hide();
            loadStudents();
        });
    };

    window.removeStudent = function (id) {
        deleteData('students.php', id, loadStudents);
    };


    window.loadSubjects = function () {
        loadData('subjects.php', function (data) {
            let rows = '';
            if (data.length === 0) {
                rows = '<tr><td colspan="5" class="text-center py-4 text-muted">ไม่มีข้อมูล</td></tr>';
            }
            data.forEach(function (s, i) {
                rows += `<tr class="animate-fade-in">
                    <td>${i + 1}</td>
                    <td><strong>${s.subject_code}</strong></td>
                    <td>${s.subject_name}</td>
                    <td>${s.credits}</td>
                    <td>
                        <button class="btn-icon btn-edit" onclick="editSubject(${s.id})" title="แก้ไข"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn-icon btn-delete" onclick="removeSubject(${s.id})" title="ลบ"><i class="bi bi-trash3"></i></button>
                    </td>
                </tr>`;
            });
            $('#subjectsTable tbody').html(rows);
        });
    };

    window.openSubjectModal = function (id = null) {
        $('#subjectForm')[0].reset();
        $('#subject_id').val('');
        $('#subjectModalLabel').text(id ? 'แก้ไขข้อมูลรายวิชา' : 'เพิ่มข้อมูลรายวิชา');
        if (id) {
            loadData('subjects.php?id=' + id, function (s) {
                $('#subject_id').val(s.id);
                $('#subject_code').val(s.subject_code);
                $('#subject_name').val(s.subject_name);
                $('#subject_credits').val(s.credits);
            });
        }
        new bootstrap.Modal($('#subjectModal')).show();
    };

    window.editSubject = function (id) { openSubjectModal(id); };

    window.saveSubject = function () {
        const data = {
            id: $('#subject_id').val(),
            subject_code: $('#subject_code').val(),
            subject_name: $('#subject_name').val(),
            credits: $('#subject_credits').val()
        };
        const method = data.id ? 'PUT' : 'POST';
        saveData('subjects.php', data, method, function () {
            bootstrap.Modal.getInstance($('#subjectModal')).hide();
            loadSubjects();
        });
    };

    window.removeSubject = function (id) {
        deleteData('subjects.php', id, loadSubjects);
    };


    window.loadClasses = function () {
        loadData('classes.php', function (data) {
            let rows = '';
            if (data.length === 0) {
                rows = '<tr><td colspan="4" class="text-center py-4 text-muted">ไม่มีข้อมูล</td></tr>';
            }
            data.forEach(function (c, i) {
                rows += `<tr class="animate-fade-in">
                    <td>${i + 1}</td>
                    <td><strong>${c.class_code}</strong></td>
                    <td>${c.class_name}</td>
                    <td>
                        <button class="btn-icon btn-edit" onclick="editClass(${c.id})" title="แก้ไข"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn-icon btn-delete" onclick="removeClass(${c.id})" title="ลบ"><i class="bi bi-trash3"></i></button>
                    </td>
                </tr>`;
            });
            $('#classesTable tbody').html(rows);
        });
    };

    window.openClassModal = function (id = null) {
        $('#classForm')[0].reset();
        $('#class_id').val('');
        $('#classModalLabel').text(id ? 'แก้ไขข้อมูลชั้นเรียน' : 'เพิ่มข้อมูลชั้นเรียน');
        if (id) {
            loadData('classes.php?id=' + id, function (c) {
                $('#class_id').val(c.id);
                $('#class_code').val(c.class_code);
                $('#class_name').val(c.class_name);
            });
        }
        new bootstrap.Modal($('#classModal')).show();
    };

    window.editClass = function (id) { openClassModal(id); };

    window.saveClass = function () {
        const data = {
            id: $('#class_id').val(),
            class_code: $('#class_code').val(),
            class_name: $('#class_name').val()
        };
        const method = data.id ? 'PUT' : 'POST';
        saveData('classes.php', data, method, function () {
            bootstrap.Modal.getInstance($('#classModal')).hide();
            loadClasses();
        });
    };

    window.removeClass = function (id) {
        deleteData('classes.php', id, loadClasses);
    };


    window.loadClassrooms = function () {
        loadData('classrooms.php', function (data) {
            let rows = '';
            if (data.length === 0) {
                rows = '<tr><td colspan="4" class="text-center py-4 text-muted">ไม่มีข้อมูล</td></tr>';
            }
            data.forEach(function (r, i) {
                rows += `<tr class="animate-fade-in">
                    <td>${i + 1}</td>
                    <td><strong>${r.room_code}</strong></td>
                    <td>${r.room_name}</td>
                    <td>
                        <button class="btn-icon btn-edit" onclick="editClassroom(${r.id})" title="แก้ไข"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn-icon btn-delete" onclick="removeClassroom(${r.id})" title="ลบ"><i class="bi bi-trash3"></i></button>
                    </td>
                </tr>`;
            });
            $('#classroomsTable tbody').html(rows);
        });
    };

    window.openClassroomModal = function (id = null) {
        $('#classroomForm')[0].reset();
        $('#classroom_id').val('');
        $('#classroomModalLabel').text(id ? 'แก้ไขข้อมูลห้องเรียน' : 'เพิ่มข้อมูลห้องเรียน');
        if (id) {
            loadData('classrooms.php?id=' + id, function (r) {
                $('#classroom_id').val(r.id);
                $('#room_code').val(r.room_code);
                $('#room_name').val(r.room_name);
            });
        }
        new bootstrap.Modal($('#classroomModal')).show();
    };

    window.editClassroom = function (id) { openClassroomModal(id); };

    window.saveClassroom = function () {
        const data = {
            id: $('#classroom_id').val(),
            room_code: $('#room_code').val(),
            room_name: $('#room_name').val()
        };
        const method = data.id ? 'PUT' : 'POST';
        saveData('classrooms.php', data, method, function () {
            bootstrap.Modal.getInstance($('#classroomModal')).hide();
            loadClassrooms();
        });
    };

    window.removeClassroom = function (id) {
        deleteData('classrooms.php', id, loadClassrooms);
    };


    window.loadDropdownOptions = function () {
        loadData('subjects.php', function (data) {
            let opts = '<option value="">-- เลือกรายวิชา --</option>';
            data.forEach(d => opts += `<option value="${d.id}">${d.subject_code} - ${d.subject_name}</option>`);
            $('#schedule_subject_id').html(opts);
        });
        loadData('teachers.php', function (data) {
            let opts = '<option value="">-- เลือกครู --</option>';
            data.forEach(d => opts += `<option value="${d.id}">${d.teacher_code} - ${d.first_name} ${d.last_name}</option>`);
            $('#schedule_teacher_id').html(opts);
        });
        loadData('classes.php', function (data) {
            let opts = '<option value="">-- เลือกชั้นเรียน --</option>';
            data.forEach(d => opts += `<option value="${d.id}">${d.class_name}</option>`);
            $('#schedule_class_id').html(opts);
        });
        loadData('classrooms.php', function (data) {
            let opts = '<option value="">-- เลือกห้องเรียน --</option>';
            data.forEach(d => opts += `<option value="${d.id}">${d.room_name}</option>`);
            $('#schedule_classroom_id').html(opts);
        });
    };

    window.loadSchedules = function () {
        loadData('schedules.php', function (data) {
            let rows = '';
            if (data.length === 0) {
                rows = '<tr><td colspan="8" class="text-center py-4 text-muted">ไม่มีข้อมูล</td></tr>';
            }
            const dayMap = { Monday: 'จันทร์', Tuesday: 'อังคาร', Wednesday: 'พุธ', Thursday: 'พฤหัสบดี', Friday: 'ศุกร์' };
            data.forEach(function (s, i) {
                rows += `<tr class="animate-fade-in">
                    <td>${i + 1}</td>
                    <td>${s.subject_name}</td>
                    <td>${s.teacher_name}</td>
                    <td>${s.class_name}</td>
                    <td>${s.room_name}</td>
                    <td>${dayMap[s.day_of_week] || s.day_of_week}</td>
                    <td>${s.start_time} - ${s.end_time}</td>
                    <td>
                        <button class="btn-icon btn-edit" onclick="editSchedule(${s.id})" title="แก้ไข"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn-icon btn-delete" onclick="removeSchedule(${s.id})" title="ลบ"><i class="bi bi-trash3"></i></button>
                    </td>
                </tr>`;
            });
            $('#schedulesTable tbody').html(rows);
        });
    };

    window.openScheduleModal = function (id = null) {
        $('#scheduleForm')[0].reset();
        $('#schedule_id').val('');
        $('#scheduleModalLabel').text(id ? 'แก้ไขตารางเรียน' : 'เพิ่มตารางเรียน');
        if (id) {
            loadData('schedules.php?id=' + id, function (s) {
                $('#schedule_id').val(s.id);
                $('#schedule_subject_id').val(s.subject_id);
                $('#schedule_teacher_id').val(s.teacher_id);
                $('#schedule_class_id').val(s.class_id);
                $('#schedule_classroom_id').val(s.classroom_id);
                $('#schedule_day').val(s.day_of_week);
                $('#schedule_start_time').val(s.start_time);
                $('#schedule_end_time').val(s.end_time);
            });
        }
        new bootstrap.Modal($('#scheduleModal')).show();
    };

    window.editSchedule = function (id) { openScheduleModal(id); };

    window.saveSchedule = function () {
        const data = {
            id: $('#schedule_id').val(),
            subject_id: $('#schedule_subject_id').val(),
            teacher_id: $('#schedule_teacher_id').val(),
            class_id: $('#schedule_class_id').val(),
            classroom_id: $('#schedule_classroom_id').val(),
            day_of_week: $('#schedule_day').val(),
            start_time: $('#schedule_start_time').val(),
            end_time: $('#schedule_end_time').val()
        };
        const method = data.id ? 'PUT' : 'POST';
        saveData('schedules.php', data, method, function () {
            bootstrap.Modal.getInstance($('#scheduleModal')).hide();
            loadSchedules();
        });
    };

    window.removeSchedule = function (id) {
        deleteData('schedules.php', id, loadSchedules);
    };


    window.loadGradeStudents = function () {
        const subjectId = $('#gradeFilterSubject').val();
        const classId = $('#gradeFilterClass').val();
        if (!subjectId || !classId) {
            showToast('กรุณาเลือกรายวิชาและชั้นเรียน', 'warning');
            return;
        }
        loadData('grades.php?subject_id=' + subjectId + '&class_id=' + classId, function (data) {
            let rows = '';
            if (data.length === 0) {
                rows = '<tr><td colspan="5" class="text-center py-4 text-muted">ไม่มีนักเรียนในชั้นเรียนนี้</td></tr>';
            }
            data.forEach(function (s, i) {
                const gradeClass = s.grade !== null ? 'grade-' + s.grade : '';
                const gradeDisplay = s.grade !== null ? `<span class="grade-badge ${gradeClass}">${s.grade}</span>` : '-';
                rows += `<tr>
                    <td>${i + 1}</td>
                    <td>${s.student_code}</td>
                    <td>${s.first_name} ${s.last_name}</td>
                    <td><input type="number" class="score-input" min="0" max="100"
                        data-student-id="${s.student_id}" value="${s.score !== null ? s.score : ''}"
                        onchange="autoCalcGrade(this)" placeholder="0-100"></td>
                    <td class="grade-cell" id="grade-${s.student_id}">${gradeDisplay}</td>
                </tr>`;
            });
            $('#gradeStudentsTable tbody').html(rows);
            $('#gradeStudentsTable').closest('.card').show();
        });
    };

    window.autoCalcGrade = function (input) {
        const score = parseFloat($(input).val());
        const studentId = $(input).data('student-id');
        let grade = '-';
        let gradeClass = '';
        if (!isNaN(score) && score >= 0 && score <= 100) {
            if (score >= 80) { grade = '4'; gradeClass = 'grade-4'; }
            else if (score >= 70) { grade = '3'; gradeClass = 'grade-3'; }
            else if (score >= 60) { grade = '2'; gradeClass = 'grade-2'; }
            else if (score >= 50) { grade = '1'; gradeClass = 'grade-1'; }
            else { grade = '0'; gradeClass = 'grade-0'; }
            $(`#grade-${studentId}`).html(`<span class="grade-badge ${gradeClass}">${grade}</span>`);
        } else {
            $(`#grade-${studentId}`).html('-');
        }
    };

    window.saveAllGrades = function () {
        const subjectId = $('#gradeFilterSubject').val();
        const grades = [];
        $('.score-input').each(function () {
            const score = $(this).val();
            if (score !== '') {
                grades.push({
                    student_id: $(this).data('student-id'),
                    score: parseFloat(score)
                });
            }
        });
        if (grades.length === 0) {
            showToast('กรุณากรอกคะแนนอย่างน้อย 1 คน', 'warning');
            return;
        }
        saveData('grades.php', { subject_id: subjectId, grades: grades }, 'POST', function () {
            loadGradeStudents();
        });
    };


    window.loadAttendanceSchedules = function () {
        loadData('attendance.php?action=schedules', function (data) {
            let opts = '<option value="">-- เลือกคาบเรียน --</option>';
            data.forEach(function (s) {
                const dayMap = { Monday: 'จันทร์', Tuesday: 'อังคาร', Wednesday: 'พุธ', Thursday: 'พฤหัสบดี', Friday: 'ศุกร์' };
                const day = dayMap[s.day_of_week] || s.day_of_week;
                opts += `<option value="${s.id}">${s.subject_name} - ${s.class_name} (${day} ${s.start_time}-${s.end_time})</option>`;
            });
            $('#attendanceFilterSchedule').html(opts);
        });
    };

    window.loadAttendanceList = function () {
        const scheduleId = $('#attendanceFilterSchedule').val();
        const date = $('#attendanceDate').val();
        if (!scheduleId || !date) {
            showToast('กรุณาเลือกคาบเรียนและวันที่', 'warning');
            return;
        }
        loadData('attendance.php?schedule_id=' + scheduleId + '&date=' + date, function (data) {
            let rows = '';
            if (data.length === 0) {
                rows = '<tr><td colspan="4" class="text-center py-4 text-muted">ไม่มีนักเรียนในรายการนี้</td></tr>';
            }
            const statusLabels = {
                present: '<i class="bi bi-check-circle-fill"></i> มาเรียน',
                absent: '<i class="bi bi-x-circle-fill"></i> ขาด',
                late: '<i class="bi bi-clock-fill"></i> สาย',
                leave: '<i class="bi bi-envelope-fill"></i> ลา'
            };
            const statusOrder = ['present', 'absent', 'late', 'leave'];
            data.forEach(function (s, i) {
                const currentStatus = s.status || 'present';
                rows += `<tr>
                    <td>${i + 1}</td>
                    <td>${s.student_code}</td>
                    <td>${s.first_name} ${s.last_name}</td>
                    <td>
                        <span class="attendance-status status-${currentStatus}"
                              data-student-id="${s.student_id}"
                              data-schedule-id="${scheduleId}"
                              data-date="${date}"
                              data-status="${currentStatus}"
                              onclick="toggleAttendance(this)">
                              ${statusLabels[currentStatus]}
                        </span>
                    </td>
                </tr>`;
            });
            $('#attendanceTable tbody').html(rows);
            $('#attendanceTable').closest('.card').show();
        });
    };

    window.toggleAttendance = function (el) {
        const $el = $(el);
        const statusOrder = ['present', 'absent', 'late', 'leave'];
        const statusLabels = {
            present: '<i class="bi bi-check-circle-fill"></i> มาเรียน',
            absent: '<i class="bi bi-x-circle-fill"></i> ขาด',
            late: '<i class="bi bi-clock-fill"></i> สาย',
            leave: '<i class="bi bi-envelope-fill"></i> ลา'
        };
        let current = $el.data('status');
        let idx = statusOrder.indexOf(current);
        let next = statusOrder[(idx + 1) % statusOrder.length];

        const data = {
            student_id: $el.data('student-id'),
            schedule_id: $el.data('schedule-id'),
            date: $el.data('date'),
            status: next
        };

        $.ajax({
            url: BASE_URL + '/api/attendance.php',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $el.data('status', next);
                    $el.removeClass('status-present status-absent status-late status-leave')
                       .addClass('status-' + next)
                       .html(statusLabels[next]);
                }
            },
            error: function () {
                showToast('เกิดข้อผิดพลาด', 'error');
            }
        });
    };


    // ========== DASHBOARD MODULE ==========
    window.loadDashboard = function () {
        loadData('dashboard.php', function (data) {
            if (data.stats) {
                $('#statTeachers').text(data.stats.teachers);
                $('#statStudents').text(data.stats.students);
                $('#statSubjects').text(data.stats.subjects);
                $('#statClassrooms').text(data.stats.classrooms);
            }

            // Attendance Chart
            if (data.attendance && $('#attendanceChart').length) {
                const ctx = document.getElementById('attendanceChart').getContext('2d');
                if (window.attendanceChartInstance) window.attendanceChartInstance.destroy();
                window.attendanceChartInstance = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['มาเรียน', 'ขาด', 'สาย', 'ลา'],
                        datasets: [{
                            data: [data.attendance.present, data.attendance.absent, data.attendance.late, data.attendance.leave],
                            backgroundColor: ['#10B981', '#EF4444', '#F59E0B', '#3B82F6'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 16, font: { family: "'Sarabun', sans-serif" } } }
                        }
                    }
                });
            }

            // Grade Distribution Chart
            if (data.grades && $('#gradeChart').length) {
                const ctx2 = document.getElementById('gradeChart').getContext('2d');
                if (window.gradeChartInstance) window.gradeChartInstance.destroy();
                window.gradeChartInstance = new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: ['เกรด 4', 'เกรด 3', 'เกรด 2', 'เกรด 1', 'เกรด 0'],
                        datasets: [{
                            label: 'จำนวนนักเรียน',
                            data: [data.grades.grade4, data.grades.grade3, data.grades.grade2, data.grades.grade1, data.grades.grade0],
                            backgroundColor: ['#10B981', '#3B82F6', '#F59E0B', '#F97316', '#EF4444'],
                            borderRadius: 8,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1, font: { family: "'Sarabun', sans-serif" } } },
                            x: { ticks: { font: { family: "'Sarabun', sans-serif" } } }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        });
    };

    // ========== INITIALIZATION (AUTO-LOAD) ==========
    if ($('#teachersTable').length) loadTeachers();

    if ($('#studentsTable').length) {
        loadStudents();
        loadClassOptions('#student_class_id');
    }

    if ($('#subjectsTable').length) loadSubjects();
    if ($('#classesTable').length) loadClasses();
    if ($('#classroomsTable').length) loadClassrooms();

    if ($('#schedulesTable').length) {
        loadDropdownOptions();
        loadSchedules();
    }

    if ($('#gradeFilterSubject').length) {
        loadData('subjects.php', function (data) {
            let opts = '<option value="">-- เลือกรายวิชา --</option>';
            data.forEach(d => opts += `<option value="${d.id}">${d.subject_code} - ${d.subject_name}</option>`);
            $('#gradeFilterSubject').html(opts);
        });
        loadData('classes.php', function (data) {
            let opts = '<option value="">-- เลือกชั้นเรียน --</option>';
            data.forEach(d => opts += `<option value="${d.id}">${d.class_name}</option>`);
            $('#gradeFilterClass').html(opts);
        });
    }

    if ($('#attendanceFilterSchedule').length) loadAttendanceSchedules();
    if ($('#dashboardStats').length) loadDashboard();

    if ($('#myScheduleTable').length) {
        loadData('schedules.php?my=1', function (data) {
            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            const dayMap = { Monday: 'จันทร์', Tuesday: 'อังคาร', Wednesday: 'พุธ', Thursday: 'พฤหัสบดี', Friday: 'ศุกร์' };
            let rows = '';
            days.forEach(function (day) {
                const daySchedules = data.filter(s => s.day_of_week === day);
                if (daySchedules.length > 0) {
                    daySchedules.forEach(function (s, i) {
                        rows += `<tr class="day-${day.toLowerCase()}">
                            ${i === 0 ? `<td rowspan="${daySchedules.length}" style="font-weight:700;vertical-align:middle;">${dayMap[day]}</td>` : ''}
                            <td>${s.start_time} - ${s.end_time}</td>
                            <td>${s.subject_name}</td>
                            <td>${s.teacher_name || '-'}</td>
                            <td>${s.room_name}</td>
                            <td>${s.class_name}</td>
                        </tr>`;
                    });
                }
            });
            if (!rows) rows = '<tr><td colspan="6" class="text-center py-4 text-muted">ยังไม่มีตารางเรียน</td></tr>';
            $('#myScheduleTable tbody').html(rows);
        });
    }

    if ($('#myGradesTable').length) {
        loadData('grades.php?my=1', function (data) {
            let rows = '';
            if (data.length === 0) {
                rows = '<tr><td colspan="5" class="text-center py-4 text-muted">ยังไม่มีผลการเรียน</td></tr>';
            }
            data.forEach(function (g, i) {
                const gradeClass = g.grade !== null ? 'grade-' + g.grade : '';
                rows += `<tr>
                    <td>${i + 1}</td>
                    <td>${g.subject_code}</td>
                    <td>${g.subject_name}</td>
                    <td>${g.score !== null ? g.score : '-'}</td>
                    <td>${g.grade !== null ? `<span class="grade-badge ${gradeClass}">${g.grade}</span>` : '-'}</td>
                </tr>`;
            });
            $('#myGradesTable tbody').html(rows);
        });
    }

}); // end document.ready
