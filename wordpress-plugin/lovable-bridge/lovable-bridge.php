<?php
/**
 * Plugin Name: Lovable Bridge
 * Plugin URI: https://github.com/martiendejong/gallery-template
 * Description: WordPress plugin for seamless Lovable React to WordPress integration. Provides REST API for block-based content management, AI conversion pipeline, and server-side rendering for SEO.
 * Version: 1.0.0
 * Author: Martien de Jong
 * Author URI: https://martiendejong.nl
 * License: MIT
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Text Domain: lovable-bridge
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('LOVABLE_BRIDGE_VERSION', '1.0.0');
define('LOVABLE_BRIDGE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LOVABLE_BRIDGE_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Lovable Bridge Plugin Class
 */
class Lovable_Bridge {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * API Handler
     */
    private $api;

    /**
     * Block Registry
     */
    private $block_registry;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Load dependencies
        $this->load_dependencies();

        // Initialize components
        $this->api = Lovable_Bridge_API::get_instance();
        $this->block_registry = Lovable_Bridge_Block_Registry::get_instance();
        $this->block_renderer = Lovable_Bridge_Block_Renderer::get_instance();

        // Register hooks
        add_action('init', array($this, 'register_post_types'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // API
        require_once LOVABLE_BRIDGE_PLUGIN_DIR . 'includes/api/class-lovable-bridge-api.php';
        require_once LOVABLE_BRIDGE_PLUGIN_DIR . 'includes/api/class-page-manager.php';
        require_once LOVABLE_BRIDGE_PLUGIN_DIR . 'includes/api/class-block-storage.php';

        // Blocks
        require_once LOVABLE_BRIDGE_PLUGIN_DIR . 'includes/blocks/class-block-registry.php';
        require_once LOVABLE_BRIDGE_PLUGIN_DIR . 'includes/blocks/class-block-renderer.php';
    }

    /**
     * Block Renderer
     */
    private $block_renderer;

    /**
     * Register custom post types
     */
    public function register_post_types() {
        // Register Gallery custom post type
        register_post_type('gallery', array(
            'labels' => array(
                'name' => __('Galleries', 'lovable-bridge'),
                'singular_name' => __('Gallery', 'lovable-bridge'),
                'add_new' => __('Add New Gallery', 'lovable-bridge'),
                'add_new_item' => __('Add New Gallery', 'lovable-bridge'),
                'edit_item' => __('Edit Gallery', 'lovable-bridge'),
                'new_item' => __('New Gallery', 'lovable-bridge'),
                'view_item' => __('View Gallery', 'lovable-bridge'),
                'search_items' => __('Search Galleries', 'lovable-bridge'),
                'not_found' => __('No galleries found', 'lovable-bridge'),
                'not_found_in_trash' => __('No galleries found in trash', 'lovable-bridge')
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-images-alt2',
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'galleries')
        ));

        // Register Artist custom post type
        register_post_type('artist', array(
            'labels' => array(
                'name' => __('Artists', 'lovable-bridge'),
                'singular_name' => __('Artist', 'lovable-bridge'),
                'add_new' => __('Add New Artist', 'lovable-bridge'),
                'add_new_item' => __('Add New Artist', 'lovable-bridge'),
                'edit_item' => __('Edit Artist', 'lovable-bridge'),
                'new_item' => __('New Artist', 'lovable-bridge'),
                'view_item' => __('View Artist', 'lovable-bridge'),
                'search_items' => __('Search Artists', 'lovable-bridge'),
                'not_found' => __('No artists found', 'lovable-bridge'),
                'not_found_in_trash' => __('No artists found in trash', 'lovable-bridge')
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-admin-users',
            'supports' => array('title', 'editor', 'thumbnail'),
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'artists')
        ));
    }

    /**
     * Register custom taxonomies
     */
    public function register_taxonomies() {
        // Register Gallery Category taxonomy
        register_taxonomy('gallery_category', 'gallery', array(
            'labels' => array(
                'name' => __('Gallery Categories', 'lovable-bridge'),
                'singular_name' => __('Gallery Category', 'lovable-bridge'),
                'search_items' => __('Search Gallery Categories', 'lovable-bridge'),
                'all_items' => __('All Gallery Categories', 'lovable-bridge'),
                'edit_item' => __('Edit Gallery Category', 'lovable-bridge'),
                'update_item' => __('Update Gallery Category', 'lovable-bridge'),
                'add_new_item' => __('Add New Gallery Category', 'lovable-bridge'),
                'new_item_name' => __('New Gallery Category Name', 'lovable-bridge'),
                'menu_name' => __('Categories', 'lovable-bridge')
            ),
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'gallery-category')
        ));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Lovable Bridge', 'lovable-bridge'),
            __('Lovable Bridge', 'lovable-bridge'),
            'manage_options',
            'lovable-bridge',
            array($this, 'render_admin_page'),
            'dashicons-admin-generic',
            30
        );

