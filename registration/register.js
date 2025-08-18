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
    // Or, for a more user-friendly approach, you could create a temporary message element:
    /*
    const messageDiv = document.createElement('div');
    messageDiv.textContent = 'Password copied!';
    messageDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; background-color: #4CAF50; color: white; padding: 10px 15px; border-radius: 5px; z-index: 10000;';
    document.body.appendChild(messageDiv);
    setTimeout(() => {
        messageDiv.remove();
    }, 2000); // Remove after 2 seconds
    */
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