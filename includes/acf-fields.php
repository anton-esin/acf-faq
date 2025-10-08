<?php
/**
 * Register the ACF-Fieldgroup "FAQ". 
 */
if ( ! defined('ABSPATH') ) { exit; }

if ( function_exists( 'acf_add_local_field_group' ) ) {

    acf_add_local_field_group( array(
        'key' => 'group_68d4ec083744a',
        'title' => 'FAQ',
        'fields' => array(
            array(
                'key' => 'field_68d4ec08cd610',
                'label' => 'Liste von Fragen und Antworten',
                'name' => 'faq_list',
                'aria-label' => '',
                'type' => 'repeater',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'layout' => 'table',
                'pagination' => 0,
                'min' => 0,
                'max' => 0,
                'collapsed' => '',
                'button_label' => 'Eintrag hinzufügen',
                'rows_per_page' => 20,
                'sub_fields' => array(
                    array(
                        'key' => 'field_68d4fb2c2dc6a',
                        'label' => 'Frage',
                        'name' => 'faq_question',
                        'aria-label' => '',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'maxlength' => '',
                        'allow_in_bindings' => 0,
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'parent_repeater' => 'field_68d4ec08cd610',
                    ),
                    array(
                        'key' => 'field_68d4fb462dc6b',
                        'label' => 'Antwort',
                        'name' => 'faq_answer',
                        'aria-label' => '',
                        'type' => 'wysiwyg',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'allow_in_bindings' => 0,
                        'tabs' => 'all',
                        'toolbar' => 'full',
                        'media_upload' => 1,
                        'delay' => 0,
                        'parent_repeater' => 'field_68d4ec08cd610',
                    ),
                ),
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'page',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
    ) );

}