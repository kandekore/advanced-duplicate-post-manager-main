<?php

if (!defined('ABSPATH')) exit;

function dpm_handle_bulk_redirects() {
    if (
        $_SERVER['REQUEST_METHOD'] !== 'POST' ||
        !isset($_POST['bulk_delete_ids']) ||
        !check_admin_referer('dpm_bulk_action')
    ) return;

    $redirects = get_option('dpm_redirects', []);
    $errors = 0;
    $success = 0;

    foreach ($_POST['bulk_delete_ids'] as $id) {
        $id = intval($id);
        $post_type = get_post_type($id);

        // Media handling — delete only, no redirect
        if ($post_type === 'attachment') {
            wp_delete_attachment($id, true);
            $success++;
            continue;
        }

        // Regular post/page/CPT logic
        $manual = trim($_POST['redirect_manual'][$id] ?? '');
        $select = trim($_POST['redirect_select'][$id] ?? '');
        $redirect_to = esc_url_raw($manual ?: $select);

        // Validate URL
        if (!$redirect_to) {
            echo '<div class="error"><p>Missing redirect for post ID ' . $id . '</p></div>';
            $errors++;
            continue;
        }

        // Use relative URL if same domain
        if (strpos($redirect_to, home_url()) === 0) {
            $redirect_to = wp_make_link_relative($redirect_to);
        }

        // Build test URL for validation
        $test_url = $redirect_to;
        if (strpos($redirect_to, '/') === 0) {
            $test_url = home_url($redirect_to);
        }

        $headers = @get_headers($test_url);
        if (!$headers || strpos($headers[0], '404') !== false) {
            echo '<div class="error"><p>Redirect URL not valid for post ID ' . $id . '</p></div>';
            $errors++;
            continue;
        }

        $slug = get_post_field('post_name', $id);
        wp_trash_post($id);
        $redirects[] = ['from' => "/$slug", 'to' => $redirect_to];
        $success++;
    }

    update_option('dpm_redirects', $redirects);

    if ($success > 0) {
        echo '<div class="updated"><p>' . $success . ' items processed successfully.</p></div>';
    }

    if ($errors > 0) {
        echo '<div class="notice notice-error"><p>' . $errors . ' items could not be processed.</p></div>';
    }
}
