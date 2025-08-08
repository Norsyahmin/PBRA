<?php
session_start();
require '../mypbra_connect.php';

// ✅ Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $work_experience = $_POST['work_experience'];
    $education = $_POST['education'];

    // ✅ Debug: show posted data
    echo "Debug: user_id = $user_id<br>";
    echo "Debug: full_name = $full_name<br>";
    echo "Debug: email = $email<br>";

    // Handle profile picture upload
    if (!empty($_FILES["profile_pic"]["name"])) {
        $target_dir = "../profile/images/";

        // Generate unique file name
        $file_extension = pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION);
        $new_file_name = "profile_" . time() . "_" . $user_id . "." . $file_extension;
        $target_file = $target_dir . $new_file_name;
        $db_file_path = "profile/images/" . $new_file_name; // Store this in DB

        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            // ✅ Debug: image upload success
            echo "✅ File uploaded: $db_file_path<br>";

            // Update all fields including profile picture
            $query = "UPDATE users SET full_name = ?, email = ?, work_experience = ?, education = ?, profile_pic = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssi", $full_name, $email, $work_experience, $education, $db_file_path, $user_id);
        } else {
            echo "❌ File upload failed.<br>";
            exit();
        }
    } else {
        // ✅ Debug: no new profile picture
        echo "✅ No new profile picture uploaded<br>";

        // Update all fields without changing profile picture
        $query = "UPDATE users SET full_name = ?, email = ?, work_experience = ?, education = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $full_name, $email, $work_experience, $education, $user_id);
    }

    if ($stmt->execute()) {
        $_SESSION['full_name'] = $full_name;

        // Only update session profile_pic if a new one is uploaded
        if (!empty($_FILES["profile_pic"]["name"])) {
            $_SESSION['profile_pic'] = $db_file_path;
        }

        // ✅ Debug: success confirmation
        echo "✅ Profile updated successfully for user_id = $user_id<br>";

        // ✅ Redirect back to the correct profile
        echo "<script>window.location.href='profile.php?id=$user_id';</script>";
        exit();
    } else {
        echo "❌ Error updating profile: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
