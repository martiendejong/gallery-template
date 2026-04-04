<?php
class Lovable_Block_Registry {
    private static $blocks = [
        'hero' => [
            'fields' => ['title', 'subtitle', 'location', 'buttonText', 'backgroundImage'],
            'required' => ['title']
        ],
        'gallery_grid' => [
            'fields' => ['items', 'columns', 'spacing'],
            'required' => ['items']
        ],
        'artist_card' => [
            'fields' => ['name', 'image', 'bio', 'featured'],
            'required' => ['name', 'image']
        ],
        'cta' => [
            'fields' => ['title', 'description', 'buttonText', 'buttonLink'],
            'required' => ['title', 'buttonText']
        ],
        'feature_grid' => [
            'fields' => ['features', 'columns'],
            'required' => ['features']
        ],
        'text_block' => [
            'fields' => ['content', 'alignment', 'size'],
            'required' => ['content']
        ],
        'image_block' => [
            'fields' => ['src', 'alt', 'caption', 'width', 'height'],
            'required' => ['src', 'alt']
        ],
        'video_block' => [
            'fields' => ['src', 'poster', 'autoplay'],
            'required' => ['src']
        ],
        'testimonial' => [
            'fields' => ['quote', 'author', 'role', 'image'],
            'required' => ['quote', 'author']
        ],
        'contact_form' => [
            'fields' => ['fields', 'submitText', 'successMessage'],
            'required' => ['fields']
        ]
    ];

    public static function get_block_schema($type) {
        return self::$blocks[$type] ?? null;
    }

    public static function validate_block($block) {
        $schema = self::get_block_schema($block['type']);
        if (!$schema) {
            return new WP_Error('invalid_block_type', 'Unknown block type: ' . $block['type']);
        }

        foreach ($schema['required'] as $field) {
            if (empty($block['data'][$field])) {
                return new WP_Error('missing_required_field', "Missing required field: $field");
            }
        }

        return true;
    }

    public static function get_all_blocks() {
        return self::$blocks;
    }
}
