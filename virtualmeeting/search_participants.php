<?php
header('Content-Type: application/json');
require_once "../mypbra_connect.php"; // DB connection

if (isset($_GET['q'])) {
    $q = "%" . $conn->real_escape_string($_GET['q']) . "%";

    // Use correct column: full_name instead of name
    $sql = "SELECT id, full_name, email FROM users 
            WHERE full_name LIKE ? OR email LIKE ?
            LIMIT 10";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $q, $q);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            "user_id" => $row["id"],
            "name"    => $row["full_name"], // rename for JS
            "email"   => $row["email"]
        ];
    }

    echo json_encode($data);
    $stmt->close();
}
$conn->close();
?>
