<?php
/**
 * OPUS Gallery Theme Functions
 *
 * This theme uses React for the frontend. WordPress provides:
 * - Content via REST API
 * - Admin interface for content management
 * - Custom post types for galleries, artworks, artists
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme Setup
 */
function opus_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'script',
        'style',
    ]);

    // Custom image sizes for gallery
    add_image_size('opus-thumbnail', 400, 400, true);
    add_image_size('opus-medium', 800, 600, true);
    add_image_size('opus-large', 1600, 1200, false);
    add_image_size('opus-hero', 2400, 1600, false);

    // Register navigation menus
    register_nav_menus([
        'header-menu' => __('Header Menu', 'opus-gallery'),
    ]);
}
add_action('after_setup_theme', 'opus_theme_setup');

/**
 * Enqueue React App (Vite production build)
 *
 * Build output lives in theme/assets/ with a Vite manifest at assets/.vite/manifest.json.
 * The manifest 'file' values already include the 'assets/' prefix, so we only prepend
 * the theme URI root — NOT an extra '/assets/'.
 */
function opus_enqueue_react_app() {
    $theme_dir = get_template_directory();
    $theme_uri = get_template_directory_uri();

    // Check for Vite manifest (production build)
    $manifest_path = $theme_dir . '/assets/.vite/manifest.json';

    if (file_exists($manifest_path)) {
        // PRODUCTION: Read hashed filenames from Vite manifest
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
                            $theme_uri . '/' . $manifest[$import_key]['file'],
                            [],
                            null,
                            true
                        );
                        $deps[] = $handle;
                    }
                }
            }

            // Main JS bundle
            wp_enqueue_script(
                'opus-gallery-main',
                $theme_uri . '/' . $main_entry['file'],
                $deps,
                null,
                true
            );

            // CSS bundle(s)
            if (!empty($main_entry['css'])) {
                foreach ($main_entry['css'] as $i => $css_file) {
                    wp_enqueue_style(
                        'opus-gallery-css-' . $i,
                        $theme_uri . '/' . $css_file,
                        [],
                        null
                    );
                }
            }
        }
    } else {
        // DEVELOPMENT: Load from Vite dev server
        $dev_server = 'http://localhost:8080';

        wp_enqueue_script(
            'vite-client',
            $dev_server . '/@vite/client',
            [],
            null,
            false
        );

        wp_enqueue_script(
            'opus-app-dev',
            $dev_server . '/src/main.tsx',
            [],
            null,
            true
        );
    }

    // Pass WordPress data to React
    wp_localize_script('opus-gallery-main', 'opusData', [
        'apiUrl'   => rest_url('lovable/v1'),
        'wpApiUrl' => rest_url('opus/v1'),
        'siteUrl'  => home_url(),
        'nonce'    => wp_create_nonce('wp_rest'),
        'pageId'   => get_the_ID() ?: 0,
        'locale'   => get_locale(),
        'themeUrl' => $theme_uri,
    ]);
}
add_action('wp_enqueue_scripts', 'opus_enqueue_react_app');

/**
 * Mark scripts as type="module" for ES module support.
 */
function opus_gallery_script_type_module($tag, $handle, $src) {
    $module_handles = ['opus-gallery-main', 'vite-client', 'opus-app-dev'];
    if (in_array($handle, $module_handles) || strpos($handle, 'opus-chunk-') === 0) {
        $tag = '<script type="module" crossorigin src="' . esc_url($src) . '"></script>' . "\n";
    }
    return $tag;
}
add_filter('script_loader_tag', 'opus_gallery_script_type_module', 10, 3);

/**
 * Register Custom Post Types
 */
