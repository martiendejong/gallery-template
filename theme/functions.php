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

    // Use WP_CONTENT_DIR so the path stays within htdocs — XAMPP's PHP process
    // may not follow the junction back to C:\projects\ when using $theme_dir directly.
    // Try dist/ (current vite outDir) then assets/ (alternative layout).
    $manifest_path = WP_CONTENT_DIR . '/themes/' . get_stylesheet() . '/dist/.vite/manifest.json';
    if (!file_exists($manifest_path)) {
        $manifest_path = WP_CONTENT_DIR . '/themes/' . get_stylesheet() . '/assets/.vite/manifest.json';
    }

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
    $site_path = parse_url(home_url(), PHP_URL_PATH);
    $base_path = $site_path ? rtrim($site_path, '/') : '';

    wp_localize_script('opus-gallery-main', 'opusData', [
        'apiUrl'   => rest_url('lovable/v1'),
        'wpApiUrl' => rest_url('opus/v1'),
        'siteUrl'  => home_url(),
        'nonce'    => wp_create_nonce('wp_rest'),
        'pageId'   => get_the_ID() ?: 0,
        'locale'   => get_locale(),
        'themeUrl' => $theme_uri,
        'basePath' => $base_path ?: '/',
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

    register_rest_route('opus/v1', '/artists/(?P<id>\d+)', [
        'methods'             => 'GET',
        'callback'            => 'opus_get_artist',
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

    $meta_query = [];

    if (isset($request['gallery_id'])) {
        $meta_query[] = [
            'key'     => 'gallery_id',
            'value'   => absint($request['gallery_id']),
            'compare' => '=',
        ];
    }

    if (isset($request['artist_id'])) {
        $meta_query[] = [
            'key'     => 'artist_id',
            'value'   => absint($request['artist_id']),
            'compare' => '=',
        ];
    }

    if (isset($request['location'])) {
        $meta_query[] = [
            'key'     => 'location',
            'value'   => sanitize_text_field($request['location']),
            'compare' => '=',
        ];
    }

    if (!empty($meta_query)) {
        $args['meta_query'] = $meta_query;
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

function opus_get_artist($request) {
    $post = get_post(absint($request['id']));
    if (!$post || $post->post_type !== 'opus_artist') {
        return new WP_Error('not_found', 'Artist not found', ['status' => 404]);
    }
    return rest_ensure_response(opus_format_artist_data($post));
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
    // Resolve the menu assigned to the 'header-menu' location rather than looking
    // for a menu literally named 'header-menu'.
    $locations = get_nav_menu_locations();
    if (empty($locations['header-menu'])) {
        return rest_ensure_response([]);
    }
    $menu_items = wp_get_nav_menu_items($locations['header-menu']);
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
    // Resolve artist name eagerly to avoid N+1 queries on client
    $artist_id   = get_post_meta($post->ID, 'artist_id', true);
    $artist_post = $artist_id ? get_post((int) $artist_id) : null;

    // Gather all images: featured image + additional gallery images
    $images     = [];
    $main_image = get_the_post_thumbnail_url($post, 'opus-large');
    if ($main_image) {
        $images[] = $main_image;
    }
    $additional = get_post_meta($post->ID, 'additional_images', true);
    if (is_array($additional)) {
        foreach ($additional as $img_id) {
            $url = wp_get_attachment_image_url($img_id, 'opus-large');
            if ($url) {
                $images[] = $url;
            }
        }
    }

    // Keywords: stored as comma-separated string or serialized array
    $keywords_raw = get_post_meta($post->ID, 'keywords', true);
    $keywords     = is_array($keywords_raw)
        ? $keywords_raw
        : array_filter(array_map('trim', explode(',', $keywords_raw ?: '')));

    $status   = get_post_meta($post->ID, 'status', true) ?: 'available';
    $location = get_post_meta($post->ID, 'location', true);
    if (!$location) {
        $location = ($status === 'available') ? 'gallery' : 'depot';
    }

    return [
        'id'                   => $post->ID,
        'title'                => get_the_title($post),
        'slug'                 => $post->post_name,
        'description'          => get_the_content(null, false, $post),
        'short_description'    => get_post_meta($post->ID, 'short_description', true) ?: '',
        'extended_description' => get_post_meta($post->ID, 'extended_description', true) ?: '',
        'story'                => get_post_meta($post->ID, 'story', true) ?: '',
        'image'                => $main_image ?: '',
        'thumbnail'            => get_the_post_thumbnail_url($post, 'opus-thumbnail') ?: '',
        'images'               => $images,
        'artist_id'            => (int) $artist_id,
        'artist_name'          => $artist_post ? get_the_title($artist_post) : '',
        'gallery_id'           => (int) get_post_meta($post->ID, 'gallery_id', true),
        'price'                => get_post_meta($post->ID, 'price', true) ?: '',
        'price_label'          => get_post_meta($post->ID, 'price_label', true) ?: '',
        'year'                 => get_post_meta($post->ID, 'year', true) ?: '',
        'medium'               => get_post_meta($post->ID, 'medium', true) ?: '',
        'dimensions'           => get_post_meta($post->ID, 'dimensions', true) ?: '',
        'keywords'             => array_values($keywords),
        'category'             => get_post_meta($post->ID, 'category', true) ?: 'Painting',
        'status'               => $status,
        'location'             => $location,
        'art_revisionist_url'  => get_post_meta($post->ID, 'art_revisionist_url', true) ?: '',
        'provenance'           => get_post_meta($post->ID, 'provenance', true) ?: '',
        'exhibition'           => get_post_meta($post->ID, 'exhibition', true) ?: '',
        'qr_slug'              => get_post_meta($post->ID, 'qr_slug', true) ?: '',
        'silhouette'           => get_post_meta($post->ID, 'silhouette', true) ?: '',
        'date'                 => get_the_date('c', $post),
    ];
}

function opus_format_artist_data($post) {
    return [
        'id'          => $post->ID,
        'name'        => get_the_title($post),
        'slug'        => $post->post_name,
        'bio'         => get_the_content(null, false, $post),
        'photo'       => get_the_post_thumbnail_url($post, 'opus-medium') ?: '',
        'nationality' => get_post_meta($post->ID, 'nationality', true) ?: '',
        'speciality'  => get_post_meta($post->ID, 'speciality', true) ?: '',
        'birth_year'  => get_post_meta($post->ID, 'birth_year', true) ?: '',
        'website'     => get_post_meta($post->ID, 'website', true) ?: '',
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
            $hero_bg = get_the_post_thumbnail_url($post['id'], 'opus-hero');
            return [
                'hero_location'    => get_post_meta($post['id'], 'hero_location', true),
                'hero_subtitle'    => get_post_meta($post['id'], 'hero_subtitle', true),
                'hero_button_text' => get_post_meta($post['id'], 'hero_button_text', true),
                'hero_background'  => $hero_bg ?: '',
            ];
        },
        'schema' => [
            'description' => 'Custom meta fields for the page',
            'type'        => 'object',
        ],
    ]);
}
add_action('rest_api_init', 'opus_register_page_meta');

/**
 * Catch all unknown routes for React Router
 *
 * WordPress returns 404 for any URL that doesn't match a post/page.
 * For a React SPA we want WordPress to serve the app shell with 200
 * for every front-end route so React Router can handle it client-side.
 */
add_action('template_redirect', function () {
    if (is_404() && !is_admin()) {
        status_header(200);
        include get_template_directory() . '/index.php';
        exit;
    }
});

/**
 * Dequeue WordPress's bundled React on the frontend.
 *
 * Our Vite build bundles its own React 18. If WordPress (or a plugin) also
 * enqueues wp-element / react, the two copies conflict and cause
 * "Invalid hook call" errors (React error #300).
 */
function opus_dequeue_wp_react() {
    if (!is_admin()) {
        wp_dequeue_script('wp-element');
        wp_dequeue_script('react');
        wp_dequeue_script('react-dom');
        wp_dequeue_script('react-jsx-runtime');
    }
}
add_action('wp_enqueue_scripts', 'opus_dequeue_wp_react', 100);

// Disable WordPress theme file editor for security
define('DISALLOW_FILE_EDIT', true);

/**
 * Register ACF Field Groups for Content Admin UX
 *
 * These fields appear in the WordPress admin when editing CPT posts,
 * giving gallery staff a proper form for entering artwork/artist data.
 * Requires the ACF plugin to be active.
 */
function opus_register_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) return;

    // --- Artwork Fields ---
    acf_add_local_field_group([
        'key'      => 'group_opus_artwork',
        'title'    => 'Artwork Details',
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'opus_artwork']]],
        'position' => 'normal',
        'style'    => 'default',
        'fields'   => [
            ['key' => 'field_artist_id', 'label' => 'Artist', 'name' => 'artist_id', 'type' => 'post_object',
                'post_type' => ['opus_artist'], 'return_format' => 'id', 'required' => 1,
                'instructions' => 'Select the artist who created this work.'],
            ['key' => 'field_gallery_id', 'label' => 'Gallery', 'name' => 'gallery_id', 'type' => 'post_object',
                'post_type' => ['opus_gallery'], 'return_format' => 'id',
                'instructions' => 'Which gallery does this artwork belong to?'],
            ['key' => 'field_year', 'label' => 'Year', 'name' => 'year', 'type' => 'text', 'placeholder' => '2024'],
            ['key' => 'field_medium', 'label' => 'Medium', 'name' => 'medium', 'type' => 'text',
                'placeholder' => 'Acrylic and oil on canvas'],
            ['key' => 'field_dimensions', 'label' => 'Dimensions', 'name' => 'dimensions', 'type' => 'text',
                'placeholder' => '150 × 120 cm'],
            ['key' => 'field_category', 'label' => 'Category', 'name' => 'category', 'type' => 'select',
                'choices' => [
                    'Painting' => 'Painting', 'Abstract' => 'Abstract', 'Mixed Media' => 'Mixed Media',
                    'Figurative' => 'Figurative', 'Print' => 'Print', 'Sculpture' => 'Sculpture',
                    'Pop Art' => 'Pop Art', 'Ceramics' => 'Ceramics',
                ],
                'default_value' => 'Painting'],
            ['key' => 'field_price', 'label' => 'Price (EUR)', 'name' => 'price', 'type' => 'number',
                'instructions' => 'Price in euros (e.g. 4500). Leave empty for "Price on request".'],
            ['key' => 'field_price_label', 'label' => 'Price Label', 'name' => 'price_label', 'type' => 'text',
                'instructions' => 'Optional custom price display (e.g. "Price on request").'],
            ['key' => 'field_status', 'label' => 'Status', 'name' => 'status', 'type' => 'select',
                'choices' => ['available' => 'Available', 'sold' => 'Sold', 'on-loan' => 'On Loan'],
                'default_value' => 'available'],
            ['key' => 'field_location', 'label' => 'Location', 'name' => 'location', 'type' => 'select',
                'choices' => ['gallery' => 'Gallery (showroom)', 'depot' => 'Depot (storage)'],
                'default_value' => 'gallery'],
            ['key' => 'field_short_desc', 'label' => 'Short Description', 'name' => 'short_description',
                'type' => 'textarea', 'rows' => 3,
                'instructions' => 'Brief overview (shown in listings). Falls back to main content if empty.'],
            ['key' => 'field_extended_desc', 'label' => 'Extended Description', 'name' => 'extended_description',
                'type' => 'wysiwyg', 'media_upload' => 0, 'tabs' => 'visual',
                'instructions' => 'Full description shown on detail page.'],
            ['key' => 'field_story', 'label' => 'Story', 'name' => 'story', 'type' => 'wysiwyg',
                'media_upload' => 0, 'tabs' => 'visual',
                'instructions' => 'The narrative behind this artwork.'],
            ['key' => 'field_keywords', 'label' => 'Keywords', 'name' => 'keywords', 'type' => 'text',
                'instructions' => 'Comma-separated keywords for search/filtering.',
                'placeholder' => 'abstract, contemporary, blue, large'],
            ['key' => 'field_additional_images', 'label' => 'Additional Images', 'name' => 'additional_images',
                'type' => 'gallery', 'return_format' => 'id', 'preview_size' => 'thumbnail',
                'instructions' => 'Extra photos of the artwork. The Featured Image is the main photo.'],
            ['key' => 'field_art_revisionist_url', 'label' => 'Art Revisionist URL', 'name' => 'art_revisionist_url',
                'type' => 'url'],
            ['key' => 'field_provenance', 'label' => 'Provenance', 'name' => 'provenance', 'type' => 'textarea',
                'rows' => 2],
            ['key' => 'field_exhibition', 'label' => 'Exhibition History', 'name' => 'exhibition',
                'type' => 'text'],
            ['key' => 'field_qr_slug', 'label' => 'QR Code Slug', 'name' => 'qr_slug', 'type' => 'text',
                'instructions' => 'Custom slug for QR code landing page.'],
            ['key' => 'field_silhouette', 'label' => 'Silhouette / Technical Drawing', 'name' => 'silhouette',
                'type' => 'image', 'return_format' => 'url', 'preview_size' => 'thumbnail'],
        ],
    ]);

    // --- Artist Fields ---
    acf_add_local_field_group([
        'key'      => 'group_opus_artist',
        'title'    => 'Artist Details',
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'opus_artist']]],
        'position' => 'normal',
        'style'    => 'default',
        'fields'   => [
            ['key' => 'field_nationality', 'label' => 'Nationality', 'name' => 'nationality', 'type' => 'text',
                'placeholder' => 'Dutch'],
            ['key' => 'field_speciality', 'label' => 'Speciality', 'name' => 'speciality', 'type' => 'text',
                'placeholder' => 'Mixed Media, Painting'],
            ['key' => 'field_birth_year', 'label' => 'Birth Year', 'name' => 'birth_year', 'type' => 'number'],
            ['key' => 'field_website', 'label' => 'Website', 'name' => 'website', 'type' => 'url'],
        ],
    ]);
}
add_action('acf/init', 'opus_register_acf_fields');
