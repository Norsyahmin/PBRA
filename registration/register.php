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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $department_id = $conn->real_escape_string($_POST['department']);
    // Determine the office value based on whether 'other' was selected
    $office = $_POST['office_select_hidden'] === 'other' ? // Changed name due to JS handling
        $conn->real_escape_string($_POST['custom_office']) :
        $conn->real_escape_string($_POST['office']); // Original name restored by JS if not 'other'
    $user_type = $conn->real_escape_string($_POST['user_type']);
    $start_date = $_POST['start_date'];
    $role_id = $conn->real_escape_string($_POST['role']);
    // Add new fields
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
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $query = "INSERT INTO users (full_name, email, password, office, user_type, start_date, work_experience, education)
                     VALUES ('$full_name', '$email', '$hashed_password', '$office', '$user_type', '$start_date', '$work_experience', '$education')";

            if ($conn->query($query)) {
                $user_id = $conn->insert_id;

                // Insert into userroles table
                $userroles_query = "INSERT INTO userroles (user_id, role_id, appointed_at)
                                  VALUES ($user_id, $role_id, NOW())";

                if ($conn->query($userroles_query)) {
                    $success_message = "User registered successfully with assigned role!";
                } else {
                    $error_message = "Error assigning role: " . $conn->error;
                }
            } else {
                $error_message = "Error registering user: " . $conn->error;
            }
        }
    }
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

    <form method="POST" action="">
        <div class="form-group">
            <label for="full_name">Full Name:</label>
            <input type="text" name="full_name" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <div class="password-container">
                <input type="password" name="password" id="password" required>
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
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
        </div>

        <div class="form-group">
            <label for="department">Department:</label>
            <select name="department" id="department" required>
                <option value="">Select Department</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?php echo $department['id']; ?>"><?php echo $department['name']; ?></option>
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
                <?php foreach ($offices as $office): ?>
                    <option value="<?php echo $office['office']; ?>"><?php echo $office['office']; ?></option>
                <?php endforeach; ?>
                <option value="other">Other</option>
            </select>
            <input type="text" name="custom_office" id="custom_office" placeholder="Enter office name">
        </div>

        <div class="form-group">
            <label for="user_type">User Type:</label>
            <select name="user_type" required>
                <option value="regular">Regular</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <div class="form-group">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" required>
        </div>

        <div class="form-group">
            <label for="work_experience">Work Experience:</label>
            <textarea name="work_experience" placeholder="Enter work experience details (Company, Position, Duration)"></textarea>
        </div>

        <div class="form-group">
            <label for="education">Education:</label>
            <textarea name="education" placeholder="Enter education details (Institution, Degree, Year)"></textarea>
        </div>

        <button type="submit">Register User</button>
    </form>

    <script>
        // Store roles data - This needs to be available globally for form_logic.js
        const rolesData = <?php echo json_encode($roles); ?>;
    </script>
    <script src="register.js"></script>
</body>

</html>