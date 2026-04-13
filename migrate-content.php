<?php
/**
 * OPUS Gallery - Content Migration Script
 *
 * Populates WordPress with initial gallery, artist, artwork, and exhibition data
 * matching the React app's static collection data.
 *
 * Usage:
 *   1. Open in browser: http://localhost/gallery/wp-content/themes/opus-gallery/migrate-content.php
 *   2. Or via WP-CLI: wp eval-file migrate-content.php
 *
 * NOTE: This script should only be run ONCE. Running it again will create duplicates.
 *       Check the admin dashboard before running.
 */

// Load WordPress if running directly
if (!defined('ABSPATH')) {
    // Find wp-load.php by traversing up from theme directory
    $wp_load = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
    if (file_exists($wp_load)) {
        require_once $wp_load;
    } else {
        die('Could not find wp-load.php. Run this from the WordPress installation.');
    }
}

// Only allow admins to run this
if (!current_user_can('manage_options') && php_sapi_name() !== 'cli') {
    wp_die('You must be an administrator to run this migration.');
}

echo "<pre>\n";
echo "========================================\n";
echo "OPUS Gallery Content Migration\n";
echo "========================================\n\n";

// =========================================================================
// ARTISTS
// =========================================================================
$artists_data = [
    [
        'name'        => 'Floyd Douglas',
        'bio'         => 'Floyd Douglas (1982, Amsterdam) challenges the boundaries of traditional art. His work is a spectacular mix of street-art, pop-art and mixed media, combining raw street energy with upper-class glamour.',
        'nationality' => 'Dutch',
        'birth_year'  => '1982',
        'speciality'  => 'Mixed Media, Pop Art',
    ],
    [
        'name'        => 'John Nieland',
        'bio'         => 'John Nieland (1966, Groningen) creates raw, unfiltered dialogue between his subconscious and the canvas. Inspired by the CoBrA movement, he paints without a preconceived plan — a creative trance where the artwork finds its way out through paint.',
        'nationality' => 'Dutch',
        'birth_year'  => '1966',
        'speciality'  => 'Abstract, Expressionism',
    ],
    [
        'name'        => 'Joost de Jonge',
        'bio'         => 'Joost de Jonge (1975, Utrecht) bridges the visible world and the metaphysical with monumental abstract canvases. His compositions are described as "musical" — colour as a gateway to stillness and meaning.',
        'nationality' => 'Dutch',
        'birth_year'  => '1975',
        'speciality'  => 'Painting, Mixed Media',
    ],
    [
        'name'        => 'Loes van Delft',
        'bio'         => 'Loes van Delft (1991, Drunen) is one of the most striking faces in contemporary Pop Art. Her work is an explosion of joy, freedom and a touch of bold humour. Winner of the Best Global Artist Award at Amsterdam International Art Fair at just 21.',
        'nationality' => 'Dutch',
        'birth_year'  => '1991',
        'speciality'  => 'Pop Art, Ceramics',
    ],
    [
        'name'        => 'Milan Sipistoo',
        'bio'         => 'Milan Sipistoo (1994, Utrecht) is a leading multidisciplinary artist exploring the boundary between visible and invisible. Known for his ultra-contemporary style — an explosion of colour, emotion and texture. Exhibited in Amsterdam, Dubai and New York.',
        'nationality' => 'Dutch',
        'birth_year'  => '1994',
        'speciality'  => 'Mixed Media, Painting',
    ],
    [
        'name'        => 'Peter Riezebos',
        'bio'         => 'Peter Riezebos (1980, Enschede) combines explosive force with deep personal introspection. His neo-expressionist style shows clear influences from CoBrA and Basquiat. Exhibited in Amsterdam, Shanghai and New York.',
        'nationality' => 'Dutch',
        'birth_year'  => '1980',
        'speciality'  => 'Neo-Expressionism, Mixed Media',
    ],
    [
        'name'        => 'Peter the Creator',
        'bio'         => 'Peter van der Wijk (1985, Friesland) creates art in three dimensions. Starting from a medical background, he discovered 3D printing as a powerful artistic medium. Themes of technology, biology and anthropology form the foundation of his sculptures.',
        'nationality' => 'Dutch',
        'birth_year'  => '1985',
        'speciality'  => 'Sculpture, 3D Art',
    ],
    [
        'name'        => 'Raymon Tiel',
        'bio'         => 'Raymon Tiel (1975, Rotterdam) is a multidisciplinary artist whose work dynamically combines form, colour and concept. His career reads as a journey of discovery — from decorating luxury cruise ships to creating striking contemporary paintings.',
        'nationality' => 'Dutch',
        'birth_year'  => '1975',
        'speciality'  => 'Mixed Media, Painting',
    ],
];

