<?php
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT status FROM tasks WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $task = $result->fetch_assoc();

    if ($task['status'] == 'Pending') {
        $update = $conn->prepare("UPDATE tasks SET status='Completed', completed_at=NOW() WHERE id=? AND user_id=?");
    } else {
        $update = $conn->prepare("UPDATE tasks SET status='Pending', completed_at=NULL WHERE id=? AND user_id=?");
    }
    $update->bind_param("ii", $id, $user_id);
    $update->execute();
}

header("Location: dashboard.php");
exit();
?>
