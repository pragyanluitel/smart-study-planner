function confirmDelete() {
    return confirm("Are you sure you want to delete this task?");
}

document.addEventListener("DOMContentLoaded", function () {
    const taskForm = document.getElementById("taskForm");

    if (taskForm) {
        taskForm.addEventListener("submit", function (event) {
            const titleField = document.querySelector('input[name="task_title"]');

            if (titleField.value.trim() === "") {
                alert("Task name cannot be empty.");
                event.preventDefault();
            }
        });
    }
});