function opus_register_post_types() {
    // Gallery CPT
    register_post_type('opus_gallery', [
        'labels' => [
            'name'          => 'Galleries',
            'singular_name' => 'Gallery',
            'add_new_item'  => 'Add New Gallery',
            'edit_item'     => 'Edit Gallery',
        ],
        'public'       => true,
        'has_archive'  => true,
        'show_in_rest' => true,
        'supports'     => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'menu_icon'    => 'dashicons-images-alt2',
    ]);

    // Artwork CPT
    register_post_type('opus_artwork', [
        'labels' => [
            'name'          => 'Artworks',
            'singular_name' => 'Artwork',
            'add_new_item'  => 'Add New Artwork',
            'edit_item'     => 'Edit Artwork',
        ],
        'public'       => true,
        'has_archive'  => true,
        'show_in_rest' => true,
        'supports'     => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'menu_icon'    => 'dashicons-admin-customizer',
    ]);

    // Artist CPT
    register_post_type('opus_artist', [
        'labels' => [
            'name'          => 'Artists',
            'singular_name' => 'Artist',
            'add_new_item'  => 'Add New Artist',
            'edit_item'     => 'Edit Artist',
        ],
        'public'       => true,
        'has_archive'  => true,
        'show_in_rest' => true,
        'supports'     => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'menu_icon'    => 'dashicons-admin-users',
    ]);

    // Exhibition CPT
    register_post_type('opus_exhibition', [
        'labels' => [
            'name'          => 'Exhibitions',
            'singular_name' => 'Exhibition',
            'add_new_item'  => 'Add New Exhibition',
            'edit_item'     => 'Edit Exhibition',
        ],
        'public'       => true,
        'has_archive'  => true,
        'show_in_rest' => true,
        'supports'     => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'menu_icon'    => 'dashicons-calendar-alt',
    ]);
}
add_action('init', 'opus_register_post_types');

/**
 * Register Custom REST API Endpoints
 */
