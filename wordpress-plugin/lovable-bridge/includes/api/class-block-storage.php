<?php
/**
 * Block Storage
 *
 * Handles storage and retrieval of block data in WordPress post meta.
 * Stores blocks as JSON in post_meta for flexibility and AI compatibility.
 */

class Lovable_Bridge_Block_Storage {

    const META_KEY = 'lovable_blocks';

    /**
     * Save blocks for a page
     */
    public function save_blocks($post_id, $blocks) {
        // Validate blocks array
        if (!is_array($blocks)) {
            return new WP_Error('invalid_blocks', 'Blocks must be an array');
        }

        // Store as JSON
        $json = json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            return new WP_Error('json_error', 'Failed to encode blocks to JSON');
        }

        update_post_meta($post_id, self::META_KEY, $json);

        // Also store in cache for performance
        $cache_key = "lovable_blocks_{$post_id}";
        wp_cache_set($cache_key, $blocks, 'lovable_bridge', HOUR_IN_SECONDS);

        return true;
    }

    /**
     * Get blocks for a page
     */
    public function get_blocks($post_id) {
        // Check cache first
        $cache_key = "lovable_blocks_{$post_id}";
        $cached = wp_cache_get($cache_key, 'lovable_bridge');

        if ($cached !== false) {
            return $cached;
        }

        // Get from meta
        $json = get_post_meta($post_id, self::META_KEY, true);

        if (empty($json)) {
            return array();
        }

        $blocks = json_decode($json, true);

        if ($blocks === null && json_last_error() !== JSON_ERROR_NONE) {
            error_log('Lovable Bridge: JSON decode error for post ' . $post_id . ': ' . json_last_error_msg());
            return array();
        }

        // Cache the result
        wp_cache_set($cache_key, $blocks, 'lovable_bridge', HOUR_IN_SECONDS);

        return $blocks;
    }

    /**
     * Delete blocks for a page
     */
    public function delete_blocks($post_id) {
        delete_post_meta($post_id, self::META_KEY);

        // Clear cache
        $cache_key = "lovable_blocks_{$post_id}";
        wp_cache_delete($cache_key, 'lovable_bridge');

        return true;
    }

    /**
     * Clear cache for a page (useful after updates)
     */
    public function clear_cache($post_id) {
        $cache_key = "lovable_blocks_{$post_id}";
        wp_cache_delete($cache_key, 'lovable_bridge');
    }
}