        add_submenu_page(
            'lovable-bridge',
            __('Settings', 'lovable-bridge'),
            __('Settings', 'lovable-bridge'),
            'manage_options',
            'lovable-bridge-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Lovable Bridge', 'lovable-bridge'); ?></h1>
            <p><?php echo esc_html__('WordPress integration for Lovable React applications.', 'lovable-bridge'); ?></p>

            <div class="card">
                <h2><?php echo esc_html__('Quick Stats', 'lovable-bridge'); ?></h2>
                <p>
                    <strong><?php echo esc_html__('Version:', 'lovable-bridge'); ?></strong> <?php echo LOVABLE_BRIDGE_VERSION; ?><br>
                    <strong><?php echo esc_html__('API Endpoint:', 'lovable-bridge'); ?></strong> <?php echo rest_url('lovable/v1'); ?><br>
                    <strong><?php echo esc_html__('Registered Blocks:', 'lovable-bridge'); ?></strong> <?php echo count($this->block_registry->get_registered_blocks()); ?>
                </p>
            </div>

            <div class="card">
                <h2><?php echo esc_html__('API Documentation', 'lovable-bridge'); ?></h2>
                <p><?php echo esc_html__('REST API endpoints available at:', 'lovable-bridge'); ?> <code><?php echo rest_url('lovable/v1'); ?></code></p>
                <ul>
                    <li><code>GET /lovable/v1/verify</code> - <?php echo esc_html__('Verify plugin connection', 'lovable-bridge'); ?></li>
                    <li><code>GET /lovable/v1/pages</code> - <?php echo esc_html__('Get all pages with blocks', 'lovable-bridge'); ?></li>
                    <li><code>GET /lovable/v1/pages/{id}</code> - <?php echo esc_html__('Get page blocks', 'lovable-bridge'); ?></li>
                    <li><code>POST /lovable/v1/pages</code> - <?php echo esc_html__('Create page with blocks', 'lovable-bridge'); ?></li>
                    <li><code>PUT /lovable/v1/pages/{id}</code> - <?php echo esc_html__('Update page blocks', 'lovable-bridge'); ?></li>
                    <li><code>GET /lovable/v1/blocks</code> - <?php echo esc_html__('Get registered block types', 'lovable-bridge'); ?></li>
                    <li><code>GET /lovable/v1/galleries</code> - <?php echo esc_html__('Get all galleries', 'lovable-bridge'); ?></li>
                    <li><code>GET /lovable/v1/artists</code> - <?php echo esc_html__('Get all artists', 'lovable-bridge'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Lovable Bridge Settings', 'lovable-bridge'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('lovable_bridge_settings');
                do_settings_sections('lovable-bridge-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_lovable-bridge' !== $hook && 'lovable-bridge_page_lovable-bridge-settings' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'lovable-bridge-admin',
            LOVABLE_BRIDGE_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            LOVABLE_BRIDGE_VERSION
        );
    }
}

/**
 * Initialize plugin
 */
function lovable_bridge_init() {
    return Lovable_Bridge::get_instance();
}

// Initialize
add_action('plugins_loaded', 'lovable_bridge_init');

/**
 * Activation hook
 */
register_activation_hook(__FILE__, function() {
    // Flush rewrite rules
    flush_rewrite_rules();

    // Set default options
    add_option('lovable_bridge_version', LOVABLE_BRIDGE_VERSION);
});

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, function() {
    // Flush rewrite rules
    flush_rewrite_rules();
});