function opus_register_rest_routes() {
    register_rest_route('opus/v1', '/galleries', [
        'methods'             => 'GET',
        'callback'            => 'opus_get_galleries',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('opus/v1', '/galleries/(?P<id>\d+)', [
        'methods'             => 'GET',
        'callback'            => 'opus_get_gallery',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('opus/v1', '/artworks', [
        'methods'             => 'GET',
        'callback'            => 'opus_get_artworks',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('opus/v1', '/artworks/(?P<id>\d+)', [
        'methods'             => 'GET',
        'callback'            => 'opus_get_artwork',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('opus/v1', '/artists', [
        'methods'             => 'GET',
        'callback'            => 'opus_get_artists',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('opus/v1', '/exhibitions', [
        'methods'             => 'GET',
        'callback'            => 'opus_get_exhibitions',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('opus/v1', '/menu', [
        'methods'             => 'GET',
        'callback'            => 'opus_get_menu',
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'opus_register_rest_routes');

/**
 * REST API Callbacks
 */
function opus_get_galleries($request) {
    $query = new WP_Query([
        'post_type'      => 'opus_gallery',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ]);

    $galleries = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $galleries[] = opus_format_gallery_data(get_post());
        }
    }
    wp_reset_postdata();
    return rest_ensure_response($galleries);
}

function opus_get_gallery($request) {
    $post = get_post(absint($request['id']));
    if (!$post || $post->post_type !== 'opus_gallery') {
        return new WP_Error('not_found', 'Gallery not found', ['status' => 404]);
    }
    return rest_ensure_response(opus_format_gallery_data($post));
}

function opus_get_artworks($request) {
    $args = [
        'post_type'      => 'opus_artwork',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ];

    if (isset($request['gallery_id'])) {
        $args['meta_query'] = [[
            'key'     => 'gallery_id',
            'value'   => $request['gallery_id'],
            'compare' => '=',
        ]];
    }

    $artworks = [];
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $artworks[] = opus_format_artwork_data(get_post());
        }
    }
    wp_reset_postdata();
    return rest_ensure_response($artworks);
}

function opus_get_artwork($request) {
    $post = get_post(absint($request['id']));
    if (!$post || $post->post_type !== 'opus_artwork') {
        return new WP_Error('not_found', 'Artwork not found', ['status' => 404]);
    }
    return rest_ensure_response(opus_format_artwork_data($post));
}

function opus_get_artists($request) {
    $query = new WP_Query([
        'post_type'      => 'opus_artist',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ]);

    $artists = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $artists[] = opus_format_artist_data(get_post());
        }
    }
    wp_reset_postdata();
    return rest_ensure_response($artists);
}

function opus_get_exhibitions($request) {
    $query = new WP_Query([
        'post_type'      => 'opus_exhibition',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ]);

    $exhibitions = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $exhibitions[] = opus_format_exhibition_data(get_post());
        }
    }
    wp_reset_postdata();
    return rest_ensure_response($exhibitions);
}

function opus_get_menu($request) {
    $menu_items = wp_get_nav_menu_items('header-menu');
    if (!$menu_items) {
        return rest_ensure_response([]);
    }

    $formatted = [];
    foreach ($menu_items as $item) {
        $formatted[] = [
            'id'     => $item->ID,
            'title'  => $item->title,
            'url'    => $item->url,
            'slug'   => basename(parse_url($item->url, PHP_URL_PATH)),
            'target' => $item->target,
            'parent' => $item->menu_item_parent,
            'order'  => $item->menu_order,
        ];
    }
    return rest_ensure_response($formatted);
}

/**
 * Data Formatters
 */
function opus_format_gallery_data($post) {
    return [
        'id'             => $post->ID,
        'title'          => get_the_title($post),
        'slug'           => $post->post_name,
        'description'    => get_the_content(null, false, $post),
        'featured_image' => get_the_post_thumbnail_url($post, 'opus-large'),
        'thumbnail'      => get_the_post_thumbnail_url($post, 'opus-thumbnail'),
        'date'           => get_the_date('c', $post),
        'meta'           => get_post_meta($post->ID),
    ];
}

function opus_format_artwork_data($post) {
    return [
        'id'          => $post->ID,
        'title'       => get_the_title($post),
        'slug'        => $post->post_name,
        'description' => get_the_content(null, false, $post),
        'image'       => get_the_post_thumbnail_url($post, 'opus-large'),
        'thumbnail'   => get_the_post_thumbnail_url($post, 'opus-thumbnail'),
        'artist_id'   => get_post_meta($post->ID, 'artist_id', true),
        'gallery_id'  => get_post_meta($post->ID, 'gallery_id', true),
        'price'       => get_post_meta($post->ID, 'price', true),
        'year'        => get_post_meta($post->ID, 'year', true),
        'medium'      => get_post_meta($post->ID, 'medium', true),
        'dimensions'  => get_post_meta($post->ID, 'dimensions', true),
        'status'      => get_post_meta($post->ID, 'status', true),
        'date'        => get_the_date('c', $post),
    ];
}

function opus_format_artist_data($post) {
    return [
        'id'          => $post->ID,
        'name'        => get_the_title($post),
        'slug'        => $post->post_name,
        'bio'         => get_the_content(null, false, $post),
        'photo'       => get_the_post_thumbnail_url($post, 'opus-medium'),
        'nationality' => get_post_meta($post->ID, 'nationality', true),
        'birth_year'  => get_post_meta($post->ID, 'birth_year', true),
        'website'     => get_post_meta($post->ID, 'website', true),
        'date'        => get_the_date('c', $post),
    ];
}

function opus_format_exhibition_data($post) {
    return [
        'id'             => $post->ID,
        'title'          => get_the_title($post),
        'slug'           => $post->post_name,
        'description'    => get_the_content(null, false, $post),
        'featured_image' => get_the_post_thumbnail_url($post, 'opus-large'),
        'start_date'     => get_post_meta($post->ID, 'start_date', true),
        'end_date'       => get_post_meta($post->ID, 'end_date', true),
        'location'       => get_post_meta($post->ID, 'location', true),
        'status'         => get_post_meta($post->ID, 'status', true),
        'date'           => get_the_date('c', $post),
    ];
}

/**
 * Rewrite rules: send all front-end requests to index.php so React Router
 * can handle client-side routing.
 */
function opus_gallery_rewrite_rules() {
    add_rewrite_rule(
        '^(?!wp-admin|wp-login|wp-json|wp-content|wp-includes)(.*)$',
        'index.php',
        'top'
    );
}
add_action('init', 'opus_gallery_rewrite_rules');

// Flush rewrite rules on theme activation.
function opus_gallery_activate() {
    opus_gallery_rewrite_rules();
    opus_register_post_types();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'opus_gallery_activate');

/**
 * Add CORS Headers for Development
 */
function opus_add_cors_headers() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        header('Access-Control-Allow-Origin: http://localhost:8080');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-WP-Nonce');
    }
}
add_action('rest_api_init', 'opus_add_cors_headers');

/**
 * Expose page custom fields in REST API
 */
function opus_register_page_meta() {
    register_rest_field('page', 'meta_fields', [
        'get_callback' => function($post) {
            return [
                'hero_location'    => get_post_meta($post['id'], 'hero_location', true),
                'hero_subtitle'    => get_post_meta($post['id'], 'hero_subtitle', true),
                'hero_button_text' => get_post_meta($post['id'], 'hero_button_text', true),
            ];
        },
        'schema' => [
            'description' => 'Custom meta fields for the page',
            'type'        => 'object',
        ],
    ]);
}
add_action('rest_api_init', 'opus_register_page_meta');

// Disable WordPress theme file editor for security
define('DISALLOW_FILE_EDIT', true);