$artist_ids = [];
echo "Creating Artists...\n";

foreach ($artists_data as $artist) {
    // Check if artist already exists
    $existing = get_posts([
        'post_type'  => 'opus_artist',
        'title'      => $artist['name'],
        'numberposts' => 1,
    ]);

    if (!empty($existing)) {
        $artist_ids[$artist['name']] = $existing[0]->ID;
        echo "  [SKIP] {$artist['name']} (already exists, ID: {$existing[0]->ID})\n";
        continue;
    }

    $post_id = wp_insert_post([
        'post_title'   => $artist['name'],
        'post_content' => $artist['bio'],
        'post_status'  => 'publish',
        'post_type'    => 'opus_artist',
        'post_name'    => sanitize_title($artist['name']),
    ]);

    if (!is_wp_error($post_id)) {
        update_post_meta($post_id, 'nationality', $artist['nationality']);
        update_post_meta($post_id, 'birth_year', $artist['birth_year']);
        update_post_meta($post_id, 'speciality', $artist['speciality']);
        $artist_ids[$artist['name']] = $post_id;
        echo "  [OK] {$artist['name']} (ID: $post_id)\n";
    } else {
        echo "  [ERROR] {$artist['name']}: {$post_id->get_error_message()}\n";
    }
}

// =========================================================================
// GALLERIES
// =========================================================================
$galleries_data = [
    [
        'name'        => 'Current Exhibition',
        'description' => 'Works currently on display at OPUS Art Gallery — a curated selection from our represented artists.',
        'location'    => 'Utrecht, Nederland',
        'subtitle'    => 'Puur, Levendig & Menselijk — Recht uit de ziel van de kunstenaar',
    ],
    [
        'name'        => 'The Depot',
        'description' => 'Works in our secure storage facility, available for private viewing by appointment.',
        'location'    => 'Utrecht, Nederland',
        'subtitle'    => 'Available by Appointment',
    ],
];

$gallery_ids = [];
echo "\nCreating Galleries...\n";

foreach ($galleries_data as $gallery) {
    $existing = get_posts([
        'post_type'  => 'opus_gallery',
        'title'      => $gallery['name'],
        'numberposts' => 1,
    ]);

    if (!empty($existing)) {
        $gallery_ids[$gallery['name']] = $existing[0]->ID;
        echo "  [SKIP] {$gallery['name']} (already exists, ID: {$existing[0]->ID})\n";
        continue;
    }

    $post_id = wp_insert_post([
        'post_title'   => $gallery['name'],
        'post_content' => $gallery['description'],
        'post_status'  => 'publish',
        'post_type'    => 'opus_gallery',
        'post_name'    => sanitize_title($gallery['name']),
    ]);

    if (!is_wp_error($post_id)) {
        update_post_meta($post_id, 'location', $gallery['location']);
        update_post_meta($post_id, 'subtitle', $gallery['subtitle']);
        $gallery_ids[$gallery['name']] = $post_id;
        echo "  [OK] {$gallery['name']} (ID: $post_id)\n";
    } else {
        echo "  [ERROR] {$gallery['name']}: {$post_id->get_error_message()}\n";
    }
}

