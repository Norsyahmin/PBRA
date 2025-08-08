<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

include '../mypbra_connect.php';

// Fetch all staff with role and department info
$sql = "
    SELECT 
        u.id,
        u.profile_pic,
        u.full_name,
        u.email,
        u.office,
        COALESCE(
            GROUP_CONCAT(CONCAT(r.name, ' (', d.name, ')') SEPARATOR ', '),
            'No role assigned'
        ) AS role_with_department
    FROM users u
    LEFT JOIN userroles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    LEFT JOIN departments d ON r.department_id = d.id
    GROUP BY u.id
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search All Staff</title>
    <link rel="stylesheet" href="staff.css">
    <style>
        .search-bar-container {
            padding: 20px 5%;
        }

        .search-bar-container input {
            width: 100%;
            padding: 12px 16px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
    </style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="page-title">
    <h1>🔍 Search All Staff</h1>
</div>

<div class="search-bar-container">
    <input type="text" id="staffSearchInput" placeholder="Search name, email, role, or department...">
</div>

<div class="staff-container">
    <?php foreach ($users as $user): ?>
        <?php
        $profilePicPath = $user['profile_pic'];
        if (!str_starts_with($profilePicPath, "../")) {
            $profilePicPath = "../" . $profilePicPath;
        }
        if (!file_exists($profilePicPath) || empty($user['profile_pic'])) {
            $profilePicPath = "../profile/images/default-profile.jpg";
        }
        ?>
        <div class="staff-card searchable-staff" onclick="location.href='../profile/profile.php?id=<?php echo htmlspecialchars($user['id']); ?>'">
            <img src="<?php echo htmlspecialchars($profilePicPath); ?>" alt="Profile Picture">
            <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role_with_department']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Office:</strong> <?php echo htmlspecialchars($user['office'] ?? 'Not Set'); ?></p>
        </div>
    <?php endforeach; ?>
</div>

<script>
    const searchInput = document.getElementById('staffSearchInput');
    const staffCards = document.querySelectorAll('.searchable-staff');

    searchInput.addEventListener('keyup', function () {
        const filter = searchInput.value.toLowerCase();
        staffCards.forEach(function (card) {
            const text = card.innerText.toLowerCase();
            card.style.display = text.includes(filter) ? "" : "none";
        });
    });
</script>

</body>
</html>
