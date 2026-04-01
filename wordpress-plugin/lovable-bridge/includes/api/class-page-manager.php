<?php
/**
 * Page Manager
 *
 * Abstracts WordPress page operations.
 * Clean interface between API and WordPress functions.
 */

class Lovable_Bridge_Page_Manager {

    private $block_storage;

    public function __construct() {
        $this->block_storage = new Lovable_Bridge_Block_Storage();
    }

    /**
     * Get all pages
     */
    public function get_all_pages() {
        return get_posts(array(
            'post_type' => 'page',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'orderby' => 'modified',
            'order' => 'DESC'
        ));
    }

    /**
     * Get single page
     */
    public function get_page($page_id) {
        $page = get_post($page_id);

        if (!$page || $page->post_type !== 'page') {
            return null;
        }

        return $page;
    }

    /**
     * Create new page
     */
    public function create_page($title, $blocks = array()) {
        $page_data = array(
            'post_title' => $title,
            'post_content' => '', // Content comes from blocks
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => get_current_user_id() ?: 1
        );

        $page_id = wp_insert_post($page_data, true);

        if (is_wp_error($page_id)) {
            return $page_id;
        }

        // Store blocks
        if (!empty($blocks)) {
            $this->block_storage->save_blocks($page_id, $blocks);
        }

        return $page_id;
    }

    /**
     * Update page blocks
     */
    public function update_page_blocks($page_id, $blocks) {
        $page = $this->get_page($page_id);

        if (!$page) {
            return new WP_Error('not_found', 'Page not found');
        }

        $this->block_storage->save_blocks($page_id, $blocks);

        // Touch the post to update modified date
        wp_update_post(array(
            'ID' => $page_id,
            'post_modified' => current_time('mysql'),
            'post_modified_gmt' => current_time('mysql', 1)
        ));

        return true;
    }

    /**
     * Delete page
     */
    public function delete_page($page_id) {
        return wp_delete_post($page_id, true);
    }
}
