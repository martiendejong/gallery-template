<?php
/**
 * Lovable Bridge API Handler
 *
 * Clean API proxy layer that abstracts WordPress functions.
 * Based on SEO God plugin architecture pattern.
 */

class Lovable_Bridge_API {

    private static $instance = null;
    private $namespace = 'lovable/v1';
    private $page_manager;
    private $block_storage;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Initialize managers
        $this->page_manager = new Lovable_Bridge_Page_Manager();
        $this->block_storage = new Lovable_Bridge_Block_Storage();

        // Register REST API routes
        add_action('rest_api_init', array($this, 'register_routes'));
        add_action('rest_api_init', array($this, 'add_cors_headers'));
    }

    /**
     * Add CORS headers for cross-origin requests
     */
    public function add_cors_headers() {
        remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
        add_filter('rest_pre_serve_request', function($value) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Nonce');

            if ('OPTIONS' === $_SERVER['REQUEST_METHOD']) {
                status_header(200);
                exit;
            }

            return $value;
        });
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Verify connection (public endpoint for plugin detection)
        register_rest_route($this->namespace, '/verify', array(
            'methods' => 'GET',
            'callback' => array($this, 'verify_connection'),
            'permission_callback' => '__return_true'
        ));

        // Pages endpoints
        register_rest_route($this->namespace, '/pages', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_pages'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route($this->namespace, '/pages/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_page'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route($this->namespace, '/pages', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_page'),
            'permission_callback' => array($this, 'check_permissions')
        ));

        register_rest_route($this->namespace, '/pages/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_page'),
            'permission_callback' => array($this, 'check_permissions')
        ));

        // Blocks registry
        register_rest_route($this->namespace, '/blocks', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_registered_blocks'),
            'permission_callback' => '__return_true'
        ));

        // Galleries
        register_rest_route($this->namespace, '/galleries', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_galleries'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route($this->namespace, '/galleries/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_gallery'),
            'permission_callback' => '__return_true'
        ));

        // Artists
        register_rest_route($this->namespace, '/artists', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_artists'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route($this->namespace, '/artists/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_artist'),
            'permission_callback' => '__return_true'
        ));
    }

    /**
     * Check permissions for write operations
     */
    public function check_permissions($request) {
        return current_user_can('edit_posts');
    }

    /**
     * Verify connection endpoint
     */
    public function verify_connection($request) {
        return array(
            'success' => true,
            'message' => 'Lovable Bridge plugin connected successfully',
            'version' => LOVABLE_BRIDGE_VERSION,
            'wordpress_version' => get_bloginfo('version'),
            'site_url' => get_site_url(),
            'site_name' => get_bloginfo('name'),
            'api_endpoint' => rest_url($this->namespace)
        );
    }

    /**
     * Get all pages with their blocks
     */
    public function get_pages($request) {
        $pages = $this->page_manager->get_all_pages();

        $result = array();
        foreach ($pages as $page) {
            $result[] = $this->format_page_response($page);
        }

        return array(
            'success' => true,
            'data' => $result,
            'total' => count($result)
        );
    }

    /**
     * Get single page with blocks
     */
    public function get_page($request) {
        $page_id = $request['id'];
        $page = $this->page_manager->get_page($page_id);

        if (!$page) {
            return new WP_Error('not_found', 'Page not found', array('status' => 404));
        }

        return array(
            'success' => true,
            'data' => $this->format_page_response($page)
        );
    }

    /**
     * Create new page with blocks
     */
    public function create_page($request) {
        $params = $request->get_json_params();

        $page_id = $this->page_manager->create_page(
            sanitize_text_field($params['title'] ?? 'Untitled Page'),
            $params['blocks'] ?? array()
        );

        if (is_wp_error($page_id)) {
            return new WP_Error('creation_failed', $page_id->get_error_message(), array('status' => 500));
        }

        $page = $this->page_manager->get_page($page_id);

        return array(
            'success' => true,
            'data' => $this->format_page_response($page),
            'message' => 'Page created successfully'
        );
    }

    /**
     * Update page blocks
     */
    public function update_page($request) {
        $page_id = $request['id'];
        $params = $request->get_json_params();

        $result = $this->page_manager->update_page_blocks($page_id, $params['blocks'] ?? array());

        if (is_wp_error($result)) {
            return new WP_Error('update_failed', $result->get_error_message(), array('status' => 500));
        }

        $page = $this->page_manager->get_page($page_id);

        return array(
            'success' => true,
            'data' => $this->format_page_response($page),
            'message' => 'Page updated successfully'
        );
    }

    /**
     * Get registered block types
     */
    public function get_registered_blocks($request) {
        $block_registry = Lovable_Bridge_Block_Registry::get_instance();
        $blocks = $block_registry->get_registered_blocks();

        return array(
            'success' => true,
            'data' => $blocks,
            'total' => count($blocks)
        );
    }

    /**
     * Get all galleries
     */
    public function get_galleries($request) {
        $galleries = get_posts(array(
            'post_type' => 'gallery',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));

        $result = array();
        foreach ($galleries as $gallery) {
            $result[] = $this->format_gallery_response($gallery);
        }

        return array(
            'success' => true,
            'data' => $result,
            'total' => count($result)
        );
    }

    /**
     * Get single gallery
     */
    public function get_gallery($request) {
        $gallery_id = $request['id'];
        $gallery = get_post($gallery_id);

        if (!$gallery || $gallery->post_type !== 'gallery') {
            return new WP_Error('not_found', 'Gallery not found', array('status' => 404));
        }

        return array(
            'success' => true,
            'data' => $this->format_gallery_response($gallery)
        );
    }

    /**
     * Get all artists
     */
    public function get_artists($request) {
        $artists = get_posts(array(
            'post_type' => 'artist',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));

        $result = array();
        foreach ($artists as $artist) {
            $result[] = $this->format_artist_response($artist);
        }

        return array(
            'success' => true,
            'data' => $result,
            'total' => count($result)
        );
    }

    /**
     * Get single artist
     */
    public function get_artist($request) {
        $artist_id = $request['id'];
        $artist = get_post($artist_id);

        if (!$artist || $artist->post_type !== 'artist') {
            return new WP_Error('not_found', 'Artist not found', array('status' => 404));
        }

        return array(
            'success' => true,
            'data' => $this->format_artist_response($artist)
        );
    }

    /**
     * Format page response
     */
    private function format_page_response($page) {
        $blocks = $this->block_storage->get_blocks($page->ID);

        return array(
            'id' => $page->ID,
            'title' => $page->post_title,
            'slug' => $page->post_name,
            'status' => $page->post_status,
            'blocks' => $blocks,
            'created_at' => $page->post_date,
            'modified_at' => $page->post_modified,
            'permalink' => get_permalink($page->ID)
        );
    }

    /**
     * Format gallery response
     */
    private function format_gallery_response($gallery) {
        return array(
            'id' => $gallery->ID,
            'title' => $gallery->post_title,
            'slug' => $gallery->post_name,
            'excerpt' => $gallery->post_excerpt,
            'content' => $gallery->post_content,
            'featured_image' => get_the_post_thumbnail_url($gallery->ID, 'full'),
            'permalink' => get_permalink($gallery->ID),
            'created_at' => $gallery->post_date,
            'modified_at' => $gallery->post_modified
        );
    }

    /**
     * Format artist response
     */
    private function format_artist_response($artist) {
        return array(
            'id' => $artist->ID,
            'name' => $artist->post_title,
            'slug' => $artist->post_name,
            'bio' => $artist->post_content,
            'image' => get_the_post_thumbnail_url($artist->ID, 'full'),
            'permalink' => get_permalink($artist->ID),
            'created_at' => $artist->post_date,
            'modified_at' => $artist->post_modified
        );
    }
}
