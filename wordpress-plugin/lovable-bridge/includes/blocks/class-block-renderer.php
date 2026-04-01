<?php
/**
 * Block Renderer
 *
 * Server-side rendering of blocks for SEO and initial page load.
 * Integrates with Block Registry to render registered block types.
 */

class Lovable_Bridge_Block_Renderer {

    private static $instance = null;
    private $block_registry;
    private $block_storage;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->block_registry = Lovable_Bridge_Block_Registry::get_instance();
        $this->block_storage = new Lovable_Bridge_Block_Storage();

        // Hook into WordPress content rendering
        add_filter('the_content', array($this, 'render_page_blocks'), 10);
    }

    /**
     * Render blocks for a page
     */
    public function render_page_blocks($content) {
        global $post;

        // Only process pages (not posts)
        if (!is_page() || !isset($post->ID)) {
            return $content;
        }

        // Get blocks for this page
        $blocks = $this->block_storage->get_blocks($post->ID);

        if (empty($blocks)) {
            return $content;
        }

        // Render all blocks
        $rendered = '';
        foreach ($blocks as $block) {
            $rendered .= $this->render_block($block);
        }

        // Return rendered blocks (append to or replace content)
        // If there's existing content, append blocks after it
        return $content . $rendered;
    }

    /**
     * Render a single block
     */
    public function render_block($block) {
        if (!isset($block['type']) || !isset($block['data'])) {
            return '';
        }

        $type = $block['type'];
        $data = $block['data'];

        // Get block definition
        $definition = $this->block_registry->get_block($type);

        if (!$definition) {
            return $this->render_error('Unknown block type: ' . esc_html($type));
        }

        // Validate block data
        $validation = $this->block_registry->validate_block($type, $data);

        if (is_wp_error($validation)) {
            return $this->render_error($validation->get_error_message());
        }

        // Get template path
        $template = $definition['template'] ?? $type;
        $template_path = $this->get_template_path($template);

        // Render using template if exists, otherwise use default
        if (file_exists($template_path)) {
            return $this->render_template($template_path, $data);
        }

        return $this->render_default($type, $data, $definition);
    }

    /**
     * Get template file path
     */
    private function get_template_path($template) {
        return LOVABLE_BRIDGE_PLUGIN_DIR . 'includes/blocks/templates/' . $template . '.php';
    }

    /**
     * Render block using PHP template
     */
    private function render_template($template_path, $data) {
        ob_start();

        // Extract data to variables
        extract($data);

        include $template_path;

        return ob_get_clean();
    }

    /**
     * Render block using default HTML structure
     */
    private function render_default($type, $data, $definition) {
        $html = '<div class="lovable-block lovable-block-' . esc_attr($type) . '">';

        // Render fields based on definition
        foreach ($definition['fields'] as $field_name => $field_def) {
            if (isset($data[$field_name])) {
                $value = $data[$field_name];

                switch ($field_def['type']) {
                    case 'string':
                        if ($field_name === 'content') {
                            $html .= '<div class="block-content">' . wp_kses_post($value) . '</div>';
                        } else {
                            $html .= '<div class="block-' . esc_attr($field_name) . '">' . esc_html($value) . '</div>';
                        }
                        break;

                    case 'number':
                        $html .= '<div class="block-' . esc_attr($field_name) . '">' . esc_html($value) . '</div>';
                        break;

                    case 'array':
                        $html .= '<div class="block-' . esc_attr($field_name) . '">';
                        if (is_array($value)) {
                            foreach ($value as $item) {
                                $html .= '<div class="block-item">' . esc_html($item) . '</div>';
                            }
                        }
                        $html .= '</div>';
                        break;
                }
            }
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render error message
     */
    private function render_error($message) {
        if (current_user_can('manage_options')) {
            return '<div class="lovable-block-error" style="background: #fff3cd; border: 1px solid #ffc107; padding: 12px; margin: 16px 0; border-radius: 4px;">'
                . '<strong>Block Error:</strong> ' . esc_html($message)
                . '</div>';
        }
        return '';
    }

    /**
     * Render blocks manually (for API or shortcode use)
     */
    public function render_blocks_for_page($page_id) {
        $blocks = $this->block_storage->get_blocks($page_id);

        if (empty($blocks)) {
            return '';
        }

        $output = '';
        foreach ($blocks as $block) {
            $output .= $this->render_block($block);
        }

        return $output;
    }
}
