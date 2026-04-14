<?php
/**
 * Opus Gallery Theme - Main Template
 * Hybrid rendering: WordPress HTML + React hydration
 */
get_header();

if (have_posts()) :
    while (have_posts()) : the_post();
        $page_id = get_the_ID();
        $blocks = get_post_meta($page_id, 'lovable_blocks', true);
        $layout = get_post_meta($page_id, 'lovable_layout', true);

        // Render WordPress HTML (SEO layer)
        if ($blocks && $layout && class_exists('Lovable_Bridge')) {
            echo Lovable_Bridge::get_instance()->render_blocks($page_id);
        } else {
            // Fallback to standard WordPress content
            the_content();
        }
    endwhile;
endif;

// React mount point for SPA hydration
?>
<div id="root"></div>
<?php get_footer(); ?>
