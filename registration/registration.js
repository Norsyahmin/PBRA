function filterRoles() {
    const deptId = document.getElementById('department').value;
    const roleSelect = document.getElementById('role_select');
    roleSelect.innerHTML = '<option value="">Select Role</option>';
    rolesData.forEach(function (role) {
        if (role.department_id == deptId) {
            let selected = (role.id == selectedRole) ? 'selected' : '';
            roleSelect.innerHTML += `<option value="${role.id}" ${selected}>${role.name}</option>`;
        }
    });
}

// On page load, filter roles if department is pre-selected
window.onload = function () {
    filterRoles();
    toggleCustomOffice();
};

function generatePassword() {
    var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
    var pass = "";
    for (var i = 0; i < 12; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    var pwdEl = document.getElementById('password');
    var cpwdEl = document.getElementById('confirm_password');
    if (pwdEl) pwdEl.value = pass;
    if (cpwdEl) cpwdEl.value = pass;
}

function toggleBothPasswords() {
    var pwd = document.getElementById('password');
    var cpwd = document.getElementById('confirm_password');
    if (pwd) pwd.type = pwd.type === 'password' ? 'text' : 'password';
    if (cpwd) cpwd.type = cpwd.type === 'password' ? 'text' : 'password';
}

function copyPassword() {
    var pwd = document.getElementById('password');
    if (!pwd) return;
    pwd.select();
    document.execCommand('copy');
    alert('Password copied to clipboard!');
}

function toggleCustomOffice() {
    var officeSelect = document.getElementById('office_select');
    var customOffice = document.getElementById('custom_office');
    if (!officeSelect || !customOffice) return;
    if (officeSelect.value === 'other') {
        customOffice.style.display = 'block';
    } else {
        customOffice.style.display = 'none';
    }
}

function getFieldValue(field) {
    var el = document.getElementById(field) || document.querySelector('[name="' + field + '"]');
    return el ? el.value.trim() : '';
}

function formatDateForDisplay(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const day = date.getDate().toString().padStart(2, '0');
    const month = months[date.getMonth()];
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
}

/* Validation helpers */
function validateEmailField(fieldId, warningId, valueId) {
    var input = document.getElementById(fieldId);
    var warning = document.getElementById(warningId);
    var valueSpan = document.getElementById(valueId);
    if (input && warning && valueSpan) {
        var val = input.value.trim();
        if (val && val.indexOf('@') === -1) {
            warning.style.display = 'block';
            valueSpan.textContent = "'" + val + "'";
        } else {
            warning.style.display = 'none';
        }
    }
}

function showRequiredWarning(fieldId, warningId) {
    var input = document.getElementById(fieldId);
    var warning = document.getElementById(warningId);
    if (!input || !warning) return;
    if (!input.value.trim()) {
        warning.style.display = 'block';
    } else {
        warning.style.display = 'none';
    }
}

/* Required fields check and proceed button state */
function allRequiredFilled() {
    var requiredFields = [
        'full_name',
        'email',
        'password',
        'confirm_password',
        'department',
        'role_select',
        'office_select',
        'start_date'
    ];
    return requiredFields.every(function (field) {
        return getFieldValue(field) !== '';
    });
}

function updateProceedBtn() {
    var proceedBtn = document.getElementById('proceedBtn');
    if (!proceedBtn) return;
    proceedBtn.disabled = !allRequiredFilled();
}

/* Summary / Modal logic (single validated implementation) */
function showSummary() {
    // Check all required fields and show warnings for empty ones
    var requiredFields = [
        { id: 'full_name', warning: null },
        { id: 'email', warning: 'emailWarning' },
        { id: 'password', warning: 'passwordWarning' },
        { id: 'confirm_password', warning: null },
        { id: 'department', warning: 'departmentWarning' },
        { id: 'role_select', warning: 'roleWarning' },
        { id: 'office_select', warning: 'officeWarning' },
        { id: 'start_date', warning: 'startDateWarning' }
    ];

    var hasEmptyFields = false;
    requiredFields.forEach(function (f) {
        if (!getFieldValue(f.id)) {
            hasEmptyFields = true;
            if (f.warning) {
                var w = document.getElementById(f.warning);
                if (w) w.style.display = 'block';
            }
        }
    });

    var email = getFieldValue('email');
    if (email && email.indexOf('@') === -1) {
        hasEmptyFields = true;
        validateEmailField('email', 'emailWarning', 'emailValue');
    }

    if (hasEmptyFields) {
        // ensure the proceed button stays disabled
        updateProceedBtn();
        return;
    }

    // Build summary HTML
    var summaryHTML = '';
    summaryHTML += '<h4>Personal Information</h4>';
    summaryHTML += '<p><strong>Full Name:</strong> ' + getFieldValue('full_name') + '</p>';
    summaryHTML += '<p><strong>Email:</strong> ' + getFieldValue('email') + '</p>';

    summaryHTML += '<h4>Work Information</h4>';
    var departmentEl = document.getElementById('department');
    var departmentName = departmentEl ? departmentEl.options[departmentEl.selectedIndex].text : '';
    summaryHTML += '<p><strong>Department:</strong> ' + departmentName + '</p>';

    var roleEl = document.getElementById('role_select');
    var roleName = roleEl ? roleEl.options[roleEl.selectedIndex].text : '';
    summaryHTML += '<p><strong>Role:</strong> ' + roleName + '</p>';

    var officeEl = document.getElementById('office_select');
    var office = '';
    if (officeEl) {
        if (officeEl.value === 'other') {
            office = getFieldValue('custom_office');
        } else {
            office = officeEl.options[officeEl.selectedIndex].text;
        }
    }
    summaryHTML += '<p><strong>Office:</strong> ' + office + '</p>';

    var userTypeEl = document.querySelector('select[name="user_type"]');
    var userType = userTypeEl ? userTypeEl.options[userTypeEl.selectedIndex].text : '';
    summaryHTML += '<p><strong>User Type:</strong> ' + userType + '</p>';

    var startDate = getFieldValue('start_date');
    summaryHTML += '<p><strong>Start Date:</strong> ' + formatDateForDisplay(startDate) + '</p>';

    summaryHTML += '<h4>Additional Information</h4>';
    var workExp = getFieldValue('work_experience');
    summaryHTML += '<p><strong>Work Experience:</strong> ' + (workExp ? workExp : '<em>Not provided</em>') + '</p>';
    var education = getFieldValue('education');
    summaryHTML += '<p><strong>Education:</strong> ' + (education ? education : '<em>Not provided</em>') + '</p>';

    var modal = document.getElementById('summaryModal');
    var summaryContent = document.getElementById('summaryContent');
    if (summaryContent) summaryContent.innerHTML = summaryHTML;
    if (modal) modal.style.display = 'block';
}

function closeSummary() {
    var modal = document.getElementById('summaryModal');
    if (modal) modal.style.display = 'none';
}

function submitForm() {
    // final check before submit
    if (!allRequiredFilled()) return;
    var form = document.getElementById('regForm');
    if (form) form.submit();
}

/* Close modal when clicking outside */
window.addEventListener('click', function (event) {
    var modal = document.getElementById('summaryModal');
    if (modal && event.target == modal) {
        modal.style.display = "none";
    }
});

/* Initialization: attach listeners after DOM ready */
window.addEventListener('DOMContentLoaded', function () {
    // Initial UI setup
    filterRoles();
    toggleCustomOffice();
    updateProceedBtn();

    // Attach change listeners to keep proceed button state updated
    var requiredFields = [
        'full_name',
        'email',
        'password',
        'confirm_password',
        'department',
        'role_select',
        'office_select',
        'start_date'
    ];
    requiredFields.forEach(function (field) {
        var el = document.getElementById(field) || document.querySelector('[name="' + field + '"]');
        if (el) {
            el.addEventListener('input', updateProceedBtn);
            el.addEventListener('change', updateProceedBtn);
            // show/hide warnings on blur where appropriate
            el.addEventListener('blur', function () {
                if (field === 'email') validateEmailField('email', 'emailWarning', 'emailValue');
                if (field === 'password') showRequiredWarning('password', 'passwordWarning');
                if (field === 'department') showRequiredWarning('department', 'departmentWarning');
                if (field === 'role_select') showRequiredWarning('role_select', 'roleWarning');
                if (field === 'office_select') showRequiredWarning('office_select', 'officeWarning');
                if (field === 'start_date') showRequiredWarning('start_date', 'startDateWarning');
            });
        }
    });

    // Additional specific handlers
    var emailEl = document.getElementById('email');
    if (emailEl) {
        emailEl.addEventListener('input', function () {
            var w = document.getElementById('emailWarning');
            if (w) w.style.display = 'none';
        });
    }

    var passwordEl = document.getElementById('password');
    if (passwordEl) {
        passwordEl.addEventListener('input', function () {
            var w = document.getElementById('passwordWarning');
            if (w) w.style.display = 'none';
        });
    }

    var deptEl = document.getElementById('department');
    if (deptEl) {
        deptEl.addEventListener('change', function () {
            filterRoles();
            toggleCustomOffice(); // in case office selection depends on department in future
        });
    }

    var officeEl = document.getElementById('office_select');
    if (officeEl) {
        officeEl.addEventListener('change', function () {
            toggleCustomOffice();
            var w = document.getElementById('officeWarning');
            if (w) w.style.display = 'none';
        });
    }

    // Attach proceed button click reliably
    var proceedBtn = document.getElementById('proceedBtn');
    if (proceedBtn) {
        proceedBtn.addEventListener('click', function (e) {
            // If disabled, do nothing
            if (proceedBtn.disabled) return;
            showSummary();
        });
    }

    // Attach role select warning hide
    var roleSel = document.getElementById('role_select');
    if (roleSel) {
        roleSel.addEventListener('change', function () {
            var w = document.getElementById('roleWarning');
            if (w) w.style.display = 'none';
        });
    }

    // Ensure custom_office visibility matches initial selection
    toggleCustomOffice();
});