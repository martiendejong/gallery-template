<?php
/**
 * CTA Block Template
 */
$data = $block['data'];
?>

<div class="cta-block" data-block-type="cta">
    <h2><?php echo esc_html($data['title']); ?></h2>
    <?php if (!empty($data['description'])): ?>
        <p><?php echo esc_html($data['description']); ?></p>
    <?php endif; ?>
    <a href="<?php echo esc_url($data['buttonLink'] ?? '#'); ?>" class="cta-button">
        <?php echo esc_html($data['buttonText']); ?>
    </a>
</div>
