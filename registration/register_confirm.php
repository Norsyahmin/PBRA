<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if form data exists in POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: register.php");
    exit();
}

include '../mypbra_connect.php';

// Store form data in session
$_SESSION['register_form_data'] = $_POST;

// Get departments data
$departments_query = "SELECT id, name FROM departments ORDER BY name";
$departments_result = $conn->query($departments_query);
$departments = $departments_result->fetch_all(MYSQLI_ASSOC);
$_SESSION['departments_lookup'] = $departments;

// Get roles data
$roles_query = "SELECT id, name FROM roles ORDER BY name";
$roles_result = $conn->query($roles_query);
$roles = $roles_result->fetch_all(MYSQLI_ASSOC);
$_SESSION['roles_lookup'] = $roles;

// Helper functions
function getDepartmentName($departmentId, $departments)
{
    foreach ($departments as $dept) {
        if ($dept['id'] == $departmentId) {
            return htmlspecialchars($dept['name']);
        }
    }
    return 'N/A';
}

function getRoleName($roleId, $roles)
{
    foreach ($roles as $role) {
        if ($role['id'] == $roleId) {
            return htmlspecialchars($role['name']);
        }
    }
    return 'N/A';
}

// Determine office display value
$displayOffice = htmlspecialchars($_POST['office']);
if ($_POST['office'] === 'other' && !empty($_POST['custom_office'])) {
    $displayOffice = htmlspecialchars($_POST['custom_office']) . " (Custom)";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Confirm Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }

        .confirmation-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .confirmation-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .confirmation-table th,
        .confirmation-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .confirmation-table th {
            background-color: #f5f5f5;
            width: 30%;
        }

        .confirmation-buttons {
            text-align: center;
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .confirmation-buttons button {
            padding: 10px 20px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            font-size: 14px;
        }

        .edit-btn {
            background-color: #f0ad4e;
            color: white;
        }

        .confirm-btn {
            background-color: #5cb85c;
            color: white;
        }
    </style>
</head>

<body>
    <h2>Confirm Registration Details</h2>

    <form method="POST" action="register_process.php" class="confirmation-form">
        <table class="confirmation-table">
            <tr>
                <th>Full Name</th>
                <td><?= htmlspecialchars($_POST['full_name']) ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?= htmlspecialchars($_POST['email']) ?></td>
            </tr>
            <tr>
                <th>Department</th>
                <td><?= getDepartmentName($_POST['department'], $departments) ?></td>
            </tr>
            <tr>
                <th>Role</th>
                <td><?= getRoleName($_POST['role'], $roles) ?></td>
            </tr>
            <tr>
                <th>Office</th>
                <td><?= $displayOffice ?></td>
            </tr>
            <tr>
                <th>User Type</th>
                <td><?= htmlspecialchars(ucfirst($_POST['user_type'])) ?></td>
            </tr>
            <tr>
                <th>Start Date</th>
                <td><?= htmlspecialchars($_POST['start_date']) ?></td>
            </tr>
            <tr>
                <th>Work Experience</th>
                <td><?= nl2br(htmlspecialchars($_POST['work_experience'])) ?></td>
            </tr>
            <tr>
                <th>Education</th>
                <td><?= nl2br(htmlspecialchars($_POST['education'])) ?></td>
            </tr>
        </table>

        <div class="confirmation-buttons">
            <button type="button" class="edit-btn" onclick="window.location.href='register.php'">
                Edit Details
            </button>
            <button type="submit" class="confirm-btn">
                Confirm Registration
            </button>
        </div>
    </form>
</body>

</html>