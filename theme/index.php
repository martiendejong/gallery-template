<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php bloginfo('description'); ?>">
    <title><?php wp_title('|', true, 'right'); bloginfo('name'); ?></title>

    <!-- Preconnect to WordPress API -->
    <link rel="preconnect" href="<?php echo esc_url(home_url()); ?>">

    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<!-- React App Root -->
<div id="root"></div>

<!-- WordPress Data for React -->
<script>
window.wpData = {
    apiUrl: '<?php echo esc_url(rest_url('opus/v1')); ?>',
    wpApiUrl: '<?php echo esc_url(rest_url('wp/v2')); ?>',
    siteUrl: '<?php echo esc_url(home_url()); ?>',
    nonce: '<?php echo wp_create_nonce('wp_rest'); ?>',
    siteName: '<?php bloginfo('name'); ?>',
    siteDescription: '<?php bloginfo('description'); ?>',
    language: '<?php echo get_bloginfo('language'); ?>',
};
</script>

<?php wp_footer(); ?>
</body>
</html>
