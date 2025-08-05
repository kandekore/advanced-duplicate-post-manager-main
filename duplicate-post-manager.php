<?php
/**
 * Plugin Name: Advanced Duplicate Post Manager
 * Description: Manage duplicate posts, pages, media, categories, and CPTs. Supports .htaccess 301 redirects, CSV exports, and more.
 * Version: 3.2.1
 * Author: Darren Kandekore
 * License: GPL2
 */

if (!defined('ABSPATH')) exit;

define('DPM_HTACCESS_FILE', ABSPATH . '.htaccess');
define('DPM_CSV_EXPORT', plugin_dir_path(__FILE__) . 'duplicates-export.csv');

add_action('admin_menu', function () {
    add_menu_page('Advanced Duplicate Post Manager', 'Duplicate Post Manager', 'manage_options', 'duplicate-post-manager', 'dpm_main_page', 'dashicons-backup', 80);
    add_submenu_page('duplicate-post-manager', '.htaccess Manager', '.htaccess Manager', 'manage_options', 'dpm-htaccess', 'dpm_htaccess_page');
    add_submenu_page('duplicate-post-manager', '.htaccess Backups', '.htaccess Backups', 'manage_options', 'dpm-htaccess-backups', 'dpm_htaccess_backups_page');
});
add_action('admin_enqueue_scripts', function($hook) {
    if (strpos($hook, 'duplicate-post-manager') !== false) {
        // Enqueue custom scripts if needed
    }
});

require_once plugin_dir_path(__FILE__) . 'includes/scanner.php';
require_once plugin_dir_path(__FILE__) . 'includes/handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/htaccess-backups.php';


function dpm_main_page() {
    $upload_dir = wp_upload_dir();
$csv_url = $upload_dir['baseurl'] . '/advanced-duplicate-posts.csv';
$csv_path = $upload_dir['basedir'] . '/advanced-duplicate-posts.csv';
    echo '<div class="wrap"><h1>Advanced Duplicate Post Manager</h1>';
    dpm_handle_bulk_redirects();

    echo '<p>Select the content type to scan for duplicates:</p>';
    echo '<form method="post">';
    wp_nonce_field('dpm_scan_form');
    echo '<select name="dpm_content_type">';
    echo '<option value="post">Posts</option><option value="page">Pages</option>';

    $cpts = get_post_types(['_builtin' => false], 'objects');
    foreach ($cpts as $cpt) {
        echo '<option value="' . esc_attr($cpt->name) . '">' . esc_html($cpt->labels->name) . '</option>';
    }
    echo '<option value="category">Categories</option><option value="media">Media Library</option>';
    echo '</select> ';
    submit_button('Scan for Duplicates');
    echo '</form>';

   if (isset($_POST['dpm_content_type']) && check_admin_referer('dpm_scan_form')) {
    $type = sanitize_text_field($_POST['dpm_content_type']);
    dpm_run_scan($type);

    // After scan runs and generates CSV
    if (file_exists($csv_path)) {
        echo '<p><a href="' . esc_url($csv_url) . '" class="button button-secondary">Download Duplicates CSV</a></p>';
    }
}
    echo '</div>';
}

function dpm_htaccess_page() {
    echo '<div class="wrap"><h1>.htaccess Manager</h1>';
    $redirects = get_option('dpm_redirects', []);

    echo '<p>Stored redirects: <strong>' . count($redirects) . '</strong></p>';

    if (!empty($redirects)) {
        echo '<form method="post">';
        wp_nonce_field('dpm_htaccess_tools');
        echo '<textarea rows="10" style="width:100%;font-family:monospace;">';
        echo "# BEGIN Post Redirects\n";
        foreach ($redirects as $rule) {
            echo "Redirect 301 {$rule['from']} {$rule['to']}\n";
        }
        echo "# END Post Redirects\n";
        echo '</textarea><br><br>';
        echo '<button class="button button-primary" name="write_htaccess" value="1">Write to .htaccess</button> ';
        echo '<button class="button" name="clear_redirects" value="1">Clear Redirect Rules</button> ';
        echo '<button class="button" name="download_htaccess" value="1">Download .htaccess Backup</button>';
        echo '</form>';
    } else {
        echo '<p>No redirect rules found.</p>';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('dpm_htaccess_tools')) {
        if (!empty($_POST['write_htaccess'])) dpm_write_htaccess();
        if (!empty($_POST['clear_redirects'])) {
            delete_option('dpm_redirects');
            echo '<div class="updated"><p>Redirect rules cleared.</p></div>';
        }
        if (!empty($_POST['download_htaccess'])) {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="htaccess-backup.txt"');
            echo "# BEGIN Post Redirects\n";
            foreach ($redirects as $rule) {
                echo "Redirect 301 {$rule['from']} {$rule['to']}\n";
            }
            echo "# END Post Redirects\n";
            exit;
        }
    }

    echo '</div>';
}

function dpm_write_htaccess() {
    $redirects = get_option('dpm_redirects', []);
    if (empty($redirects)) return;

    $output = "# BEGIN Post Redirects\n";
    foreach ($redirects as $rule) {
        $output .= "Redirect 301 {$rule['from']} {$rule['to']}\n";
    }
    $output .= "# END Post Redirects\n";

    if (file_exists(DPM_HTACCESS_FILE)) {
        copy(DPM_HTACCESS_FILE, DPM_HTACCESS_FILE . '.bak');
    }

    $existing = file_get_contents(DPM_HTACCESS_FILE);
    $existing = preg_replace('/# BEGIN Post Redirects.*?# END Post Redirects\s*/s', '', $existing);
    $new_content = trim($existing) . "\n\n" . $output;
    file_put_contents(DPM_HTACCESS_FILE, $new_content);
}
?>
