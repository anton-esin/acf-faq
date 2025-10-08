<?php
/**
 * Plugin Name: ACF (pro) FAQ (JSON-LD)
 * Plugin URI: https://github.com/anton-esin/acf-faq
 * Description: FAQ shortcode based on ACF Pro (Repeater) with FAQPage JSON-LD. Shortcode: [acf_faq]
 * Version:     1.0.0
 * Author:      Anton Esin <a.esin@creationell.de>
 * Author URI:  https://www.creationell.de/
 * Contributors: anton-esin
 * Tags:        faq, structured data
 * Requires Plugins: advanced-custom-fields-pro
 * Requires at least: 6.7
 * Requires PHP: 8.3
 * Stable tag: 1.0.0
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: anton-esin/acf-faq
 * Primary Branch: main
 */

if ( ! defined('ABSPATH') ) { exit; }

/**
 * Template locator to allow theme overrides.
 * Looks for templates in:
 *  1) Child theme: /acf-faq/{file}
 *  2) Parent theme: /acf-faq/{file}
 *  3) Plugin fallback: /templates/{file}
 */
if ( ! function_exists('acf_faq_locate_template') ) {
    function acf_faq_locate_template($filename) {
        $candidates = [
            trailingslashit( get_stylesheet_directory() ) . 'acf-faq/' . $filename, // Child theme
            trailingslashit( get_template_directory() )   . 'acf-faq/' . $filename, // Parent theme
            plugin_dir_path(__FILE__) . 'templates/' . $filename,                   // Plugin fallback
        ];
        foreach ($candidates as $file) {
            if ( file_exists($file) ) {
                return $file;
            }
        }
        return '';
    }
}

/**
 * Render helper that extracts variables and includes the resolved template.
 */
if ( ! function_exists('acf_faq_render_template') ) {
    function acf_faq_render_template($filename, array $vars = []) {
        $template = acf_faq_locate_template($filename);
        if ( ! $template ) return '';
        ob_start();
        extract($vars, EXTR_SKIP);
        include $template;
        return ob_get_clean();
    }
}

/**
 * Load ACF field group at a reliable timing.
 * This file registers the group directly via acf_add_local_field_group(...).
 * The file path can be overridden via the 'acf_faq/fields_file' filter.
 * You can also disable loading the default fields via 'acf_faq/register_default_fields'.
 */
add_action('acf/include_fields', function () {
    // Disable default fields?
    if ( false === apply_filters('acf_faq/register_default_fields', true) ) {
        return;
    }

    // Allow overriding the fields file path (e.g., from a theme)
    $file = apply_filters('acf_faq/fields_file', plugin_dir_path(__FILE__) . 'includes/acf-fields.php');

    if ( $file && file_exists($file) ) {
        require_once $file; // The included file must call acf_add_local_field_group(...) directly.
    }
}, 5);

/**
 * Shortcode: [acf_faq]
 * Optional attributes:
 *   field="faq_list"       // ACF repeater field name
 *   post_id="123"          // Data source; defaults to current post ID
 *   heading="h3"           // h2..h6 (for static) and aria-level for the accordion label
 *   accordion="1"          // 1 = <details>/<summary>, 0 = static markup
 *   class="faq-wrapper"    // Wrapper CSS class
 *   item_class="faq-item"  // Item CSS class
 *   aria_heading="0"       // 1 = add role="heading" + aria-level to <summary>
 */
add_shortcode('acf_faq', function($atts){
    $a = shortcode_atts([
        'field'        => 'faq_list',
        'post_id'      => get_the_ID(),
        'heading'      => 'h3',
        'accordion'    => '1',
        'class'        => 'faq-wrapper',
        'item_class'   => 'faq-item',
        'aria_heading' => '0',
    ], $atts, 'acf_faq');

    // Ensure ACF is available
    if ( ! function_exists('have_rows') ) return '';

    // Determine source post
    $post_id = $a['post_id'] ?: get_the_ID();

    // Ensure rows exist
    if ( ! have_rows($a['field'], $post_id) ) return '';

    // Validate heading tag
    $allowed_headings = ['h2','h3','h4','h5','h6'];
    if ( ! in_array(strtolower($a['heading']), $allowed_headings, true) ) {
        $a['heading'] = 'h3';
    }

    // Collect FAQs
    $faqs = [];
    while ( have_rows($a['field'], $post_id) ) {
        the_row();
        $q   = get_sub_field('faq_question');
        $ans = get_sub_field('faq_answer');
        if ( $q && $ans ) {
            $faqs[] = ['q' => $q, 'a' => $ans];
        }
    }
    if ( ! $faqs ) return '';

    // Build JSON-LD schema
    $permalink = $post_id ? get_permalink($post_id) : home_url('/');
    $title     = $post_id ? get_the_title($post_id) : get_bloginfo('name');

    $schema = [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        '@id'        => $permalink . '#faqpage',
        'url'        => $permalink,
        'name'       => $title . ' – FAQ',
        'mainEntity' => array_map(function($item) use ($permalink){
            $qid = $permalink . '#q-' . sanitize_title($item['q']);
            return [
                '@type' => 'Question',
                '@id'   => $qid,
                'name'  => wp_strip_all_tags($item['q']),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    '@id'   => $qid . '-a',
                    'text'  => wp_strip_all_tags($item['a']),
                ],
            ];
        }, $faqs),
    ];
    // Allow external schema customization
    $schema = apply_filters('acf_faq/schema', $schema, $faqs, $a);

    // Render HTML via templates
    $vars = [
        'faqs'          => $faqs,
        'args'          => $a,
        'wrapper_class' => $a['class'],
        'item_class'    => $a['item_class'],
        'uid'           => 'faq-' . wp_generate_uuid4(),
    ];
    $template_file = ($a['accordion'] === '1') ? 'faq-accordion.php' : 'faq-static.php';
    $html = acf_faq_render_template($template_file, $vars);

    // Allow full HTML override
    $html = apply_filters('acf_faq/html', $html, $faqs, $a);

    // Minimal inline styles for the accordion (print once; can be disabled via filter)
    static $faq_css_printed = false;
    $print_css = apply_filters('acf_faq/print_inline_css', true);
    if ( $a['accordion'] === '1' && ! $faq_css_printed && $print_css ) {
        $faq_css_printed = true;
        $html .= '<style>
            .faq-wrapper details { border-bottom: 1px solid #e5e7eb; padding: .75rem 0; }
            .faq-wrapper summary { cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: .75rem; font-weight: 600; list-style: none; }
            .faq-wrapper summary::-webkit-details-marker { display: none; }
            .faq-wrapper .faq-question-text { flex: 1 1 auto; }
            .faq-wrapper .faq-icon { width: 1.25rem; height: 1.25rem; flex: 0 0 auto; transform: rotate(0deg); transition: transform .2s ease; transform-origin: center; }
            .faq-wrapper details[open] .faq-icon { transform: rotate(180deg); }
            .faq-wrapper .faq-answer { margin-top: .5rem; }
            .faq-wrapper summary:focus-visible { outline: 2px solid; outline-offset: 2px; }
            .faq-wrapper .faq-item { margin-bottom: 2rem; }
            @media (prefers-reduced-motion: reduce) { .faq-wrapper .faq-icon { transition: none; } }
        </style>';
    }

    // Append JSON-LD
    $html .= '<script type="application/ld+json">' .
             wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) .
             '</script>';

    return $html;
});