// =========================================================================
// ARTWORKS
// =========================================================================
$artworks_data = [
    [
        'title'       => 'The Godfather',
        'artist'      => 'Floyd Douglas',
        'gallery'     => 'Current Exhibition',
        'year'        => '2025',
        'medium'      => 'Mixed media',
        'dimensions'  => '165 × 125 cm',
        'price'       => 5200,
        'status'      => 'available',
        'description' => 'An iconic mixed-media piece deconstructing pop culture imagery with street art energy and high-end finish. Douglas combines luxury brand aesthetics with raw street art techniques to create powerful visual statements.',
    ],
    [
        'title'       => 'Excavation I',
        'artist'      => 'Joost de Jonge',
        'gallery'     => 'Current Exhibition',
        'year'        => '2022',
        'medium'      => 'Acrylic and oil on canvas',
        'dimensions'  => '150 × 120 cm',
        'price'       => 4500,
        'status'      => 'available',
        'description' => 'A monumental portrait emerging from thick impasto layers — the face both revealed and concealed by the archaeology of paint itself. Excavation I is the cornerstone of the Archeology of Personhood series.',
    ],
    [
        'title'       => 'Geometric Composition I',
        'artist'      => 'Joost de Jonge',
        'gallery'     => 'Current Exhibition',
        'year'        => '2025',
        'medium'      => 'Acrylic on canvas',
        'dimensions'  => '100 × 100 cm',
        'price'       => 2800,
        'status'      => 'available',
        'description' => 'A bold geometric abstraction in turquoise, mauve, and crimson — interlocking planes create a dynamic visual rhythm.',
    ],
    [
        'title'       => 'Flux I — Convergence',
        'artist'      => 'Joost de Jonge',
        'gallery'     => 'Current Exhibition',
        'year'        => '2023',
        'medium'      => 'Acrylic on canvas',
        'dimensions'  => '160 × 130 cm',
        'price'       => 5500,
        'status'      => 'available',
        'description' => 'Turquoise and amber collide in a torrential flow of paint — capturing the moment when two forces meet.',
    ],
    [
        'title'       => 'Archeology XXVI — The Watcher',
        'artist'      => 'Joost de Jonge',
        'gallery'     => 'Current Exhibition',
        'year'        => '2026',
        'medium'      => 'Acrylic on canvas',
        'dimensions'  => '100 × 100 cm',
        'price'       => 3500,
        'status'      => 'available',
        'description' => 'Muted lavenders, slate greys, and flashes of crimson converge around a spade-like sentinel.',
    ],
    [
        'title'       => 'Searching the Golden Fleece',
        'artist'      => 'Joost de Jonge',
        'gallery'     => 'Current Exhibition',
        'year'        => '2025',
        'medium'      => 'Acrylic on canvas',
        'dimensions'  => '120 × 120 cm',
        'price'       => 4200,
        'status'      => 'available',
        'description' => 'An explosive composition in reds, pinks, and gold — pyramidal forms evoke the mythical quest.',
    ],
    [
        'title'       => 'Astro Pjipje',
        'artist'      => 'Loes van Delft',
        'gallery'     => 'Current Exhibition',
        'year'        => '2026',
        'medium'      => 'Ceramics',
        'dimensions'  => '26 × 26 cm',
        'price'       => 4295,
        'status'      => 'sold',
        'description' => 'A playful ceramic piece featuring Loes\' signature character in an astronaut outfit — joy meets cosmic exploration.',
    ],
    [
        'title'       => 'Pjipje Playground',
        'artist'      => 'Loes van Delft',
        'gallery'     => 'Current Exhibition',
        'year'        => '2026',
        'medium'      => 'Ceramics',
        'dimensions'  => '31 × 31 cm',
        'price'       => 5295,
        'status'      => 'available',
        'description' => 'A vibrant ceramic artwork where Pjipje inhabits a colourful playground of imagination.',
    ],
    [
        'title'       => 'Pjipje Dream Ice Cream',
        'artist'      => 'Loes van Delft',
        'gallery'     => 'Current Exhibition',
        'year'        => '2026',
        'medium'      => 'Ceramics',
        'dimensions'  => '42 × 42 cm',
        'price'       => 6295,
        'status'      => 'available',
        'description' => 'The largest Pjipje ceramic — an ice cream dream in bold pop colours.',
    ],
    [
        'title'       => 'Untitled (Figure)',
        'artist'      => 'Milan Sipistoo',
        'gallery'     => 'Current Exhibition',
        'year'        => '2025',
        'medium'      => 'Mixed media on canvas',
        'dimensions'  => '180 × 140 cm',
        'price'       => 4800,
        'status'      => 'available',
        'description' => 'A striking figure emerges from layers of vibrant colour and raw texture — Sipistoo\'s signature explosive style.',
    ],
    [
        'title'       => 'Faire Temps',
        'artist'      => 'Milan Sipistoo',
        'gallery'     => 'Current Exhibition',
        'year'        => '2025',
        'medium'      => 'Mixed media on canvas',
        'dimensions'  => '120 × 100 cm',
        'price'       => 3200,
        'status'      => 'available',
        'description' => 'A contemplative composition where warm and cool tones create an atmospheric dialogue about time and space.',
    ],
    [
        'title'       => 'Medusa',
        'artist'      => 'Peter Riezebos',
        'gallery'     => 'Current Exhibition',
        'year'        => '2024',
        'medium'      => 'Acrylic on canvas',
        'dimensions'  => '150 × 120 cm',
        'price'       => 4100,
        'status'      => 'available',
        'description' => 'A powerful neo-expressionist interpretation of the Medusa myth — raw energy meets mythological depth.',
    ],
    [
        'title'       => 'Blue Spirit',
        'artist'      => 'Peter Riezebos',
        'gallery'     => 'Current Exhibition',
        'year'        => '2024',
        'medium'      => 'Acrylic on canvas',
        'dimensions'  => '100 × 80 cm',
        'price'       => 2900,
        'status'      => 'available',
        'description' => 'Ethereal blues and whites swirl around a central figure — introspection rendered in explosive brushstrokes.',
    ],
    [
        'title'       => 'Pink Panther',
        'artist'      => 'Raymon Tiel',
        'gallery'     => 'Current Exhibition',
        'year'        => '2025',
        'medium'      => 'Mixed media',
        'dimensions'  => '120 × 90 cm',
        'price'       => 3600,
        'status'      => 'available',
        'description' => 'A vibrant pop-culture deconstruction — the iconic cartoon character reimagined through Tiel\'s multidisciplinary lens.',
    ],
    [
        'title'       => 'Fragments of Identity',
        'artist'      => 'Raymon Tiel',
        'gallery'     => 'Current Exhibition',
        'year'        => '2025',
        'medium'      => 'Mixed media on canvas',
        'dimensions'  => '140 × 110 cm',
        'price'       => 4000,
        'status'      => 'available',
        'description' => 'Layered fragments of form and colour create a portrait of modern identity — both fractured and unified.',
    ],
];

