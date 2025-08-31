<?php
include '../mypbra_connect.php'; // your DB connection

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $meetingId = intval($_GET['id']);

    // Start transaction for safety
    $conn->begin_transaction();

    try {
        // Delete related chats
        $conn->query("DELETE FROM meeting_chats WHERE meeting_id = $meetingId");

        // Delete related participants
        $conn->query("DELETE FROM meeting_participants WHERE meeting_id = $meetingId");

        // Now delete the meeting itself
        $stmt = $conn->prepare("DELETE FROM meetings WHERE meeting_id = ?");
        $stmt->bind_param("i", $meetingId);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();

        echo "Meeting deleted successfully!";

    } catch (Exception $e) {
        // Rollback if something goes wrong
        $conn->rollback();
        echo "Error deleting meeting: " . $e->getMessage();
    }

    $conn->close();
    exit; // stop script here
}
?>
