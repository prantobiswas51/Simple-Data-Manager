<?php
/*
Plugin Name: Simple Data Manager
Description: A simple plugin to add, view, and edit data.
Version: 1.2
Author: Pranto Biswas
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Add menu items
add_action('admin_menu', 'sdm_register_menu');

function sdm_register_menu() {
    add_menu_page(
        'Simple Data Manager', // Page title
        'Data Manager',        // Menu title
        'manage_options',      // Capability
        'sdm-main-menu',       // Menu slug
        'sdm_main_page'        // Callback function
    );

    add_submenu_page(
        'sdm-main-menu',       // Parent slug
        'Add Data',            // Page title
        'Add Data',            // Menu title
        'manage_options',      // Capability
        'sdm-add-data',        // Menu slug
        'sdm_add_data_page'    // Callback function
    );

    add_submenu_page(
        'sdm-main-menu',       // Parent slug
        'View Data',           // Page title
        'View Data',           // Menu title
        'manage_options',      // Capability
        'sdm-view-data',       // Menu slug
        'sdm_view_data_page'   // Callback function
    );

    add_submenu_page(
        null,                  // No parent slug (hidden page)
        'Edit Data',           // Page title
        'Edit Data',           // Menu title
        'manage_options',      // Capability
        'sdm-edit-data',       // Menu slug
        'sdm_edit_data_page'   // Callback function
    );
}

// Main menu page callback
function sdm_main_page() {
    echo '<h1>Simple Data Manager</h1>';
    echo '<p>Welcome to the Simple Data Manager Plugin.</p>';
}

// Add data page callback
function sdm_add_data_page() {
    if (isset($_POST['sdm_data_submit'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'simple_data';

        $name = sanitize_text_field($_POST['sdm_name']);
        $email = sanitize_email($_POST['sdm_email']);
        $message = sanitize_textarea_field($_POST['sdm_message']);

        $wpdb->insert($table_name, array(
            'name' => $name,
            'email' => $email,
            'message' => $message
        ));

        echo '<div class="updated"><p>Data added successfully!</p></div>';
    }

    echo '<h1>Add Data</h1>';
    echo '<form method="POST" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="sdm_name">Name</label></th>
                    <td><input type="text" id="sdm_name" name="sdm_name" class="regular-text" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="sdm_email">Email</label></th>
                    <td><input type="email" id="sdm_email" name="sdm_email" class="regular-text" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="sdm_message">Message</label></th>
                    <td><textarea id="sdm_message" name="sdm_message" class="large-text" rows="5" required></textarea></td>
                </tr>
            </table>
            <input type="submit" name="sdm_data_submit" class="button-primary" value="Submit Data" />
          </form>';
}

// View data page callback
function sdm_view_data_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'simple_data';

    $results = $wpdb->get_results("SELECT * FROM $table_name");

    echo '<h1>View Data</h1>';
    echo '<table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th class="manage-column column-columnname" scope="col">ID</th>
                    <th class="manage-column column-columnname" scope="col">Name</th>
                    <th class="manage-column column-columnname" scope="col">Email</th>
                    <th class="manage-column column-columnname" scope="col">Message</th>
                    <th class="manage-column column-columnname" scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>';

    if ($results) {
        foreach ($results as $row) {
            echo '<tr>
                    <td>' . esc_html($row->id) . '</td>
                    <td>' . esc_html($row->name) . '</td>
                    <td>' . esc_html($row->email) . '</td>
                    <td>' . esc_html($row->message) . '</td>
                    <td><a href="admin.php?page=sdm-edit-data&id=' . $row->id . '">Edit</a></td>
                  </tr>';
        }
    } else {
        echo '<tr><td colspan="5">No data found.</td></tr>';
    }

    echo '</tbody></table>';
}

// Edit data page callback
function sdm_edit_data_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'simple_data';
    $id = intval($_GET['id']);

    if (isset($_POST['sdm_data_update'])) {
        $name = sanitize_text_field($_POST['sdm_name']);
        $email = sanitize_email($_POST['sdm_email']);
        $message = sanitize_textarea_field($_POST['sdm_message']);

        $wpdb->update(
            $table_name,
            array(
                'name' => $name,
                'email' => $email,
                'message' => $message
            ),
            array('id' => $id)
        );

        echo '<div class="updated"><p>Data updated successfully!</p></div>';
    }

    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));

    if ($row) {
        echo '<h1>Edit Data</h1>';
        echo '<form method="POST" action="">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="sdm_name">Name</label></th>
                        <td><input type="text" id="sdm_name" name="sdm_name" class="regular-text" value="' . esc_attr($row->name) . '" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="sdm_email">Email</label></th>
                        <td><input type="email" id="sdm_email" name="sdm_email" class="regular-text" value="' . esc_attr($row->email) . '" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="sdm_message">Message</label></th>
                        <td><textarea id="sdm_message" name="sdm_message" class="large-text" rows="5" required>' . esc_textarea($row->message) . '</textarea></td>
                    </tr>
                </table>
                <input type="submit" name="sdm_data_update" class="button-primary" value="Update Data" />
              </form>';
    } else {
        echo '<div class="error"><p>Data not found.</p></div>';
    }
}

// Activation hook to create the database table
register_activation_hook(__FILE__, 'sdm_create_table');

function sdm_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'simple_data';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name text NOT NULL,
        email text NOT NULL,
        message text NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
?>
