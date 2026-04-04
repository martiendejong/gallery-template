<?php
/**
 * Hero Block Template
 * Renders server-side HTML for SEO
 */
$data = $block['data'];
$bg_image = $data['backgroundImage'] ?? '';
$title = $data['title'] ?? '';
$subtitle = $data['subtitle'] ?? '';
$location = $data['location'] ?? '';
$button_text = $data['buttonText'] ?? '';
?>

<section class="hero-block" data-block-type="hero" data-block-id="<?php echo esc_attr($block['id'] ?? ''); ?>" style="background-image: url('<?php echo esc_url($bg_image); ?>');">
    <div class="hero-content">
        <?php if ($location): ?>
            <p class="hero-location"><?php echo esc_html($location); ?></p>
        <?php endif; ?>

        <h1 class="hero-title"><?php echo esc_html($title); ?></h1>

        <?php if ($subtitle): ?>
            <p class="hero-subtitle"><?php echo esc_html($subtitle); ?></p>
        <?php endif; ?>

        <?php if ($button_text): ?>
            <button class="hero-button"><?php echo esc_html($button_text); ?></button>
        <?php endif; ?>
    </div>
</section>
