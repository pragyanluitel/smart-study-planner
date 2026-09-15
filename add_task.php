<?php
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = trim($_POST['subject']);
    $task_title = trim($_POST['task_title']);
    $priority = $_POST['priority'];
    $deadline = $_POST['deadline'];
    $estimated_hours = $_POST['estimated_hours'];

    if ($subject == "" || $task_title == "" || $deadline == "") {
        $error = "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, subject, task_title, priority, deadline, estimated_hours) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $subject, $task_title, $priority, $deadline, $estimated_hours);

        if ($stmt->execute()) {
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Task - Smart Study Planner</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="form-container">
        <h2>Add New Study Task</h2>

        <?php if ($error != "") { ?>
            <p class="error-msg"><?php echo $error; ?></p>
        <?php } ?>

        <form method="POST" action="add_task.php" id="taskForm">
            <label>Subject</label>
            <input type="text" name="subject" placeholder="e.g. Mathematics" required>

            <label>Task</label>
            <input type="text" name="task_title" placeholder="e.g. Revise Chapter 5" required>

            <label>Priority</label>
            <select name="priority">
                <option value="High">High</option>
                <option value="Medium" selected>Medium</option>
                <option value="Low">Low</option>
            </select>

            <label>Deadline</label>
            <input type="date" name="deadline" required>

            <label>Estimated Hours</label>
            <input type="number" name="estimated_hours" step="0.5" min="0.5" value="1">

            <button type="submit">Add Task</button>
        </form>

        <p><a href="dashboard.php">&larr; Back to Dashboard</a></p>
    </div>

<script src="js/script.js"></script>
</body>
</html>