echo "\nCreating Artworks...\n";

foreach ($artworks_data as $artwork) {
    $existing = get_posts([
        'post_type'  => 'opus_artwork',
        'title'      => $artwork['title'],
        'numberposts' => 1,
    ]);

    if (!empty($existing)) {
        echo "  [SKIP] {$artwork['title']} (already exists)\n";
        continue;
    }

    $post_id = wp_insert_post([
        'post_title'   => $artwork['title'],
        'post_content' => $artwork['description'],
        'post_status'  => 'publish',
        'post_type'    => 'opus_artwork',
        'post_name'    => sanitize_title($artwork['title']),
    ]);

    if (!is_wp_error($post_id)) {
        $artist_id = $artist_ids[$artwork['artist']] ?? 0;
        $gallery_id = $gallery_ids[$artwork['gallery']] ?? 0;

        update_post_meta($post_id, 'artist_id', $artist_id);
        update_post_meta($post_id, 'gallery_id', $gallery_id);
        update_post_meta($post_id, 'year', $artwork['year']);
        update_post_meta($post_id, 'medium', $artwork['medium']);
        update_post_meta($post_id, 'dimensions', $artwork['dimensions']);
        update_post_meta($post_id, 'price', $artwork['price']);
        update_post_meta($post_id, 'status', $artwork['status']);

        echo "  [OK] {$artwork['title']} by {$artwork['artist']} (ID: $post_id)\n";
    } else {
        echo "  [ERROR] {$artwork['title']}: {$post_id->get_error_message()}\n";
    }
}

// =========================================================================
// EXHIBITIONS
// =========================================================================
$exhibitions_data = [
    [
        'title'       => 'Puur, Levendig & Menselijk',
        'description' => 'The flagship exhibition at OPUS Art Gallery, Utrecht. A curated journey through the works of eight contemporary Dutch artists — from Floyd Douglas\' explosive pop art to Joost de Jonge\'s meditative abstractions.',
        'start_date'  => '2025-03-01',
        'end_date'    => '2026-06-30',
        'location'    => 'OPUS Art Gallery, Utrecht',
        'status'      => 'current',
        'admission'   => 'Free',
        'hours'       => "Tuesday - Saturday: 10:00 - 18:00\nSunday: 12:00 - 17:00\nMonday: Closed",
    ],
    [
        'title'       => 'Joost de Jonge: Archeology of Personhood',
        'description' => 'A solo exhibition exploring twenty years of Joost de Jonge\'s Archeology series — from the earliest excavations of identity to his latest monumental canvases. Features 15 works including previously unseen pieces from the artist\'s private collection.',
        'start_date'  => '2026-09-01',
        'end_date'    => '2026-12-15',
        'location'    => 'OPUS Art Gallery, Utrecht',
        'status'      => 'upcoming',
        'admission'   => 'Free',
        'hours'       => "Tuesday - Saturday: 10:00 - 18:00\nSunday: 12:00 - 17:00\nMonday: Closed",
    ],
];

