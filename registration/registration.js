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
    document.getElementById('password').value = pass;
    document.getElementById('confirm_password').value = pass;
}

function toggleBothPasswords() {
    var pwd = document.getElementById('password');
    var cpwd = document.getElementById('confirm_password');
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
    cpwd.type = cpwd.type === 'password' ? 'text' : 'password';
}

function copyPassword() {
    var pwd = document.getElementById('password');
    pwd.select();
    document.execCommand('copy');

    // Add a visual feedback
    alert('Password copied to clipboard!');

}

function toggleCustomOffice() {
    var officeSelect = document.getElementById('office_select');
    var customOffice = document.getElementById('custom_office');
    if (officeSelect.value === 'other') {
        customOffice.style.display = 'block';
    } else {
        customOffice.style.display = 'none';
    }
}

function showSummary() {
    var form = document.getElementById('regForm');
    var summary = '';
    summary += '<div class="summary-row"><span class="summary-label">Full Name:</span> ' + form.full_name.value + '</div>';
    summary += '<div class="summary-row"><span class="summary-label">Email:</span> ' + form.email.value + '</div>';
    summary += '<div class="summary-row"><span class="summary-label">Recovery Email:</span> ' + form.recovery_email.value + '</div>';
    summary += '<div class="summary-row"><span class="summary-label">Department:</span> ' + form.department.options[form.department.selectedIndex].text + '</div>';
    summary += '<div class="summary-row"><span class="summary-label">Role:</span> ' + form.role_select.options[form.role_select.selectedIndex].text + '</div>';
    var officeText = form.office_select.options[form.office_select.selectedIndex].text;
    if (form.office_select.value === 'other') {
        officeText = form.custom_office.value;
    }
    summary += '<div class="summary-row"><span class="summary-label">Office:</span> ' + officeText + '</div>';
    summary += '<div class="summary-row"><span class="summary-label">User Type:</span> ' + form.user_type.options[form.user_type.selectedIndex].text + '</div>';
    summary += '<div class="summary-row"><span class="summary-label">Start Date:</span> ' + form.start_date.value + '</div>';
    summary += '<div class="summary-row"><span class="summary-label">Work Experience:</span> ' + form.work_experience.value + '</div>';
    summary += '<div class="summary-row"><span class="summary-label">Education:</span> ' + form.education.value + '</div>';
    document.getElementById('summaryContent').innerHTML = summary;
    document.getElementById('summaryModal').style.display = 'block';
}

function closeSummary() {
    document.getElementById('summaryModal').style.display = 'none';
}

function submitForm() {
    document.getElementById('regForm').submit();
}

