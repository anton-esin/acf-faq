<?php
// Variables: $faqs, $args, $wrapper_class, $item_class, $uid
$H = in_array(strtolower($args['heading']), ['h2','h3','h4','h5','h6'], true) ? strtolower($args['heading']) : 'h3';
?>
<div class="<?php echo esc_attr($wrapper_class); ?>" id="<?php echo esc_attr($uid); ?>">
  <?php foreach ($faqs as $row): ?>
    <div class="<?php echo esc_attr($item_class); ?>">
      <<?php echo $H; ?> class="faq-question"><?php echo esc_html($row['q']); ?></<?php echo $H; ?>>
      <div class="faq-answer"><?php echo wp_kses_post($row['a']); ?></div>
    </div>
  <?php endforeach; ?>
</div>
