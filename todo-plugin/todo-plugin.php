<?php
/**
 * Plugin Name: Todo Plugin
 * Author: Bishal
 * Description: This is a demo plugin
 * Version: 1.0.0
 * Text Domain: new-plugin
 */

if(!defined('ABSPATH')){
    die;
}

function todo_plugin_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'todo_plugin';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id int(10) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        description varchar(255) NOT NULL,
        completed tinyint(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (id)
    ) $charset_collate";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'todo_plugin_create_table');


function todo_plugin_enqueue_scripts() {
    wp_enqueue_script('todo-plugin-script', plugin_dir_url(__FILE__) . 'includes/js/todo.js', ['jquery'], '1.0.0', true);

    wp_enqueue_style('todo-plugin-style', plugin_dir_url(__FILE__). 'includes/css/todo.css');

    wp_localize_script('todo-plugin-script', 'todo_ajax_url', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('todo_plugin_nonce')
    ]);
}

add_action('wp_enqueue_scripts', 'todo_plugin_enqueue_scripts');


function todo_plugin_add_task() {
    if(!wp_verify_nonce($_POST['nonce'] , 'todo_plugin_nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
    }

    $title = sanitize_text_field($_POST['title']);
    $description = sanitize_textarea_field($_POST['description']);

    global $wpdb;

    $table_name = $wpdb->prefix. 'todo_plugin';
    $wpdb->insert($table_name, [
        'title' => $title,
        'description' => $description,
        'completed' => 0
    ]);
    wp_send_json_success(['message' => 'Task added successfully']);
};

add_action("wp_ajax_todo_plugin_add_task", "todo_plugin_add_task");
add_action("wp_ajax_nopriv_todo_plugin_add_task", "todo_plugin_add_task");


function todo_plugin_fetch_tasks() {
    global $wpdb;
    $table_name = $wpdb->prefix. 'todo_plugin';
    $tasks = $wpdb->get_results("SELECT * FROM $table_name WHERE completed = 0 ORDER BY id DESC");

    wp_send_json_success(['tasks' => $tasks]);
}

add_action("wp_ajax_todo_plugin_fetch_tasks", "todo_plugin_fetch_tasks");
add_action("wp_ajax_nopriv_todo_plugin_fetch_tasks", "todo_plugin_fetch_tasks");



function todo_plugin_delete_task () {
    if(!wp_verify_nonce($_POST['nonce'], 'todo_plugin_nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
    }

    $id = intval($_POST['id']);
    global $wpdb;
    $table_name = $wpdb->prefix. 'todo_plugin';

    $wpdb->update($table_name, ['completed' => 1], ['id' => $id]);
    wp_send_json_success(['message' => 'Task completed successfully']);
}

add_action("wp_ajax_todo_plugin_delete_task", "todo_plugin_delete_task");
add_action("wp_ajax_nopriv_todo_plugin_delete_task", "todo_plugin_delete_task");



function todo_plugin_shortcode(){
    ob_start();
?>
    <div id="todo-plugin">
        <h3>My Todo List</h3>
        <div class="todo-input-group">
            <input type="text" id="todo-title" placeholder="Enter title">
            <textarea id="todo-description" placeholder="Enter description"></textarea>
            <button id="todo-add-btn">Add</button>
        </div>
        <div id="todo-list"></div>
    </div>
<?php
    return ob_get_clean();
};

add_shortcode("todo_plugin", "todo_plugin_shortcode");


