<?php
/**
 * OPUS Gallery - ACF Field Groups Registration
 *
 * Place this file in: wp-content/mu-plugins/opus-acf-fields.php
 * OR include it from the theme's functions.php
 *
 * Requires: Advanced Custom Fields (free or PRO) plugin to be active.
 */

if (!defined('ABSPATH')) exit;

add_action('acf/init', 'opus_register_acf_field_groups');

function opus_register_acf_field_groups() {

    // =========================================================================
    // ARTWORK FIELDS
    // =========================================================================
    acf_add_local_field_group([
        'key'      => 'group_opus_artwork',
        'title'    => 'Artwork Details',
        'fields'   => [
            [
                'key'   => 'field_artwork_images',
                'label' => 'Artwork Images',
                'name'  => 'artwork_images',
                'type'  => 'gallery',
                'instructions' => 'Upload multiple images of this artwork (different angles, details, etc.)',
                'required' => 0,
                'return_format' => 'array',
                'preview_size' => 'medium',
                'library' => 'all',
                'min' => 0,
                'max' => 20,
            ],
            [
                'key'   => 'field_artwork_artist',
                'label' => 'Artist',
                'name'  => 'artist_id',
                'type'  => 'post_object',
                'instructions' => 'Select the artist who created this artwork',
                'post_type' => ['opus_artist'],
                'return_format' => 'id',
                'required' => 1,
            ],
            [
                'key'   => 'field_artwork_gallery',
                'label' => 'Gallery / Collection',
                'name'  => 'gallery_id',
                'type'  => 'post_object',
                'instructions' => 'Which gallery or collection does this artwork belong to?',
                'post_type' => ['opus_gallery'],
                'return_format' => 'id',
            ],
            [
                'key'   => 'field_artwork_year',
                'label' => 'Year Created',
                'name'  => 'year',
                'type'  => 'text',
                'instructions' => 'e.g. 2023 or c. 1920',
                'placeholder' => '2024',
            ],
            [
                'key'   => 'field_artwork_medium',
                'label' => 'Medium',
                'name'  => 'medium',
                'type'  => 'text',
                'instructions' => 'e.g. Oil on canvas, Bronze sculpture, Mixed media',
                'placeholder' => 'Oil on canvas',
            ],
            [
                'key'   => 'field_artwork_dimensions',
                'label' => 'Dimensions',
                'name'  => 'dimensions',
                'type'  => 'text',
                'instructions' => 'e.g. 120 x 80 cm',
                'placeholder' => '120 x 80 cm',
            ],
            [
                'key'   => 'field_artwork_price',
                'label' => 'Price',
                'name'  => 'price',
                'type'  => 'number',
                'instructions' => 'Price in EUR. Leave empty if price on request.',
                'prepend' => '€',
            ],
            [
                'key'     => 'field_artwork_status',
                'label'   => 'Status',
                'name'    => 'status',
                'type'    => 'select',
                'choices' => [
                    'available' => 'Available',
                    'sold'      => 'Sold',
                    'on-loan'   => 'On Loan',
                    'reserved'  => 'Reserved',
                ],
                'default_value' => 'available',
            ],
            [
                'key'   => 'field_artwork_provenance',
                'label' => 'Provenance',
                'name'  => 'provenance',
                'type'  => 'textarea',
                'instructions' => 'History of ownership (optional)',
                'rows' => 4,
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'opus_artwork',
                ],
            ],
        ],
        'menu_order' => 0,
        'position'   => 'normal',
        'style'      => 'default',
        'active'     => true,
    ]);

    // =========================================================================
    // ARTIST FIELDS
    // =========================================================================
    acf_add_local_field_group([
        'key'      => 'group_opus_artist',
        'title'    => 'Artist Details',
        'fields'   => [
            [
                'key'   => 'field_artist_photo',
                'label' => 'Artist Photo',
                'name'  => 'artist_photo',
                'type'  => 'image',
                'instructions' => 'Portrait or studio photo of the artist',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'library' => 'all',
            ],
            [
                'key'   => 'field_artist_portfolio',
                'label' => 'Portfolio Images',
                'name'  => 'portfolio_images',
                'type'  => 'gallery',
                'instructions' => 'Gallery of the artist\'s key works',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'min' => 0,
                'max' => 30,
            ],
            [
                'key'   => 'field_artist_nationality',
                'label' => 'Nationality',
                'name'  => 'nationality',
                'type'  => 'text',
                'placeholder' => 'Dutch',
            ],
            [
                'key'   => 'field_artist_birth_year',
                'label' => 'Birth Year',
                'name'  => 'birth_year',
                'type'  => 'number',
                'placeholder' => '1985',
            ],
            [
                'key'   => 'field_artist_website',
                'label' => 'Website',
                'name'  => 'website',
                'type'  => 'url',
                'placeholder' => 'https://artist-website.com',
            ],
            [
                'key'   => 'field_artist_statement',
                'label' => 'Artist Statement',
                'name'  => 'artist_statement',
                'type'  => 'wysiwyg',
                'instructions' => 'A personal statement from the artist about their work',
                'media_upload' => 0,
                'tabs' => 'all',
            ],
            [
                'key'     => 'field_artist_featured',
                'label'   => 'Featured Artist',
                'name'    => 'featured',
                'type'    => 'true_false',
                'instructions' => 'Show this artist prominently on the homepage',
                'default_value' => 0,
                'ui' => 1,
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'opus_artist',
                ],
            ],
        ],
        'menu_order' => 0,
        'position'   => 'normal',
        'style'      => 'default',
        'active'     => true,
    ]);

    // =========================================================================
    // GALLERY / COLLECTION FIELDS
    // =========================================================================
    acf_add_local_field_group([
        'key'      => 'group_opus_gallery',
        'title'    => 'Gallery Details',
        'fields'   => [
            [
                'key'   => 'field_gallery_hero_image',
                'label' => 'Hero / Banner Image',
                'name'  => 'hero_image',
                'type'  => 'image',
                'instructions' => 'Large banner image for this gallery page',
                'return_format' => 'array',
                'preview_size' => 'large',
            ],
            [
                'key'   => 'field_gallery_images',
                'label' => 'Gallery Images',
                'name'  => 'gallery_images',
                'type'  => 'gallery',
                'instructions' => 'Additional images showcasing this gallery / collection',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'min' => 0,
                'max' => 50,
            ],
            [
                'key'   => 'field_gallery_location',
                'label' => 'Location',
                'name'  => 'location',
                'type'  => 'text',
                'placeholder' => 'Utrecht, Nederland',
            ],
            [
                'key'   => 'field_gallery_subtitle',
                'label' => 'Subtitle / Tagline',
                'name'  => 'subtitle',
                'type'  => 'text',
                'placeholder' => 'Contemporary Dutch Masters',
            ],
            [
                'key'     => 'field_gallery_style',
                'label'   => 'Gallery Style',
                'name'    => 'gallery_style',
                'type'    => 'select',
                'choices' => [
                    'grid'    => 'Grid Layout',
                    'masonry' => 'Masonry Layout',
                    'slider'  => 'Slider / Carousel',
                ],
                'default_value' => 'grid',
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'opus_gallery',
                ],
            ],
        ],
        'menu_order' => 0,
        'position'   => 'normal',
        'style'      => 'default',
        'active'     => true,
    ]);

    // =========================================================================
    // EXHIBITION FIELDS
    // =========================================================================
    acf_add_local_field_group([
        'key'      => 'group_opus_exhibition',
        'title'    => 'Exhibition Details',
        'fields'   => [
            [
                'key'   => 'field_exhibition_hero_image',
                'label' => 'Exhibition Banner',
                'name'  => 'exhibition_banner',
                'type'  => 'image',
                'instructions' => 'Main promotional image for this exhibition',
                'return_format' => 'array',
                'preview_size' => 'large',
            ],
            [
                'key'   => 'field_exhibition_images',
                'label' => 'Exhibition Photos',
                'name'  => 'exhibition_images',
                'type'  => 'gallery',
                'instructions' => 'Photos from the exhibition',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'min' => 0,
                'max' => 30,
            ],
            [
                'key'   => 'field_exhibition_start_date',
                'label' => 'Start Date',
                'name'  => 'start_date',
                'type'  => 'date_picker',
                'display_format' => 'd/m/Y',
                'return_format' => 'Y-m-d',
                'required' => 1,
            ],
            [
                'key'   => 'field_exhibition_end_date',
                'label' => 'End Date',
                'name'  => 'end_date',
                'type'  => 'date_picker',
                'display_format' => 'd/m/Y',
                'return_format' => 'Y-m-d',
                'required' => 1,
            ],
            [
                'key'   => 'field_exhibition_location',
                'label' => 'Location',
                'name'  => 'location',
                'type'  => 'text',
                'placeholder' => 'OPUS Gallery, Utrecht',
            ],
            [
                'key'     => 'field_exhibition_status',
                'label'   => 'Status',
                'name'    => 'status',
                'type'    => 'select',
                'choices' => [
                    'upcoming' => 'Upcoming',
                    'current'  => 'Currently Running',
                    'past'     => 'Past Exhibition',
                ],
                'default_value' => 'upcoming',
            ],
            [
                'key'   => 'field_exhibition_artists',
                'label' => 'Featured Artists',
                'name'  => 'featured_artists',
                'type'  => 'post_object',
                'instructions' => 'Select artists participating in this exhibition',
                'post_type' => ['opus_artist'],
                'return_format' => 'id',
                'multiple' => 1,
            ],
            [
                'key'   => 'field_exhibition_opening_hours',
                'label' => 'Opening Hours',
                'name'  => 'opening_hours',
                'type'  => 'textarea',
                'instructions' => 'e.g. Tuesday - Saturday, 10:00 - 18:00',
                'rows' => 3,
            ],
            [
                'key'   => 'field_exhibition_admission',
                'label' => 'Admission Fee',
                'name'  => 'admission',
                'type'  => 'text',
                'placeholder' => 'Free / €10',
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'opus_exhibition',
                ],
            ],
        ],
        'menu_order' => 0,
        'position'   => 'normal',
        'style'      => 'default',
        'active'     => true,
    ]);

    // =========================================================================
    // HOMEPAGE FIELDS (for pages using the front-page template)
    // =========================================================================
    acf_add_local_field_group([
        'key'      => 'group_opus_homepage',
        'title'    => 'Homepage Settings',
        'fields'   => [
            [
                'key'   => 'field_hero_location',
                'label' => 'Hero Location Text',
                'name'  => 'hero_location',
                'type'  => 'text',
                'default_value' => 'Utrecht, Nederland',
            ],
            [
                'key'   => 'field_hero_subtitle',
                'label' => 'Hero Subtitle',
                'name'  => 'hero_subtitle',
                'type'  => 'textarea',
                'default_value' => 'Puur, Levendig & Menselijk — Recht uit de ziel van de kunstenaar',
                'rows' => 2,
            ],
            [
                'key'   => 'field_hero_button_text',
                'label' => 'Hero Button Text',
                'name'  => 'hero_button_text',
                'type'  => 'text',
                'default_value' => 'Bekijk Huidige Tentoonstelling',
            ],
            [
                'key'   => 'field_hero_background',
                'label' => 'Hero Background Image / Video',
                'name'  => 'hero_background',
                'type'  => 'file',
                'instructions' => 'Upload a background image or video for the hero section',
                'return_format' => 'array',
                'mime_types' => 'jpg, jpeg, png, webp, mp4, webm',
            ],
            [
                'key'   => 'field_featured_galleries',
                'label' => 'Featured Galleries',
                'name'  => 'featured_galleries',
                'type'  => 'post_object',
                'instructions' => 'Select galleries to feature on the homepage',
                'post_type' => ['opus_gallery'],
                'return_format' => 'id',
                'multiple' => 1,
            ],
            [
                'key'   => 'field_featured_artworks',
                'label' => 'Featured Artworks',
                'name'  => 'featured_artworks',
                'type'  => 'post_object',
                'instructions' => 'Select artworks to highlight on the homepage',
                'post_type' => ['opus_artwork'],
                'return_format' => 'id',
                'multiple' => 1,
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'page_type',
                    'operator' => '==',
                    'value'    => 'front_page',
                ],
            ],
        ],
        'menu_order' => 0,
        'position'   => 'normal',
        'style'      => 'default',
        'active'     => true,
    ]);
}

/**
 * Expose ACF fields in the REST API for all OPUS post types.
 */
add_filter('acf/settings/rest_api_format', function() {
    return 'standard';
});

function opus_add_acf_to_rest_api() {
    $post_types = ['opus_gallery', 'opus_artwork', 'opus_artist', 'opus_exhibition', 'page'];

    foreach ($post_types as $post_type) {
        register_rest_field($post_type, 'acf_fields', [
            'get_callback' => function($post) {
                if (function_exists('get_fields')) {
                    return get_fields($post['id']) ?: [];
                }
                return [];
            },
            'schema' => [
                'description' => 'ACF custom fields',
                'type' => 'object',
            ],
        ]);
    }
}
add_action('rest_api_init', 'opus_add_acf_to_rest_api');
