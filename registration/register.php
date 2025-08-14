<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../mypbra_connect.php';

include '../includes/navbar.php';
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

    // Validation
    if (
        empty($full_name) || empty($email) || empty($password) || empty($confirm_password) ||
        empty($department_id) || empty($role_id) || empty($office) || empty($user_type) || empty($start_date)
    ) {
        $error_message = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        // Check if email already exists
        $check_email = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check_email->num_rows > 0) {
            $error_message = "Email already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, office, user_type, start_date, work_experience, education)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
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

                $success_message = "Registration successful!";
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
<html>

<head>
    <title>Register New User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="register.css">
</head>

<body>
    <div class="container">
        <h2>Register New User</h2>
        <div class="subtitle">Fill out the form below to create a new user account.</div>
        <?php if ($success_message): ?>
            <div class="success"><?= $success_message ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="error"><?= $error_message ?></div>
        <?php endif; ?>
        <form id="regForm" method="post" autocomplete="off">
            <label>Full Name:</label>
            <input type="text" name="full_name" value="<?= get_field_value('full_name'); ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= get_field_value('email'); ?>" required>

            <label>Password:</label>
            <div class="btn-row">
                <input type="password" name="password" id="password" style="width:60%;display:inline-block;" value="<?= get_field_value('password'); ?>" required>
                <button type="button" onclick="generatePassword()" title="Generate Password">Generate</button>
                <button type="button" onclick="toggleBothPasswords()" title="Show/Hide Password">Show</button>
                <button type="button" onclick="copyPassword()" title="Copy Password">Copy</button>
            </div>

            <label>Confirm Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" value="<?= get_field_value('confirm_password'); ?>" required>

            <label>Department:</label>
            <select name="department" id="department" required onchange="filterRoles()">
                <option value="">Select Department</option>
                <?php foreach ($departments as $d) { ?>
                    <option value="<?= $d['id'] ?>" <?= (get_field_value('department') == $d['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['name']) ?>
                    </option>
                <?php } ?>
            </select>

            <label>Role:</label>
            <select name="role" id="role_select" required>
                <option value="">Select Role</option>
                <!-- Options will be filled by JS -->
            </select>

            <label>Office:</label>
            <select name="office" id="office_select" required onchange="toggleCustomOffice()">
                <option value="">Select Office</option>
                <?php foreach ($offices as $o) { ?>
                    <option value="<?= htmlspecialchars($o['office']) ?>" <?= (get_field_value('office') == $o['office']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($o['office']) ?>
                    </option>
                <?php } ?>
                <option value="other" <?= (get_field_value('office') === 'other') ? 'selected' : '' ?>>Other</option>
            </select>
            <input type="text" name="custom_office" id="custom_office" placeholder="Enter office name"
                value="<?= get_field_value('custom_office'); ?>"
                style="<?= (get_field_value('office') === 'other') ? 'display: block;' : 'display: none;'; ?>">

            <label>User Type:</label>
            <select name="user_type" required>
                <option value="regular" <?= (get_field_value('user_type') == 'regular') ? 'selected' : '' ?>>Regular</option>
                <option value="admin" <?= (get_field_value('user_type') == 'admin') ? 'selected' : '' ?>>Admin</option>
            </select>

            <label>Start Date:</label>
            <input type="date" name="start_date" value="<?= get_field_value('start_date'); ?>" required>

            <label>Work Experience:</label>
            <textarea name="work_experience" rows="3" placeholder="Enter work experience details (Company, Position, Duration)"><?= get_field_value('work_experience'); ?></textarea>

            <label>Education:</label>
            <textarea name="education" rows="3" placeholder="Enter education details (Institution, Degree, Year)"><?= get_field_value('education'); ?></textarea>

            <input type="hidden" name="final_submit" value="1">
            <button type="button" class="submit-btn" onclick="showSummary()">Proceed to Confirmation</button>
        </form>
    </div>
    <!-- Modal for summary -->
    <div id="summaryModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Registration Details</h3>
            <div id="summaryContent"></div>
            <div class="modal-actions">
                <button type="button" onclick="closeSummary()">Edit</button>
                <button type="button" onclick="submitForm()">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        // Roles data for JS filtering
        const rolesData = <?= json_encode($roles); ?>;
        // Pre-fill role if coming back from confirmation
        const selectedDepartment = "<?= get_field_value('department'); ?>";
        const selectedRole = "<?= get_field_value('role'); ?>";
    </script>
    <script src="register.js"></script>
    <?php include '../includes/footer.php'; ?>
</body>

</html>