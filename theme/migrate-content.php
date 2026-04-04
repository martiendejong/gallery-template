<?php
/**
 * Content Migration Script for Opus Gallery
 * Access via browser: http://localhost/gallery/wp-content/themes/opus-gallery/migrate-content.php
 * DELETE THIS FILE AFTER RUNNING.
 */

// Load WordPress
require_once dirname(__FILE__) . '/../../../wp-load.php';

if (!current_user_can('manage_options')) {
    wp_die('You must be logged in as an administrator to run this migration.');
}

$results = [];

// ──────────────────────────────────────
// 1. Create Home page with blocks
// ──────────────────────────────────────
$existing_home = get_page_by_path('home');
if ($existing_home) {
    $home_id = $existing_home->ID;
    $results[] = "Home page already exists (ID: $home_id) — updating blocks.";
} else {
    $home_id = wp_insert_post([
        'post_title'  => 'Home',
        'post_name'   => 'home',
        'post_status' => 'publish',
        'post_type'   => 'page',
    ]);
    $results[] = "Home page created (ID: $home_id).";
}

$home_blocks = [
    [
        'id'   => 'hero_1',
        'type' => 'hero',
        'data' => [
            'title'           => 'OPUS Art Gallery',
            'subtitle'        => 'Puur, Levendig & Menselijk — Recht uit de ziel van de kunstenaar',
            'location'        => 'Utrecht, Nederland',
            'buttonText'      => 'Bekijk Huidige Tentoonstelling',
            'backgroundImage' => ''
        ]
    ],
    [
        'id'   => 'features_1',
        'type' => 'feature_grid',
        'data' => [
            'features' => [
                [
                    'title'       => 'Curated Collections',
                    'description' => 'Carefully selected artworks from emerging and established artists',
                ],
                [
                    'title'       => 'Virtual Tours',
                    'description' => 'Explore our exhibitions from anywhere in the world',
                ],
                [
                    'title'       => 'Art Consultation',
                    'description' => 'Expert guidance for collectors and art enthusiasts',
                ]
            ],
            'columns' => 3
        ]
    ],
    [
        'id'   => 'cta_1',
        'type' => 'cta',
        'data' => [
            'title'       => 'Visit Our Gallery',
            'description' => 'Experience art in person at our Utrecht location',
            'buttonText'  => 'Book a Viewing',
            'buttonLink'  => '/book-viewing'
        ]
    ]
];

$home_layout = [
    ['id' => 'hero_1',     'order' => 1],
    ['id' => 'features_1', 'order' => 2],
    ['id' => 'cta_1',      'order' => 3],
];

update_post_meta($home_id, 'lovable_blocks', wp_json_encode($home_blocks));
update_post_meta($home_id, 'lovable_layout', wp_json_encode($home_layout));

// ──────────────────────────────────────
// 2. Create Galleries page
// ──────────────────────────────────────
$existing_galleries = get_page_by_path('galleries');
if ($existing_galleries) {
    $galleries_id = $existing_galleries->ID;
    $results[] = "Galleries page already exists (ID: $galleries_id) — updating blocks.";
} else {
    $galleries_id = wp_insert_post([
        'post_title'  => 'Galleries',
        'post_name'   => 'galleries',
        'post_status' => 'publish',
        'post_type'   => 'page',
    ]);
    $results[] = "Galleries page created (ID: $galleries_id).";
}

$galleries_blocks = [
    [
        'id'   => 'header_1',
        'type' => 'text_block',
        'data' => [
            'content'   => '<h1>Our Galleries</h1><p>Explore our curated collections of contemporary art</p>',
            'alignment' => 'center',
            'size'      => 'large'
        ]
    ]
];

update_post_meta($galleries_id, 'lovable_blocks', wp_json_encode($galleries_blocks));
update_post_meta($galleries_id, 'lovable_layout', wp_json_encode([['id' => 'header_1', 'order' => 1]]));

// ──────────────────────────────────────
// 3. Create Artists page
// ──────────────────────────────────────
$existing_artists = get_page_by_path('artists');
if ($existing_artists) {
    $artists_id = $existing_artists->ID;
    $results[] = "Artists page already exists (ID: $artists_id) — updating blocks.";
} else {
    $artists_id = wp_insert_post([
        'post_title'  => 'Artists',
        'post_name'   => 'artists',
        'post_status' => 'publish',
        'post_type'   => 'page',
    ]);
    $results[] = "Artists page created (ID: $artists_id).";
}

$artists_blocks = [
    [
        'id'   => 'header_2',
        'type' => 'text_block',
        'data' => [
            'content'   => '<h1>Featured Artists</h1><p>Meet the talented artists behind our collections</p>',
            'alignment' => 'center',
            'size'      => 'large'
        ]
    ]
];

update_post_meta($artists_id, 'lovable_blocks', wp_json_encode($artists_blocks));
update_post_meta($artists_id, 'lovable_layout', wp_json_encode([['id' => 'header_2', 'order' => 1]]));

// ──────────────────────────────────────
// 4. Set Home as front page
// ──────────────────────────────────────
update_option('show_on_front', 'page');
update_option('page_on_front', $home_id);
$results[] = "Home page set as static front page.";

// ──────────────────────────────────────
// Output results
// ──────────────────────────────────────
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><title>Migration Results</title>';
echo '<style>body{font-family:system-ui;max-width:600px;margin:40px auto;padding:20px;}';
echo '.ok{color:#16a34a;}.warn{color:#d97706;}h1{font-size:1.5em;}</style></head><body>';
echo '<h1>Opus Gallery — Content Migration</h1><ul>';
foreach ($results as $r) {
    echo "<li class='ok'>✓ $r</li>";
}
echo '</ul>';
echo '<h2>Verification</h2>';
echo '<p>Home blocks: <code>' . esc_html(get_post_meta($home_id, 'lovable_blocks', true)) . '</code></p>';
echo '<p><strong class="warn">⚠ Delete this file now:</strong> <code>migrate-content.php</code></p>';
echo '</body></html>';
