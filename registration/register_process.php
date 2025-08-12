<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}
include '../mypbra_connect.php'; // Ensure DB connection

// Ensure data exists in session for processing
if (!isset($_SESSION['register_form_data'])) {
    $_SESSION['register_error'] = "No registration data found. Please start over.";
    header("Location: register.php");
    exit();
}

// Change the data source from $_POST to $_SESSION['register_form_data']
$formData = $_SESSION['register_form_data'];

// --- Data Validation (Re-validate before final insert) ---
$error_message = '';

if ($formData['password'] !== $formData['confirm_password']) {
    $error_message = "Passwords do not match!";
}

// Check if email already exists (important re-check here)
$check_email_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check_email_stmt->bind_param("s", $formData['email']);
$check_email_stmt->execute();
$check_email_stmt->store_result();
if ($check_email_stmt->num_rows > 0) {
    $error_message = "Email already exists!";
}
$check_email_stmt->close();

if (!empty($error_message)) {
    $_SESSION['register_error'] = $error_message;
    // Put data back to session for pre-filling form if redirecting back
    $_SESSION['register_form_data'] = $formData;
    header("Location: register.php");
    exit();
}

// --- Data Sanitization and Hashing for DB Insert ---
$full_name = $conn->real_escape_string($formData['full_name']);
$email = $conn->real_escape_string($formData['email']);
$hashed_password = password_hash($formData['password'], PASSWORD_DEFAULT); // Hash password here!
$user_type = $conn->real_escape_string($formData['user_type']);
$start_date = $conn->real_escape_string($formData['start_date']);
$role_id = (int)$formData['role']; // Cast to int for safety
$work_experience = $conn->real_escape_string($formData['work_experience']);
$education = $conn->real_escape_string($formData['education']);

// Determine the final office value from the form data
$office_to_insert = $conn->real_escape_string($formData['office']);
if ($formData['office'] === 'other' && !empty($formData['custom_office'])) {
    $office_to_insert = $conn->real_escape_string($formData['custom_office']);
}


// --- Database Insertion ---
$conn->begin_transaction(); // Start transaction

try {
    // Insert new user
    $query = "INSERT INTO users (full_name, email, password, office, user_type, start_date, work_experience, education)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssss", $full_name, $email, $hashed_password, $office_to_insert, $user_type, $start_date, $work_experience, $education);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $user_id = $conn->insert_id;

        // Insert into userroles table
        $userroles_query = "INSERT INTO userroles (user_id, role_id, appointed_at) VALUES (?, ?, NOW())";
        $userroles_stmt = $conn->prepare($userroles_query);
        $userroles_stmt->bind_param("ii", $user_id, $role_id);
        $userroles_stmt->execute();

        if ($userroles_stmt->affected_rows > 0) {
            $conn->commit(); // Commit transaction if all successful
            $_SESSION['register_success'] = "User registered successfully with assigned role!";
        } else {
            throw new Exception("Error assigning role: " . $userroles_stmt->error);
        }
        $userroles_stmt->close();
    } else {
        throw new Exception("Error registering user: " . $stmt->error);
    }
    $stmt->close();
} catch (Exception $e) {
    $conn->rollback(); // Rollback on error
    $_SESSION['register_error'] = $e->getMessage();
    // Put data back to session for pre-filling form if redirecting back
    $_SESSION['register_form_data'] = $formData;
    header("Location: register.php"); // Redirect back to form on error
    exit();
}

// Clear session data used for registration after successful completion
unset($_SESSION['register_form_data']);
unset($_SESSION['departments_lookup']);
unset($_SESSION['roles_lookup']);

// Redirect to success page or the login page
header("Location: register.php"); // Or any other success/dashboard page
exit();
