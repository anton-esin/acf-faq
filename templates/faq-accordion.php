<?php
// Variables: $faqs, $args, $wrapper_class, $item_class, $uid
$level = ($args['aria_heading'] === '1') ? (int) filter_var($args['heading'], FILTER_SANITIZE_NUMBER_INT) : 0;
if ($level < 2 || $level > 6) $level = 0;
?>
<div class="<?php echo esc_attr($wrapper_class); ?>" id="<?php echo esc_attr($uid); ?>">
  <?php foreach ($faqs as $row): ?>
    <details class="<?php echo esc_attr($item_class); ?>">
      <summary class="faq-question"<?php echo $level ? ' role="heading" aria-level="'.(int) $level.'"' : ''; ?>>
        <span class="faq-question-text"><?php echo esc_html($row['q']); ?></span>
        <svg class="faq-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
          <path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </summary>
      <div class="faq-answer"><?php echo wp_kses_post($row['a']); ?></div>
    </details>
  <?php endforeach; ?>
</div>