// Close modal if clicked outside
window.onclick = function (event) {
    var modal = document.getElementById('summaryModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

// prepareSubmission copies visible select values into hidden inputs expected by server
function prepareSubmission() {
    var roleSel = document.getElementById('role_select');
    var hiddenRole = document.getElementById('hidden_role');
    if (roleSel && hiddenRole) {
        hiddenRole.value = roleSel.value || '';
    }

    var officeSel = document.getElementById('office_select');
    var customOffice = document.getElementById('custom_office');
    var hiddenOffice = document.getElementById('hidden_office');
    if (officeSel && hiddenOffice) {
        if (officeSel.value === 'other' && customOffice) {
            hiddenOffice.value = customOffice.value || '';
        } else {
            hiddenOffice.value = officeSel.value || '';
        }
    }
    // allow the form to submit
    return true;
}

// Email '@' validation warning (show only on blur)
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

document.getElementById('email').addEventListener('blur', function () {
    validateEmailField('email', 'emailWarning', 'emailValue');
});
document.getElementById('email').addEventListener('input', function () {
    document.getElementById('emailWarning').style.display = 'none';
});

document.getElementById('recovery_email').addEventListener('blur', function () {
    validateEmailField('recovery_email', 'recoveryEmailWarning', 'recoveryEmailValue');
});
document.getElementById('recovery_email').addEventListener('input', function () {
    document.getElementById('recoveryEmailWarning').style.display = 'none';
});

// Helper to get field value by id or name
function getFieldValue(field) {
    var el = document.getElementById(field) || document.querySelector('[name="' + field + '"]');
    return el ? el.value.trim() : '';
}

// Check if all required fields are filled
function allRequiredFilled() {
    var requiredFields = [
        'full_name',
        'email',
        'recovery_email',
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

// Enable/disable Proceed button
function updateProceedBtn() {
    var proceedBtn = document.getElementById('proceedBtn');
    if (proceedBtn) proceedBtn.disabled = !allRequiredFilled();
}

// Attach listeners to required fields
window.addEventListener('DOMContentLoaded', function () {
    var requiredFields = [
        'full_name',
        'email',
        'recovery_email',
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
        }
    });
    updateProceedBtn();
});

// Ensure custom_office visibility matches initial selection on page load
window.addEventListener('load', function () {
    var officeSel = document.getElementById('office_select');
    var customOffice = document.getElementById('custom_office');
    if (officeSel && customOffice) {
        if (officeSel.value === 'other') customOffice.style.display = 'block';
        else customOffice.style.display = 'none';
    }
});

// Show warning only on blur if field is empty
function showRequiredWarning(fieldId, warningId, message) {
    var input = document.getElementById(fieldId);
    var warning = document.getElementById(warningId);
    if (input && warning) {
        if (!input.value.trim()) {
            warning.style.display = 'block';
        } else {
            warning.style.display = 'none';
        }
    }
}

document.getElementById('password').addEventListener('blur', function () {
    showRequiredWarning('password', 'passwordWarning', 'Password is required.');
});
document.getElementById('password').addEventListener('input', function () {
    document.getElementById('passwordWarning').style.display = 'none';
});

document.getElementById('department').addEventListener('blur', function () {
    showRequiredWarning('department', 'departmentWarning', 'Department is required.');
});
document.getElementById('department').addEventListener('change', function () {
    document.getElementById('departmentWarning').style.display = 'none';
});

document.getElementById('role_select').addEventListener('blur', function () {
    showRequiredWarning('role_select', 'roleWarning', 'Role is required.');
});
document.getElementById('role_select').addEventListener('change', function () {
    document.getElementById('roleWarning').style.display = 'none';
});

document.getElementById('office_select').addEventListener('blur', function () {
    showRequiredWarning('office_select', 'officeWarning', 'Office is required.');
});
document.getElementById('office_select').addEventListener('change', function () {
    document.getElementById('officeWarning').style.display = 'none';
});

document.getElementById('start_date').addEventListener('blur', function () {
    showRequiredWarning('start_date', 'startDateWarning', 'Start date is required.');
});
document.getElementById('start_date').addEventListener('input', function () {
    document.getElementById('startDateWarning').style.display = 'none';
});

// Helper function to format date from YYYY-MM-DD to DD-MMM-YYYY
function formatDateForDisplay(dateString) {
    if (!dateString) return '';

    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString; // Return original if invalid

    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const day = date.getDate().toString().padStart(2, '0');
    const month = months[date.getMonth()];
    const year = date.getFullYear();

    return `${day}-${month}-${year}`;
}

// Modified showSummary function to check required fields before proceeding
function showSummary() {
    // Check all required fields and show warnings for empty ones
    var requiredFields = [{
        id: 'full_name',
        warning: null,
        message: 'Full Name is required.'
    },
    {
        id: 'email',
        warning: 'emailWarning',
        message: 'Email is required.'
    },
    {
        id: 'recovery_email',
        warning: 'recoveryEmailWarning',
        message: 'Recovery Email is required.'
    },
    {
        id: 'password',
        warning: 'passwordWarning',
        message: 'Password is required.'
    },
    {
        id: 'confirm_password',
        warning: null,
        message: 'Confirm Password is required.'
    },
    {
        id: 'department',
        warning: 'departmentWarning',
        message: 'Department is required.'
    },
    {
        id: 'role_select',
        warning: 'roleWarning',
        message: 'Role is required.'
    },
    {
        id: 'office_select',
        warning: 'officeWarning',
        message: 'Office is required.'
    },
    {
        id: 'start_date',
        warning: 'startDateWarning',
        message: 'Start Date is required.'
    }
    ];

    var hasEmptyFields = false;

    // Check each field and show warnings for empty ones
    requiredFields.forEach(function (field) {
        var value = getFieldValue(field.id);
        if (!value) {
            hasEmptyFields = true;
            // If field has associated warning element, show it
            if (field.warning) {
                var warningEl = document.getElementById(field.warning);
                if (warningEl) {
                    warningEl.style.display = 'block';
                }
            }
        }
    });

    // Additional validation for email format
    var email = getFieldValue('email');
    var recoveryEmail = getFieldValue('recovery_email');

    if (email && email.indexOf('@') === -1) {
        hasEmptyFields = true;
        validateEmailField('email', 'emailWarning', 'emailValue');
    }

    if (recoveryEmail && recoveryEmail.indexOf('@') === -1) {
        hasEmptyFields = true;
        validateEmailField('recovery_email', 'recoveryEmailWarning', 'recoveryEmailValue');
    }

    // Only proceed to show the summary if all fields are valid
    if (!hasEmptyFields) {
        // Prepare summary HTML with complete information
        var summaryHTML = '';

        // Personal Information
        summaryHTML += '<h4>Personal Information</h4>';
        summaryHTML += '<p><strong>Full Name:</strong> ' + getFieldValue('full_name') + '</p>';
        summaryHTML += '<p><strong>Email:</strong> ' + getFieldValue('email') + '</p>';
        summaryHTML += '<p><strong>Recovery Email:</strong> ' + getFieldValue('recovery_email') + '</p>';

        // Work Information
        summaryHTML += '<h4>Work Information</h4>';

        // Department (get text, not value)
        var departmentEl = document.getElementById('department');
        var departmentName = departmentEl.options[departmentEl.selectedIndex].text;
        summaryHTML += '<p><strong>Department:</strong> ' + departmentName + '</p>';

        // Role (get text, not value)
        var roleEl = document.getElementById('role_select');
        var roleName = roleEl.options[roleEl.selectedIndex].text;
        summaryHTML += '<p><strong>Role:</strong> ' + roleName + '</p>';

        // Office (handle custom office)
        var officeEl = document.getElementById('office_select');
        var office = '';
        if (officeEl.value === 'other') {
            office = getFieldValue('custom_office');
        } else {
            office = officeEl.options[officeEl.selectedIndex].text;
        }
        summaryHTML += '<p><strong>Office:</strong> ' + office + '</p>';

        // User type
        var userTypeEl = document.querySelector('select[name="user_type"]');
        var userType = userTypeEl.options[userTypeEl.selectedIndex].text;
        summaryHTML += '<p><strong>User Type:</strong> ' + userType + '</p>';

        // Format the start date
        var startDate = getFieldValue('start_date');
        var formattedStartDate = formatDateForDisplay(startDate);
        summaryHTML += '<p><strong>Start Date:</strong> ' + formattedStartDate + '</p>';

        // Additional Information
        summaryHTML += '<h4>Additional Information</h4>';

        // Work experience and education (if provided)
        var workExp = getFieldValue('work_experience');
        if (workExp) {
            summaryHTML += '<p><strong>Work Experience:</strong> ' + workExp + '</p>';
        } else {
            summaryHTML += '<p><strong>Work Experience:</strong> <em>Not provided</em></p>';
        }

        var education = getFieldValue('education');
        if (education) {
            summaryHTML += '<p><strong>Education:</strong> ' + education + '</p>';
        } else {
            summaryHTML += '<p><strong>Education:</strong> <em>Not provided</em></p>';
        }

        // Show the summary modal
        var modal = document.getElementById('summaryModal');
        var summaryContent = document.getElementById('summaryContent');
        summaryContent.innerHTML = summaryHTML;
        modal.style.display = 'block';
    }
}