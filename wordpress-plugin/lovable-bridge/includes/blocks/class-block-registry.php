<?php
/**
 * Block Registry
 *
 * Centralized registry for all Lovable block types.
 * Defines block structure, validation, and rendering templates.
 */

class Lovable_Bridge_Block_Registry {

    private static $instance = null;
    private $blocks = array();

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->register_default_blocks();
    }

    /**
     * Register default block types
     */
    private function register_default_blocks() {
        // Hero Block
        $this->register_block('hero', array(
            'label' => __('Hero Section', 'lovable-bridge'),
            'description' => __('Large header section with title, subtitle, and image', 'lovable-bridge'),
            'icon' => 'format-image',
            'fields' => array(
                'title' => array(
                    'type' => 'string',
                    'label' => __('Title', 'lovable-bridge'),
                    'required' => true
                ),
                'subtitle' => array(
                    'type' => 'string',
                    'label' => __('Subtitle', 'lovable-bridge')
                ),
                'image' => array(
                    'type' => 'string',
                    'label' => __('Image URL', 'lovable-bridge')
                ),
                'cta_text' => array(
                    'type' => 'string',
                    'label' => __('Call to Action Text', 'lovable-bridge')
                ),
                'cta_link' => array(
                    'type' => 'string',
                    'label' => __('Call to Action Link', 'lovable-bridge')
                )
            ),
            'template' => 'hero'
        ));

        // Gallery Grid Block
        $this->register_block('gallery_grid', array(
            'label' => __('Gallery Grid', 'lovable-bridge'),
            'description' => __('Display galleries in a grid layout', 'lovable-bridge'),
            'icon' => 'images-alt2',
            'fields' => array(
                'gallery_ids' => array(
                    'type' => 'array',
                    'label' => __('Gallery IDs', 'lovable-bridge'),
                    'description' => __('Array of gallery post IDs to display', 'lovable-bridge')
                ),
                'columns' => array(
                    'type' => 'number',
                    'label' => __('Columns', 'lovable-bridge'),
                    'default' => 3
                )
            ),
            'template' => 'gallery-grid'
        ));

        // Artist Card Block
        $this->register_block('artist_card', array(
            'label' => __('Artist Card', 'lovable-bridge'),
            'description' => __('Display artist information', 'lovable-bridge'),
            'icon' => 'admin-users',
            'fields' => array(
                'artist_id' => array(
                    'type' => 'number',
                    'label' => __('Artist ID', 'lovable-bridge'),
                    'required' => true
                )
            ),
            'template' => 'artist-card'
        ));

        // CTA Block
        $this->register_block('cta', array(
            'label' => __('Call to Action', 'lovable-bridge'),
            'description' => __('Call to action button or section', 'lovable-bridge'),
            'icon' => 'megaphone',
            'fields' => array(
                'title' => array(
                    'type' => 'string',
                    'label' => __('Title', 'lovable-bridge')
                ),
                'text' => array(
                    'type' => 'string',
                    'label' => __('Text', 'lovable-bridge')
                ),
                'button_text' => array(
                    'type' => 'string',
                    'label' => __('Button Text', 'lovable-bridge'),
                    'required' => true
                ),
                'button_link' => array(
                    'type' => 'string',
                    'label' => __('Button Link', 'lovable-bridge'),
                    'required' => true
                )
            ),
            'template' => 'cta'
        ));

        // Text Block
        $this->register_block('text', array(
            'label' => __('Text Block', 'lovable-bridge'),
            'description' => __('Simple text content', 'lovable-bridge'),
            'icon' => 'editor-alignleft',
            'fields' => array(
                'content' => array(
                    'type' => 'string',
                    'label' => __('Content', 'lovable-bridge'),
                    'required' => true
                )
            ),
            'template' => 'text'
        ));

        // Image Block
        $this->register_block('image', array(
            'label' => __('Image Block', 'lovable-bridge'),
            'description' => __('Single image', 'lovable-bridge'),
            'icon' => 'format-image',
            'fields' => array(
                'src' => array(
                    'type' => 'string',
                    'label' => __('Image URL', 'lovable-bridge'),
                    'required' => true
                ),
                'alt' => array(
                    'type' => 'string',
                    'label' => __('Alt Text', 'lovable-bridge')
                ),
                'caption' => array(
                    'type' => 'string',
                    'label' => __('Caption', 'lovable-bridge')
                )
            ),
            'template' => 'image'
        ));
    }

    /**
     * Register a block type
     */
    public function register_block($type, $definition) {
        $this->blocks[$type] = $definition;
    }

    /**
     * Get all registered blocks
     */
    public function get_registered_blocks() {
        return $this->blocks;
    }

    /**
     * Get block definition
     */
    public function get_block($type) {
        return isset($this->blocks[$type]) ? $this->blocks[$type] : null;
    }

    /**
     * Validate block data against definition
     */
    public function validate_block($type, $data) {
        $definition = $this->get_block($type);

        if (!$definition) {
            return new WP_Error('invalid_block_type', 'Unknown block type: ' . $type);
        }

        $errors = array();

        // Check required fields
        foreach ($definition['fields'] as $field_name => $field_def) {
            if (!empty($field_def['required']) && empty($data[$field_name])) {
                $errors[] = sprintf(__('Field "%s" is required', 'lovable-bridge'), $field_name);
            }
        }

        if (!empty($errors)) {
            return new WP_Error('validation_failed', implode(', ', $errors));
        }

        return true;
    }
}
