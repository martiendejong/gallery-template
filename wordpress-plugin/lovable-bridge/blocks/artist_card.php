<?php
/**
 * Artist Card Block Template
 */
$data = $block['data'];
?>

<div class="artist-card" data-block-type="artist_card" data-featured="<?php echo esc_attr($data['featured'] ?? 'false'); ?>">
    <img src="<?php echo esc_url($data['image']); ?>" alt="<?php echo esc_attr($data['name']); ?>" class="artist-image">
    <div class="artist-info">
        <h3 class="artist-name"><?php echo esc_html($data['name']); ?></h3>
        <?php if (!empty($data['bio'])): ?>
            <p class="artist-bio"><?php echo esc_html($data['bio']); ?></p>
        <?php endif; ?>
    </div>
</div>
