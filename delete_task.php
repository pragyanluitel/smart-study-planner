<?php
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();

header("Location: dashboard.php");
exit();
?>
