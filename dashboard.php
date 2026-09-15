<?php
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$total_tasks = 0;
$completed_tasks = 0;
$pending_tasks = 0;
$hours_remaining = 0;

$stmt = $conn->prepare("SELECT status, estimated_hours FROM tasks WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$all_result = $stmt->get_result();

while ($row = $all_result->fetch_assoc()) {
    $total_tasks++;
    if ($row['status'] == 'Completed') {
        $completed_tasks++;
    } else {
        $pending_tasks++;
        $hours_remaining += $row['estimated_hours'];
    }
}

$percent_done = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;

$week_stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM tasks WHERE user_id = ? AND status = 'Completed' AND completed_at >= (NOW() - INTERVAL 7 DAY)");
$week_stmt->bind_param("i", $user_id);
$week_stmt->execute();
$completed_this_week = $week_stmt->get_result()->fetch_assoc()['cnt'];

$reminder_stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? AND status = 'Pending' AND deadline <= (CURDATE() + INTERVAL 3 DAY) ORDER BY deadline ASC");
$reminder_stmt->bind_param("i", $user_id);
$reminder_stmt->execute();
$reminders = $reminder_stmt->get_result();

$task_stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY FIELD(priority, 'High', 'Medium', 'Low'), deadline ASC");
$task_stmt->bind_param("i", $user_id);
$task_stmt->execute();
$tasks = $task_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Smart Study Planner</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="navbar">
    <h1>Smart Study Planner</h1>
    <div class="nav-right">
        <span>Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <a href="logout.php">Logout</a>
    </div>
</header>

<div class="container">

    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php echo $total_tasks; ?></h3>
            <p>Total Tasks</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $completed_tasks; ?></h3>
            <p>Completed</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $pending_tasks; ?></h3>
            <p>Pending</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $percent_done; ?>%</h3>
            <p>Progress</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $hours_remaining; ?>h</h3>
            <p>Hours Left</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $completed_this_week; ?></h3>
            <p>Done This Week</p>
        </div>
    </div>

    <?php if ($reminders->num_rows > 0) { ?>
    <div class="reminder-box">
        <h3>Upcoming Deadlines</h3>
        <ul>
        <?php while ($r = $reminders->fetch_assoc()) {
            $days_left = floor((strtotime($r['deadline']) - strtotime(date('Y-m-d'))) / 86400);
            if ($days_left < 0) {
                $label = "Overdue by " . abs($days_left) . " day(s)";
                $css_class = "overdue";
            } elseif ($days_left == 0) {
                $label = "Due today";
                $css_class = "due-soon";
            } else {
                $label = "Due in " . $days_left . " day(s)";
                $css_class = "due-soon";
            }
        ?>
            <li class="<?php echo $css_class; ?>">
                <?php echo htmlspecialchars($r['subject']) . " - " . htmlspecialchars($r['task_title']) . " (" . $label . ")"; ?>
            </li>
        <?php } ?>
        </ul>
    </div>
    <?php } ?>

    <div class="task-header">
        <h2>Your Study Tasks</h2>
        <a href="add_task.php" class="btn-add">+ Add New Task</a>
    </div>

    <table class="task-table">
        <tr>
            <th>Subject</th>
            <th>Task</th>
            <th>Priority</th>
            <th>Deadline</th>
            <th>Hours</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php if ($tasks->num_rows == 0) { ?>
        <tr><td colspan="7">No tasks yet. Add your first study task!</td></tr>
        <?php }
        while ($t = $tasks->fetch_assoc()) { ?>
        <tr class="<?php echo $t['status'] == 'Completed' ? 'row-completed' : ''; ?>">
            <td><?php echo htmlspecialchars($t['subject']); ?></td>
            <td><?php echo htmlspecialchars($t['task_title']); ?></td>
            <td><span class="badge priority-<?php echo strtolower($t['priority']); ?>"><?php echo $t['priority']; ?></span></td>
            <td><?php echo date("d M Y", strtotime($t['deadline'])); ?></td>
            <td><?php echo $t['estimated_hours']; ?>h</td>
            <td><span class="badge status-<?php echo strtolower($t['status']); ?>"><?php echo $t['status']; ?></span></td>
            <td class="actions">
                <a href="update_status.php?id=<?php echo $t['id']; ?>">
                    <?php echo $t['status'] == 'Completed' ? 'Mark Pending' : 'Mark Done'; ?>
                </a>
                <a href="edit_task.php?id=<?php echo $t['id']; ?>">Edit</a>
                <a href="delete_task.php?id=<?php echo $t['id']; ?>" class="delete-link" onclick="return confirmDelete();">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>

</div>

<script src="js/script.js"></script>
</body>
</html>
