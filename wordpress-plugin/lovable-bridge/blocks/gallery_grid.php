<?php
/**
 * Gallery Grid Block Template
 */
$data = $block['data'];
$items = $data['items'] ?? [];
$columns = $data['columns'] ?? 3;
$spacing = $data['spacing'] ?? 'md';
?>

<div class="gallery-grid" data-block-type="gallery_grid" data-columns="<?php echo esc_attr($columns); ?>" data-spacing="<?php echo esc_attr($spacing); ?>">
    <?php foreach ($items as $item): ?>
        <div class="gallery-item">
            <img src="<?php echo esc_url($item['image'] ?? ''); ?>" alt="<?php echo esc_attr($item['title'] ?? ''); ?>" loading="lazy">
            <div class="gallery-item-info">
                <h3><?php echo esc_html($item['title'] ?? ''); ?></h3>
                <?php if (!empty($item['artist'])): ?>
                    <p class="artist-name"><?php echo esc_html($item['artist']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
