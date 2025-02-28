jQuery(document).ready(function($) {
    const fetchTasks = () => {
        $.post(todo_ajax_url.ajaxUrl, {
            action: 'todo_plugin_fetch_tasks',
        }, function(response) {
            if (response.success) {
                const list = $('#todo-list');
                list.empty();
                response.data.tasks.forEach(task => {
                    const li = $('<li>').html(`
                        <strong>${task.title}</strong>
                        <p>${task.description}</p>
                        <button class="delete-task" data-id="${task.id}">Mark as Complete</button>
                    `);
                    list.append(li);
                });
            } else {
                alert(response.data.message);
            }
        });
    };

    const addTask = () => {
        const title = $('#todo-title').val();
        const description = $('#todo-description').val();

        if (!title || !description) {
            alert('Please fill in both title and description');
            return;
        }

        $.post(todo_ajax_url.ajaxUrl, {
            action: 'todo_plugin_add_task',
            title: title,
            description: description,
            nonce: todo_ajax_url.nonce,
        }, function(response) {
            if (response.success) {
                $('#todo-title').val('');
                $('#todo-description').val('');
                fetchTasks();
            } else {
                alert(response.data.message);
            }
        });
    };

    $(document).on('click', '.delete-task', function() {
        const id = $(this).data('id');
        $.post(todo_ajax_url.ajaxUrl, {
            action: 'todo_plugin_delete_task',
            id: id,
            nonce: todo_ajax_url.nonce,
        }, function(response) {
            if (response.success) {
                fetchTasks();
            } else {
                alert(response.data.message);
            }
        });
    });

    $('#todo-add-btn').on('click', function(e) {
        e.preventDefault();
        addTask();
    });

    // Initial load of tasks
    fetchTasks();
});