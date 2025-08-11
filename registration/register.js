/**
 * Toggles the visibility (type) of both password input fields.
 */
function toggleBothPasswords() {
  const passwordInput = document.getElementById("password");
  const confirmPasswordInput = document.getElementById("confirm_password");
  const type = passwordInput.type === "password" ? "text" : "password";
  passwordInput.type = type;
  confirmPasswordInput.type = type;
}

/**
 * Generates a random password and populates the password input fields.
 */
function generatePassword() {
  const length = 12;
  const charset =
    "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+";
  let password = "";
  for (let i = 0; i < length; i++) {
    password += charset.charAt(Math.floor(Math.random() * charset.length));
  }
  document.getElementById("password").value = password;
  document.getElementById("confirm_password").value = password;
}

/**
 * Copies the generated password to the clipboard.
 */
function copyPassword() {
  const passwordInput = document.getElementById("password");
  passwordInput.select();
  // document.execCommand('copy') is deprecated but widely supported.
  // For modern browsers in a secure context (HTTPS), use navigator.clipboard.writeText().
  document.execCommand("copy");
  alert("Password copied to clipboard!");
}

// This script assumes that 'rolesData' is defined globally in the HTML
// by the PHP script (e.g., <script>const rolesData = ...;</script>)
// before this script file is loaded.

document.addEventListener("DOMContentLoaded", () => {
  const departmentSelect = document.getElementById("department");
  const officeSelect = document.getElementById("office_select");
  const roleSelect = document.getElementById("role_select");
  const customOfficeInput = document.getElementById("custom_office");

  // Initial state for custom office input based on current selection
  // (useful if form state is preserved on refresh/error)
  if (officeSelect.value !== "other") {
    customOfficeInput.style.display = "none";
    customOfficeInput.required = false;
  }

  /**
   * Handles the change event for the department select element.
   * Filters roles based on the selected department and updates the office code.
   */
  departmentSelect.addEventListener("change", function () {
    const departmentId = this.value;

    // Clear and update role options
    roleSelect.innerHTML = '<option value="">Select Role</option>';
    if (departmentId && typeof rolesData !== "undefined") {
      const departmentRoles = rolesData.filter(
        (role) => role.department_id == departmentId,
      );
      departmentRoles.forEach((role) => {
        roleSelect.add(new Option(role.name, role.id));
      });

      // Update office code: 'OSP' + departmentId
      const officeCode = "OSP" + departmentId;
      let option = Array.from(officeSelect.options).find(
        (opt) => opt.value === officeCode,
      );
      if (!option) {
        // If 'OSP' + departmentId option doesn't exist, add it
        option = new Option(officeCode, officeCode);
        officeSelect.add(option);
      }
      officeSelect.value = officeCode; // Select the newly added/found option

      // Ensure custom office input is hidden and not required if an OSP code is selected
      customOfficeInput.style.display = "none";
      customOfficeInput.required = false;
      customOfficeInput.value = "";
      // Restore the name of the 'office' select to ensure its value is submitted
      officeSelect.name = "office";
    } else {
      // If no department is selected, clear roles and reset office selection
      officeSelect.value = "";
      customOfficeInput.style.display = "none";
      customOfficeInput.required = false;
      customOfficeInput.value = "";
      officeSelect.name = "office";
    }
  });

  /**
   * Handles the change event for the office select element.
   * Toggles the visibility and required status of the custom office input field.
   * Also changes the 'name' attribute of the 'office_select' element
   * to control which value is submitted to the server ('office' or 'custom_office').
   */
  officeSelect.addEventListener("change", function () {
    if (this.value === "other") {
      customOfficeInput.style.display = "block";
      customOfficeInput.required = true;
      // Change the name of the 'office' select to prevent it from being submitted
      // when 'other' is selected. The 'custom_office' input will then be submitted.
      this.name = "office_select_hidden";
    } else {
      customOfficeInput.style.display = "none";
      customOfficeInput.required = false;
      customOfficeInput.value = "";
      // Restore the name of the 'office' select so its value is submitted.
      this.name = "office";
    }
  });
});