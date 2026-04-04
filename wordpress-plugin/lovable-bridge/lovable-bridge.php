<?php
/**
 * Plugin Name: Lovable Bridge
 * Description: Bridge between Lovable React components and WordPress blocks
 * Version: 1.0.0
 * Author: Martien de Jong
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/block-registry.php';

class Lovable_Bridge {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('rest_api_init', [$this, 'register_rest_routes']);
        add_action('init', [$this, 'register_block_types']);
        add_action('save_post', [$this, 'invalidate_cache']);
    }

    public function register_rest_routes() {
        register_rest_route('lovable/v1', '/page', [
            'methods' => 'POST',
            'callback' => [$this, 'create_page'],
            'permission_callback' => function() {
                return current_user_can('edit_posts');
            }
        ]);

        register_rest_route('lovable/v1', '/page/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_page_blocks'],
            'permission_callback' => '__return_true'
        ]);
    }

    public function register_block_types() {
        register_post_meta('page', 'lovable_blocks', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
            'default' => '[]'
        ]);

        register_post_meta('page', 'lovable_layout', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
            'default' => '[]'
        ]);
    }

    public function create_page($request) {
        $params = $request->get_json_params();

        $page_id = wp_insert_post([
            'post_title' => sanitize_text_field($params['title']),
            'post_status' => 'publish',
            'post_type' => 'page'
        ]);

        if (is_wp_error($page_id)) {
            return new WP_Error('page_creation_failed', $page_id->get_error_message());
        }

        // Validate blocks before saving
        if (!empty($params['blocks'])) {
            foreach ($params['blocks'] as $block) {
                $valid = Lovable_Block_Registry::validate_block($block);
                if (is_wp_error($valid)) {
                    wp_delete_post($page_id, true);
                    return $valid;
                }
            }
        }

        update_post_meta($page_id, 'lovable_blocks', wp_json_encode($params['blocks']));
        update_post_meta($page_id, 'lovable_layout', wp_json_encode($params['layout']));

        return [
            'success' => true,
            'page_id' => $page_id,
            'url' => get_permalink($page_id)
        ];
    }

    public function get_page_blocks($request) {
        $page_id = absint($request['id']);

        // Check transient cache first
        $cache_key = "lovable_blocks_{$page_id}";
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $blocks = get_post_meta($page_id, 'lovable_blocks', true);
        $layout = get_post_meta($page_id, 'lovable_layout', true);

        $result = [
            'blocks' => json_decode($blocks ?: '[]'),
            'layout' => json_decode($layout ?: '[]')
        ];

        // Cache for 1 hour
        set_transient($cache_key, $result, HOUR_IN_SECONDS);

        return $result;
    }

    public function invalidate_cache($post_id) {
        delete_transient("lovable_blocks_{$post_id}");
    }

    public function render_blocks($page_id) {
        $blocks = json_decode(get_post_meta($page_id, 'lovable_blocks', true) ?: '[]', true);
        $layout = json_decode(get_post_meta($page_id, 'lovable_layout', true) ?: '[]', true);

        ob_start();

        foreach ($layout as $layout_item) {
            $block = $this->find_block_by_id($blocks, $layout_item['id']);
            if ($block) {
                $this->render_single_block($block);
            }
        }

        return ob_get_clean();
    }

    private function find_block_by_id($blocks, $id) {
        foreach ($blocks as $block) {
            if (isset($block['id']) && $block['id'] === $id) {
                return $block;
            }
        }
        return null;
    }

    private function render_single_block($block) {
        $type = $block['type'];
        $template_file = plugin_dir_path(__FILE__) . "blocks/{$type}.php";

        if (file_exists($template_file)) {
            include $template_file;
        } else {
            echo "<!-- Block type '{$type}' not found -->";
        }
    }
}

Lovable_Bridge::get_instance();
