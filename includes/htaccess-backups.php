<?php

if (!defined('ABSPATH')) exit;

function dpm_htaccess_backups_page() {
    echo '<div class="wrap"><h1>.htaccess Backups</h1>';

    $upload_dir = wp_upload_dir();
    $backup_dir = trailingslashit($upload_dir['basedir']) . 'htaccess-backups/';
    $backup_url = trailingslashit($upload_dir['baseurl']) . 'htaccess-backups/';
    @mkdir($backup_dir, 0755, true);

    $latest_backup = $backup_dir . 'htaccess-backup.txt';

    // Handle actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('dpm_htaccess_backup')) {
        if (!empty($_POST['backup_now'])) {
            if (file_exists(ABSPATH . '.htaccess')) {
                copy(ABSPATH . '.htaccess', $latest_backup);
                echo '<div class="updated"><p>.htaccess backed up successfully.</p></div>';
            } else {
                echo '<div class="error"><p>.htaccess file not found.</p></div>';
            }
        }

        if (!empty($_POST['restore_backup'])) {
            if (file_exists($latest_backup)) {
                copy($latest_backup, ABSPATH . '.htaccess');
                echo '<div class="updated"><p>.htaccess restored from backup.</p></div>';
            } else {
                echo '<div class="error"><p>No backup found to restore.</p></div>';
            }
        }
    }

    echo '<form method="post">';
    wp_nonce_field('dpm_htaccess_backup');

    echo '<p><button class="button button-secondary" name="backup_now" value="1">Backup .htaccess Now</button> ';
    echo '<button class="button button-primary" name="restore_backup" value="1">Restore Last Backup</button></p>';

    if (file_exists($latest_backup)) {
        echo '<p>Last backup: <a href="' . esc_url($backup_url . 'htaccess-backup.txt') . '" target="_blank">View/Download Backup</a></p>';
    } else {
        echo '<p>No backup has been created yet.</p>';
    }

    echo '</form>';
    echo '</div>';
}
