<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php bloginfo('description'); ?>">
    <meta name="author" content="OPUS Art Gallery">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_template_directory_uri(); ?>/assets/favicon.png">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<!-- React App Root -->
<div id="root"></div>

<!-- WordPress Data for React -->
<script>
window.wpData = {
    apiUrl: '<?php echo esc_url(rest_url('opus/v1')); ?>',
    lovableApiUrl: '<?php echo esc_url(rest_url('lovable/v1')); ?>',
    wpApiUrl: '<?php echo esc_url(rest_url('wp/v2')); ?>',
    siteUrl: '<?php echo esc_url(home_url()); ?>',
    nonce: '<?php echo wp_create_nonce('wp_rest'); ?>',
    siteName: '<?php bloginfo('name'); ?>',
    siteDescription: '<?php bloginfo('description'); ?>',
    language: '<?php echo get_bloginfo('language'); ?>',
    themeUrl: '<?php echo esc_url(get_template_directory_uri()); ?>',
    pageId: <?php echo get_the_ID() ?: 0; ?>,
};
</script>

<?php wp_footer(); ?>
</body>
</html>