echo "\nCreating Exhibitions...\n";

foreach ($exhibitions_data as $exhibition) {
    $existing = get_posts([
        'post_type'  => 'opus_exhibition',
        'title'      => $exhibition['title'],
        'numberposts' => 1,
    ]);

    if (!empty($existing)) {
        echo "  [SKIP] {$exhibition['title']} (already exists)\n";
        continue;
    }

    $post_id = wp_insert_post([
        'post_title'   => $exhibition['title'],
        'post_content' => $exhibition['description'],
        'post_status'  => 'publish',
        'post_type'    => 'opus_exhibition',
        'post_name'    => sanitize_title($exhibition['title']),
    ]);

    if (!is_wp_error($post_id)) {
        update_post_meta($post_id, 'start_date', $exhibition['start_date']);
        update_post_meta($post_id, 'end_date', $exhibition['end_date']);
        update_post_meta($post_id, 'location', $exhibition['location']);
        update_post_meta($post_id, 'status', $exhibition['status']);
        update_post_meta($post_id, 'admission', $exhibition['admission']);
        update_post_meta($post_id, 'opening_hours', $exhibition['hours']);

        echo "  [OK] {$exhibition['title']} (ID: $post_id)\n";
    } else {
        echo "  [ERROR] {$exhibition['title']}: {$post_id->get_error_message()}\n";
    }
}

// =========================================================================
// HOME PAGE WITH BLOCKS
// =========================================================================
echo "\nCreating Home Page with blocks...\n";

$home_page = get_page_by_title('Home');
if (!$home_page) {
    $home_blocks = [
        [
            'id'   => 'hero-1',
            'type' => 'hero',
            'data' => [
                'title'      => 'OPUS Art Gallery',
                'subtitle'   => 'Puur, Levendig & Menselijk — Recht uit de ziel van de kunstenaar',
                'location'   => 'Utrecht, Nederland',
                'buttonText' => 'Bekijk Huidige Tentoonstelling',
            ],
        ],
        [
            'id'   => 'cta-1',
            'type' => 'cta',
            'data' => [
                'title'       => 'Visit Our Gallery',
                'description' => 'Experience art in person at our Utrecht location',
                'buttonText'  => 'Book a Viewing',
                'buttonLink'  => '/book-viewing',
            ],
        ],
    ];

    $home_layout = [
        ['id' => 'hero-1'],
        ['id' => 'cta-1'],
    ];

    $home_id = wp_insert_post([
        'post_title'   => 'Home',
        'post_content' => 'Welcome to OPUS Art Gallery — Puur, Levendig & Menselijk.',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_name'    => 'home',
    ]);

    if (!is_wp_error($home_id)) {
        update_post_meta($home_id, 'lovable_blocks', wp_json_encode($home_blocks));
        update_post_meta($home_id, 'lovable_layout', wp_json_encode($home_layout));
        update_post_meta($home_id, 'hero_location', 'Utrecht, Nederland');
        update_post_meta($home_id, 'hero_subtitle', 'Puur, Levendig & Menselijk — Recht uit de ziel van de kunstenaar');
        update_post_meta($home_id, 'hero_button_text', 'Bekijk Huidige Tentoonstelling');

        // Set as static front page
        update_option('show_on_front', 'page');
        update_option('page_on_front', $home_id);

        echo "  [OK] Home page created (ID: $home_id) and set as front page\n";
    }
} else {
    echo "  [SKIP] Home page already exists (ID: {$home_page->ID})\n";
}

// =========================================================================
// SUMMARY
// =========================================================================
echo "\n========================================\n";
echo "Migration Complete!\n";
echo "========================================\n";
echo "Artists:      " . count($artists_data) . "\n";
echo "Galleries:    " . count($galleries_data) . "\n";
echo "Artworks:     " . count($artworks_data) . "\n";
echo "Exhibitions:  " . count($exhibitions_data) . "\n";
echo "Pages:        1 (Home)\n";
echo "\nNext steps:\n";
echo "  1. Upload artwork images via WP Admin > Artworks\n";
echo "  2. Upload artist portraits via WP Admin > Artists\n";
echo "  3. Install ACF for custom field management\n";
echo "  4. Visit http://localhost/gallery/ to verify\n";
echo "</pre>\n";
