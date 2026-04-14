<?php

/**
 * Opus Gallery – React/Vite SPA WordPress theme functions.
 */

// Enqueue the compiled React app assets (production or dev server).
function opus_gallery_enqueue_assets() {
    $theme_dir = get_template_directory();
    $theme_uri = get_template_directory_uri();

    // Check for Vite manifest (production build)
    $manifest_path = $theme_dir . '/assets/.vite/manifest.json';

    if (file_exists($manifest_path)) {
        // Production: read hashed filenames from Vite manifest
        $manifest = json_decode(file_get_contents($manifest_path), true);
        $main_entry = $manifest['src/main.tsx'] ?? null;

        if ($main_entry) {
            // Enqueue vendor chunks (imports) first
            $deps = [];
            if (!empty($main_entry['imports'])) {
                foreach ($main_entry['imports'] as $import_key) {
                    if (isset($manifest[$import_key])) {
                        $handle = 'opus-chunk-' . sanitize_title($import_key);
                        wp_enqueue_script(
                            $handle,
                            $theme_uri . '/assets/' . $manifest[$import_key]['file'],
                            array(),
                            null,
                            true
                        );
                        $deps[] = $handle;
                    }
                }
            }

            // JS bundle
            wp_enqueue_script(
                'opus-gallery-main',
                $theme_uri . '/assets/' . $main_entry['file'],
                $deps,
                null,
                true
            );

            // CSS bundle(s)
            if (!empty($main_entry['css'])) {
                foreach ($main_entry['css'] as $i => $css_file) {
                    wp_enqueue_style(
                        'opus-gallery-css-' . $i,
                        $theme_uri . '/assets/' . $css_file,
                        array(),
                        null
                    );
                }
            }
        }
    } else {
        // Fallback: use the known hashed filenames from the current build
        wp_enqueue_style(
            'opus-gallery-main',
            $theme_uri . '/assets/main-DDKrO0dj.css', // Updated after code splitting
            array(),
            '1.0.0'
        );

        wp_enqueue_script(
            'opus-gallery-main',
            $theme_uri . '/assets/main-BcRu9al5.js',
            array(),
            '1.0.0',
            true
        );
    }

    // Pass WordPress data to React
    wp_localize_script('opus-gallery-main', 'opusData', [
        'apiUrl'  => rest_url('lovable/v1'),
        'pageId'  => get_the_ID() ?: 0,
        'locale'  => get_locale(),
        'themeUrl' => $theme_uri,
    ]);
}
add_action('wp_enqueue_scripts', 'opus_gallery_enqueue_assets');

// Mark the script as type="module" so the browser handles ES imports.
function opus_gallery_script_type_module($tag, $handle, $src) {
    if ($handle === 'opus-gallery-main' || strpos($handle, 'opus-chunk-') === 0) {
        $tag = '<script type="module" crossorigin src="' . esc_url($src) . '"></script>' . "\n";
    }
    return $tag;
}
add_filter('script_loader_tag', 'opus_gallery_script_type_module', 10, 3);

// Rewrite rule: send all front-end requests to index.php so React Router
// can handle client-side routing.
function opus_gallery_rewrite_rules() {
    add_rewrite_rule('^(?!wp-admin|wp-login|wp-json|wp-content|wp-includes)(.*)$', 'index.php', 'top');
}
add_action('init', 'opus_gallery_rewrite_rules');

// Flush rewrite rules on theme activation.
function opus_gallery_activate() {
    opus_gallery_rewrite_rules();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'opus_gallery_activate');

// Add theme support basics.
function opus_gallery_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('html5', array('script', 'style'));
}
add_action('after_setup_theme', 'opus_gallery_theme_setup');

// Custom image sizes for gallery optimization.
function opus_add_image_sizes() {
    add_image_size('gallery_thumbnail', 400, 400, true);
    add_image_size('gallery_medium', 800, 600, true);
    add_image_size('hero_background', 1920, 1080, true);
}
add_action('after_setup_theme', 'opus_add_image_sizes');
