<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}
include '../mypbra_connect.php'; // Ensure DB connection

$success_message = '';
$error_message = '';

// Get departments for dropdown
$departments_query = "SELECT id, name FROM departments ORDER BY name";
$departments_result = $conn->query($departments_query);
$departments = $departments_result->fetch_all(MYSQLI_ASSOC);

// Get unique offices from users table - modified to sort in ascending order
$offices_query = "SELECT DISTINCT office FROM users
                 WHERE office IS NOT NULL
                 ORDER BY CAST(SUBSTRING(office, 4) AS UNSIGNED), office";
$offices_result = $conn->query($offices_query);
$offices = $offices_result->fetch_all(MYSQLI_ASSOC);

// Get roles for dropdown (will be filtered by JavaScript)
$roles_query = "SELECT id, name, department_id FROM roles ORDER BY name";
$roles_result = $conn->query($roles_query);
$roles = $roles_result->fetch_all(MYSQLI_ASSOC);

// --- Handle POST request (Submission to Confirmation) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $department_id = $conn->real_escape_string($_POST['department']);
    $office = $_POST['office'] === 'other' ?
        $conn->real_escape_string($_POST['custom_office']) :
        $conn->real_escape_string($_POST['office']);
    $user_type = $conn->real_escape_string($_POST['user_type']);
    $start_date = $_POST['start_date'];
    $role_id = $conn->real_escape_string($_POST['role']);
    $work_experience = $conn->real_escape_string($_POST['work_experience']);
    $education = $conn->real_escape_string($_POST['education']);

    // Validation
    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        // Check if email already exists
        $check_email = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check_email->num_rows > 0) {
            $error_message = "Email already exists!";
        } else {
            // Store form data in session
            $_SESSION['register_form_data'] = [
                'full_name' => $full_name,
                'email' => $email,
                'password' => $password, // Will be hashed later
                'department' => $department_id,
                'role' => $role_id,
                'office' => $_POST['office'],
                'custom_office' => $_POST['custom_office'] ?? '',
                'user_type' => $user_type,
                'start_date' => $start_date,
                'work_experience' => $work_experience,
                'education' => $education
            ];

            // Store lookups for department and role names
            $_SESSION['departments_lookup'] = $departments;
            $_SESSION['roles_lookup'] = $roles;

            // Redirect to confirmation page
            header("Location: register_confirm.php");
            exit();
        }
    }
}

// --- Pre-fill form if data exists in session (e.g., coming back from confirmation page) ---
$form_data = $_SESSION['register_form_data'] ?? [];
unset($_SESSION['register_form_data']); // Clear form data after retrieving it

// Display any success/error messages from previous steps (e.g., from register_process.php)
if (isset($_SESSION['register_success'])) {
    $success_message = $_SESSION['register_success'];
    unset($_SESSION['register_success']);
}
if (isset($_SESSION['register_error'])) {
    $error_message = $_SESSION['register_error'];
    unset($_SESSION['register_error']);
}

// Helper function to safely get a value from $form_data for input fields
function get_field_value($field_name, $default = '')
{
    global $form_data;
    return htmlspecialchars($form_data[$field_name] ?? $default);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register New User</title>
    <link rel="stylesheet" href="register.css">
</head>

<body>
    <h2>Register New User</h2>

    <?php if ($success_message): ?>
        <div class="success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="register_confirm.php">
        <div class="form-group">
            <label for="full_name">Full Name:</label>
            <input type="text" name="full_name" value="<?= get_field_value('full_name'); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" value="<?= get_field_value('email'); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <div class="password-container">
                <input type="password" name="password" id="password" value="<?= get_field_value('password'); ?>" required>
                <div class="password-buttons">
                    <button type="button" class="generate-btn" onclick="generatePassword()">Generate</button>
                    <button type="button" onclick="toggleBothPasswords()">Show</button>
                    <button type="button" onclick="copyPassword()">Copy</button>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password:</label>
            <div class="password-container">
                <input type="password" name="confirm_password" id="confirm_password" value="<?= get_field_value('confirm_password'); ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="department">Department:</label>
            <select name="department" id="department" required>
                <option value="">Select Department</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?php echo $department['id']; ?>" <?= (get_field_value('department') == $department['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($department['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="role">Role:</label>
            <select name="role" id="role_select" required>
                <option value="">Select Role</option>
            </select>
        </div>

        <div class="form-group">
            <label for="office">Office:</label>
            <select name="office" id="office_select" required>
                <option value="">Select Office</option>
                <?php foreach ($offices as $office_option): ?>
                    <option value="<?php echo htmlspecialchars($office_option['office']); ?>" <?= (get_field_value('office') == $office_option['office']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($office_option['office']); ?>
                    </option>
                <?php endforeach; ?>
                <option value="other" <?= (get_field_value('office') === 'other') ? 'selected' : ''; ?>>Other</option>
            </select>
            <input type="text" name="custom_office" id="custom_office" placeholder="Enter office name" value="<?= get_field_value('custom_office'); ?>" style="<?= (get_field_value('office') === 'other') ? 'display: block;' : 'display: none;'; ?>">
        </div>

        <div class="form-group">
            <label for="user_type">User Type:</label>
            <select name="user_type" required>
                <option value="regular" <?= (get_field_value('user_type') == 'regular') ? 'selected' : ''; ?>>Regular</option>
                <option value="admin" <?= (get_field_value('user_type') == 'admin') ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>

        <div class="form-group">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" value="<?= get_field_value('start_date'); ?>" required>
        </div>

        <div class="form-group">
            <label for="work_experience">Work Experience:</label>
            <textarea name="work_experience" placeholder="Enter work experience details (Company, Position, Duration)"><?= get_field_value('work_experience'); ?></textarea>
        </div>

        <div class="form-group">
            <label for="education">Education:</label>
            <textarea name="education" placeholder="Enter education details (Institution, Degree, Year)"><?= get_field_value('education'); ?></textarea>
        </div>

        <button type="submit">Proceed to Confirmation</button>
    </form>
    <script>
        const rolesData = <?php echo json_encode($roles); ?>;
    </script>
    <script src="register.js"></script>
</body>

</html>