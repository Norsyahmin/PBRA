<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../mypbra_connect.php';
// Include the verification email function
require_once '../account_activation/send_verification_email.php';
// Include registration notification functions
require_once 'registration_notification.php';

$success_message = '';
$error_message = '';

// Get departments for dropdown
$departments_query = "SELECT id, name FROM departments ORDER BY name";
$departments_result = $conn->query($departments_query);
$departments = $departments_result->fetch_all(MYSQLI_ASSOC);

// Get unique offices from users table - sorted
$offices_query = "SELECT DISTINCT office FROM users WHERE office IS NOT NULL ORDER BY CAST(SUBSTRING(office, 4) AS UNSIGNED), office";
$offices_result = $conn->query($offices_query);
$offices = $offices_result->fetch_all(MYSQLI_ASSOC);

// Get roles for dropdown (filtered by JS)
$roles_query = "SELECT id, name, department_id FROM roles ORDER BY name";
$roles_result = $conn->query($roles_query);
$roles = $roles_result->fetch_all(MYSQLI_ASSOC);

// --- Handle POST request (Final Registration) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['final_submit'])) {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    // NOTE: recovery_email field removed from UI. For backward DB compatibility, we set it equal to primary email.
    $recovery_email = $email;
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $department_id = $conn->real_escape_string($_POST['department']);
    $office = $_POST['office'] === 'other'
        ? $conn->real_escape_string($_POST['custom_office'])
        : $conn->real_escape_string($_POST['office']);
    $user_type = $conn->real_escape_string($_POST['user_type']);
    $start_date = $_POST['start_date'];
    $role_id = $conn->real_escape_string($_POST['role']);
    $work_experience = $conn->real_escape_string($_POST['work_experience']);
    $education = $conn->real_escape_string($_POST['education']);

    // Validation (note recovery_email removed as separate input)
    if (
        empty($full_name) || empty($email) || empty($password) || empty($confirm_password) ||
        empty($department_id) || empty($role_id) || empty($office) || empty($user_type) || empty($start_date)
    ) {
        $error_message = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        // Check if email already exists
        $check_email = $conn->query("SELECT id FROM users WHERE email = '$email'");

        if ($check_email->num_rows > 0) {
            $error_message = "Email already exists!";
        }
        // Note: we no longer check recovery_email uniqueness separately (recovery_email = primary email now)
        else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user with is_verified=0 and must_change_password=1
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, office, user_type, start_date, work_experience, education, is_verified, must_change_password)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 1)");
            $stmt->bind_param(
                "ssssssss",
                $full_name,
                $email,
                $hashed_password,
                $office,
                $user_type,
                $start_date,
                $work_experience,
                $education
            );

            if ($stmt->execute()) {
                $user_id = $conn->insert_id;

                // Assign role to user
                $stmt2 = $conn->prepare("INSERT INTO userroles (user_id, role_id) VALUES (?, ?)");
                $stmt2->bind_param("ii", $user_id, $role_id);
                $stmt2->execute();
                $stmt2->close();

                // Send email to user with their credentials and verification link
                // send_user_registration_notification will send to the primary email (we pass primary email)
                if (send_user_registration_notification(
                    $user_id,
                    $full_name,
                    $email,
                    $password,
                    $email, // pass primary email as destination for compatibility
                    $department_id,
                    $role_id,
                    $office,
                    $user_type,
                    $conn
                )) {
                    $success_message = "Registration successful! An email with account details and verification instructions has been sent to $email.";
                } else {
                    $success_message = "Registration successful! However, we couldn't send the notification email. Please ask the user to contact the administrator.";
                    // Log this error
                    error_log("Failed to send notification email to $recovery_email for user ID $user_id");
                }

                // No need to call send_verification_email separately since the verification link is included in the notification email
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
// Helper function to safely get a value from $_POST for input fields
function get_field_value($field_name, $default = '')
{
    return htmlspecialchars($_POST[$field_name] ?? $default);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- reuse report styles for consistent design -->
    <link rel="stylesheet" href="../report/report.css">
    <link rel="stylesheet" href="registration.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Registration</title>
</head>

<body>
    <?php include '../dashboard_template/navbar/navbar.php'; ?>

    <!-- Page Title -->
    <div class="page-title">
        <h1 style="font-size: 30px;">REGISTRATION</h1>
    </div>

    <!-- BEGIN: Registration form (added) -->
    <div id="content" class="form">
        <?php if ($success_message): ?>
            <div class="success">
                <i class="fas fa-check-circle" style="margin-right: 8px;"></i>
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="error">
                <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <!-- Registration Form Section -->
        <form id="regForm" method="post" autocomplete="off">
            <div class="field">
                <label>Full Name:</label>
                <input type="text" name="full_name" required value="<?= get_field_value('full_name') ?>">
            </div>

            <div class="field">
                <label>Email:</label>
                <input type="email" name="email" id="email" required value="<?= get_field_value('email') ?>">
                <div id="emailWarning" class="field-warning" style="display:none;">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                    Please include an '@' in the email address. <span id="emailValue"></span> is missing an '@'.
                </div>
            </div>

            <div class="field">
                <label>Password:</label>
                <div class="btn-row">
                    <input type="password" name="password" id="password" required>
                    <button type="button" onclick="generatePassword()" title="Generate Password">Generate</button>
                    <button type="button" onclick="toggleBothPasswords()" title="Show/Hide Password">Show</button>
                    <button type="button" onclick="copyPassword()" title="Copy Password">Copy</button>
                </div>
                <div id="passwordWarning" class="field-warning" style="display:none;">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                    Password is required.
                </div>
            </div>

            <div class="field">
                <label>Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>

            <div class="field">
                <label>Department:</label>
                <select name="department" id="department" required onchange="filterRoles()">
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= htmlspecialchars($d['id']) ?>" <?= (get_field_value('department') == $d['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="departmentWarning" class="field-warning" style="display:none;">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                    Department is required.
                </div>
            </div>

            <div class="field">
                <label>Role:</label>
                <select name="role" id="role_select" required>
                    <option value="">Select Role</option>
                </select>
                <div id="roleWarning" class="field-warning" style="display:none;">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                    Role is required.
                </div>
            </div>

            <div class="field">
                <label>Office:</label>
                <select name="office" id="office_select" required onchange="toggleCustomOffice()">
                    <option value="">Select Office</option>
                    <?php foreach ($offices as $o): ?>
                        <option value="<?= htmlspecialchars($o['office']) ?>" <?= (get_field_value('office') == $o['office']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($o['office']) ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="other" <?= (get_field_value('office') === 'other') ? 'selected' : '' ?>>Other</option>
                </select>
                <input type="text" name="custom_office" id="custom_office" placeholder="Enter office name" style="display:none;" value="<?= get_field_value('custom_office') ?>">
                <div id="officeWarning" class="field-warning" style="display:none;">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                    Office is required.
                </div>
            </div>

            <div class="field">
                <label>User Type:</label>
                <select name="user_type" required>
                    <option value="regular" <?= (get_field_value('user_type', 'regular') === 'regular') ? 'selected' : '' ?>>Regular</option>
                    <option value="admin" <?= (get_field_value('user_type') === 'admin') ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>

            <div class="field">
                <label>Start Date:</label>
                <input type="date" name="start_date" id="start_date" required value="<?= get_field_value('start_date') ?>">
                <div id="startDateWarning" class="field-warning" style="display:none;">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                    Start date is required.
                </div>
            </div>

            <div class="field">
                <label>Work Experience:</label>
                <textarea name="work_experience" rows="3" placeholder="Enter work experience details (Company, Position, Duration)"><?= get_field_value('work_experience') ?></textarea>
            </div>

            <div class="field">
                <label>Education:</label>
                <textarea name="education" rows="3" placeholder="Enter education details (Institution, Degree, Year)"><?= get_field_value('education') ?></textarea>
            </div>

            <input type="hidden" name="final_submit" value="1">
            <div class="form-actions-right">
                <button type="button" class="cancel-btn" onclick=" window.location.href='../homepage/homepage.php'">Cancel</button>
                <button type="button" class="confirm-btn" id="proceedBtn" onclick="showSummary()">Proceed to Confirmation</button>
            </div>
        </form>
        <!-- End Registration Form Section -->
    </div>
    <!-- END: Registration form (added) -->

    <!-- Summary modal expected by register.js -->
    <div id="summaryModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; overflow:auto; background:rgba(0,0,0,0.5);">
        <div style="background:#fff; margin:10% auto; padding:20px; border-radius:6px; width:90%; max-width:600px; position:relative;">
            <h3>Registration Summary</h3>
            <div id="summaryContent" style="margin-top:12px;"></div>
            <div style="margin-top:16px;text-align:right;">
                <button type="button" class="cancel-btn" onclick="closeSummary()" style="margin-right:8px;">Edit</button>
                <button type="button" class="confirm-btn" onclick="submitForm()">Confirm &amp; Submit</button>
            </div>
        </div>
    </div>

    <script>
        // provide roles data and selected role to register.js
        const rolesData = <?= json_encode($roles); ?>;
        // Pre-fill role if coming back from confirmation
        const selectedDepartment = "<?= get_field_value('department'); ?>";
        const selectedRole = "<?= get_field_value('role'); ?>";
    </script>

    <script src="registration.js"></script>
    <?php include '../footer/footer.php'; ?>
    <?php include '../dashboard_template/scrolltop/scrolltop.php'; ?>
    <script src="../dashboard_template/scrolltop/scrolltop.js"></script>
    <?php include '../dashboard_template/scrolltop/scrolltop.php'; ?>
</body>

</html>