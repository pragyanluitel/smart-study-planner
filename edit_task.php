<?php
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}
$task = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = trim($_POST['subject']);
    $task_title = trim($_POST['task_title']);
    $priority = $_POST['priority'];
    $deadline = $_POST['deadline'];
    $estimated_hours = $_POST['estimated_hours'];

    if ($subject == "" || $task_title == "" || $deadline == "") {
        $error = "Please fill in all required fields.";
    } else {
        $update = $conn->prepare("UPDATE tasks SET subject=?, task_title=?, priority=?, deadline=?, estimated_hours=? WHERE id=? AND user_id=?");
        $update->bind_param("sssssii", $subject, $task_title, $priority, $deadline, $estimated_hours, $id, $user_id);

        if ($update->execute()) {
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
    $task['subject'] = $subject;
    $task['task_title'] = $task_title;
    $task['priority'] = $priority;
    $task['deadline'] = $deadline;
    $task['estimated_hours'] = $estimated_hours;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Task - Smart Study Planner</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="form-container">
        <h2>Edit Study Task</h2>

        <?php if ($error != "") { ?>
            <p class="error-msg"><?php echo $error; ?></p>
        <?php } ?>

        <form method="POST" action="edit_task.php?id=<?php echo $task['id']; ?>" id="taskForm">
            <label>Subject</label>
            <input type="text" name="subject" value="<?php echo htmlspecialchars($task['subject']); ?>" required>

            <label>Task</label>
            <input type="text" name="task_title" value="<?php echo htmlspecialchars($task['task_title']); ?>" required>

            <label>Priority</label>
            <select name="priority">
                <option value="High" <?php echo $task['priority'] == 'High' ? 'selected' : ''; ?>>High</option>
                <option value="Medium" <?php echo $task['priority'] == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                <option value="Low" <?php echo $task['priority'] == 'Low' ? 'selected' : ''; ?>>Low</option>
            </select>

            <label>Deadline</label>
            <input type="date" name="deadline" value="<?php echo $task['deadline']; ?>" required>

            <label>Estimated Hours</label>
            <input type="number" name="estimated_hours" step="0.5" min="0.5" value="<?php echo $task['estimated_hours']; ?>">

            <button type="submit">Update Task</button>
        </form>

        <p><a href="dashboard.php">&larr; Back to Dashboard</a></p>
    </div>

<script src="js/script.js"></script>
</body>
</html>
