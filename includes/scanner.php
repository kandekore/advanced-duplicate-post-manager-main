<?php

if (!defined('ABSPATH')) exit;

function dpm_run_scan($type) {
    $csv_rows = [];
    $csv_rows[] = ['ID', 'Title/Name', 'Slug', 'Type', 'Status'];

    echo '<h2>Scanning for duplicates in: <em>' . esc_html(ucfirst($type)) . '</em></h2>';
    $duplicates = [];

    if ($type === 'category') {
        $terms = get_terms(['taxonomy' => 'category', 'hide_empty' => false]);
        $grouped = [];
        foreach ($terms as $term) {
            $key = strtolower($term->name);
            $grouped[$key][] = $term;
        }
        foreach ($grouped as $group) {
            if (count($group) > 1) $duplicates[] = $group;
        }

        if (empty($duplicates)) {
            echo '<p>No duplicate categories found.</p>';
        } else {
            echo '<table class="widefat striped"><thead><tr><th>Name</th><th>Slug</th></tr></thead><tbody>';
            foreach ($duplicates as $group) {
                foreach ($group as $term) {
                    $csv_rows[] = [$term->term_id, $term->name, $term->slug, 'category', '-'];
                    echo '<tr><td>' . esc_html($term->name) . '</td><td>' . esc_html($term->slug) . '</td></tr>';
                }
            }
            echo '</tbody></table>';
        }

       } elseif ($type === 'media') {
        $attachments = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => -1]);
        $grouped = [];

        foreach ($attachments as $att) {
            $file = get_attached_file($att->ID);
            if (!$file || !file_exists($file)) continue;
            $filename = strtolower(basename($file));
            $size = filesize($file);
            $key = $filename . '|' . $size;
            $grouped[$key][] = $att;
        }

        foreach ($grouped as $group) {
            if (count($group) > 1) $duplicates[] = $group;
        }

        if (empty($duplicates)) {
            echo '<p>No duplicate media files found.</p>';
        } else {
            echo '<form method="post">';
            wp_nonce_field('dpm_bulk_action');
            echo '<table class="widefat striped"><thead><tr><th>Delete</th><th>Filename</th><th>Size</th><th>Preview</th></tr></thead><tbody>';

            foreach ($duplicates as $group) {
                foreach ($group as $att) {
                    $file = get_attached_file($att->ID);
                    $size = filesize($file);
                    $url = wp_get_attachment_url($att->ID);

                    $csv_rows[] = [$att->ID, basename($file), '-', 'media', '-'];

                    echo '<tr>';
                    echo '<td><input type="checkbox" name="bulk_delete_ids[]" value="' . esc_attr($att->ID) . '"></td>';
                    echo '<td>' . esc_html(basename($file)) . '</td>';
                    echo '<td>' . esc_html(size_format($size)) . '</td>';
                    echo '<td><a href="' . esc_url($url) . '" target="_blank">View</a></td>';
                    echo '</tr>';
                }
            }

            echo '</tbody></table>';
            echo '<br><button type="submit" class="button button-primary">Delete Selected Media</button>';
            echo '</form>';
        }

    } else {
        $posts = get_posts([
            'post_type' => $type,
            'post_status' => 'publish',
            'numberposts' => -1
        ]);

        $grouped = [];
        foreach ($posts as $post) {
            $key = strtolower(trim($post->post_title));
            $grouped[$key][] = $post;
        }

        foreach ($grouped as $group) {
            if (count($group) > 1) $duplicates[] = $group;
        }

        if (empty($duplicates)) {
            echo '<p>No duplicates found for this content type.</p>';
        } else {
            echo '<form method="post">';
            wp_nonce_field('dpm_bulk_action');
            echo '<table class="widefat striped"><thead><tr><th>Delete</th><th>Title</th><th>Slug</th><th>Redirect To</th><th>Manual URL</th></tr></thead><tbody>';

            foreach ($duplicates as $group) {
                foreach ($group as $post) {
                    $csv_rows[] = [$post->ID, $post->post_title, $post->post_name, $post->post_type, $post->post_status];
                    $others = array_filter($group, fn($p) => $p->ID !== $post->ID);

                    echo '<tr>';
                    echo '<td><input type="checkbox" name="bulk_delete_ids[]" value="' . esc_attr($post->ID) . '"></td>';
                    echo '<td>' . esc_html($post->post_title) . '</td>';
                    echo '<td>' . esc_html($post->post_name) . '</td>';
                    echo '<td><select name="redirect_select[' . esc_attr($post->ID) . ']"><option value="">-- Select --</option>';
                    foreach ($others as $target) {
                        $url = wp_make_link_relative(get_permalink($target->ID));
                        echo '<option value="' . esc_attr($url) . '">' . esc_html($target->post_name) . '</option>';
                    }
                    echo '</select></td>';
                    echo '<td><input type="text" name="redirect_manual[' . esc_attr($post->ID) . ']" style="width:100%" placeholder="https://..."></td>';
                    echo '</tr>';
                }
            }

            echo '</tbody></table>';
            echo '<br><button type="submit" class="button button-primary">Delete Selected & Redirect</button>';
            echo '</form>';
        }
    }

    // ✅ Save the CSV
    if (count($csv_rows) > 1) { // Header + at least one result
        $upload_dir = wp_upload_dir();
        $csv_path = trailingslashit($upload_dir['basedir']) . 'advanced-duplicate-posts.csv';
        $fp = fopen($csv_path, 'w');
        foreach ($csv_rows as $row) {
            fputcsv($fp, $row, ',', '"', "\\");

        }
        fclose($fp);
    }
}
