<?php
/**
 * Plugin Name: Dynamic Classes for Elementor
 * Description: Add dynamic CSS classes (gap, padding, margin) via Elementor Site Settings
 * Version: 3.0.0
 * Author: DEVSR
 * Text Domain: dynamic-classes-elementor
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load the tab class only after Elementor is fully loaded
add_action('elementor/init', function() {
    
    // Check if Tab_Base class exists before defining our tab
    if (!class_exists('\Elementor\Core\Kits\Documents\Tabs\Tab_Base')) {
        return;
    }
    
    // Define the Dynamic Classes Tab
    class Dynamic_Classes_Tab extends \Elementor\Core\Kits\Documents\Tabs\Tab_Base {
        
        public function get_id() {
            return 'dynamic-classes';
        }

        public function get_title() {
            return __('Dynamic Classes', 'dynamic-classes-elementor');
        }

        public function get_group() {
            return 'settings';
        }

        public function get_icon() {
            return 'eicon-code';
        }

        protected function register_tab_controls() {
            
            // Gap Classes Section
            $this->start_controls_section(
                'section_gap_classes',
                [
                    'label' => __('Gap Classes', 'dynamic-classes-elementor'),
                    'tab' => $this->get_id(),
                ]
            );

            $gap_repeater = new \Elementor\Repeater();

            $gap_repeater->add_control(
                'name',
                [
                    'label' => __('Class Name', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => 'gap-custom-1',
                    'description' => __('Enter class name without dot (e.g., gap-sm)', 'dynamic-classes-elementor'),
                ]
            );

            $gap_repeater->add_control(
                'row_gap',
                [
                    'label' => __('Row Gap', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => '20px',
                    'description' => __('e.g., 20px, 1.5rem, 2em', 'dynamic-classes-elementor'),
                ]
            );

            $gap_repeater->add_control(
                'column_gap',
                [
                    'label' => __('Column Gap', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => '20px',
                    'description' => __('e.g., 20px, 1.5rem, 2em', 'dynamic-classes-elementor'),
                ]
            );

            $this->add_control(
                'dce_gap_classes',
                [
                    'label' => __('Gap Classes', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::REPEATER,
                    'fields' => $gap_repeater->get_controls(),
                    'default' => [
                        [
                            'name' => 'gap-xs',
                            'row_gap' => '10px',
                            'column_gap' => '10px',
                        ],
                        [
                            'name' => 'gap-sm',
                            'row_gap' => '20px',
                            'column_gap' => '20px',
                        ],
                        [
                            'name' => 'gap-md',
                            'row_gap' => '30px',
                            'column_gap' => '30px',
                        ],
                    ],
                    'title_field' => '{{{ name }}} - Row: {{{ row_gap }}}, Col: {{{ column_gap }}}',
                ]
            );

            $this->end_controls_section();

            // Padding Classes Section
            $this->start_controls_section(
                'section_padding_classes',
                [
                    'label' => __('Padding Classes', 'dynamic-classes-elementor'),
                    'tab' => $this->get_id(),
                ]
            );

            $padding_repeater = new \Elementor\Repeater();

            $padding_repeater->add_control(
                'name',
                [
                    'label' => __('Class Name', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => 'padding-custom-1',
                ]
            );

            $padding_repeater->add_control(
                'top',
                [
                    'label' => __('Top', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => '10px',
                ]
            );

            $padding_repeater->add_control(
                'right',
                [
                    'label' => __('Right', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => '10px',
                ]
            );

            $padding_repeater->add_control(
                'bottom',
                [
                    'label' => __('Bottom', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => '10px',
                ]
            );

            $padding_repeater->add_control(
                'left',
                [
                    'label' => __('Left', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => '10px',
                ]
            );

            $this->add_control(
                'dce_padding_classes',
                [
                    'label' => __('Padding Classes', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::REPEATER,
                    'fields' => $padding_repeater->get_controls(),
                    'default' => [
                        [
                            'name' => 'padding-sm',
                            'top' => '10px',
                            'right' => '10px',
                            'bottom' => '10px',
                            'left' => '10px',
                        ],
                        [
                            'name' => 'padding-md',
                            'top' => '20px',
                            'right' => '20px',
                            'bottom' => '20px',
                            'left' => '20px',
                        ],
                    ],
                    'title_field' => '{{{ name }}} - {{{ top }}} {{{ right }}} {{{ bottom }}} {{{ left }}}',
                ]
            );

            $this->end_controls_section();

            // Margin Classes Section
            $this->start_controls_section(
                'section_margin_classes',
                [
                    'label' => __('Margin Classes', 'dynamic-classes-elementor'),
                    'tab' => $this->get_id(),
                ]
            );

            $margin_repeater = new \Elementor\Repeater();

            $margin_repeater->add_control(
                'name',
                [
                    'label' => __('Class Name', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => 'margin-custom-1',
                ]
            );

            $margin_repeater->add_control(
                'top',
                [
                    'label' => __('Top', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => '10px',
                ]
            );

            $margin_repeater->add_control(
                'right',
                [
                    'label' => __('Right', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => '10px',
                ]
            );

            $margin_repeater->add_control(
                'bottom',
                [
                    'label' => __('Bottom', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => '10px',
                ]
            );

            $margin_repeater->add_control(
                'left',
                [
                    'label' => __('Left', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => '10px',
                ]
            );

            $this->add_control(
                'dce_margin_classes',
                [
                    'label' => __('Margin Classes', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::REPEATER,
                    'fields' => $margin_repeater->get_controls(),
                    'default' => [
                        [
                            'name' => 'margin-sm',
                            'top' => '10px',
                            'right' => '0px',
                            'bottom' => '10px',
                            'left' => '0px',
                        ],
                        [
                            'name' => 'margin-md',
                            'top' => '20px',
                            'right' => '0px',
                            'bottom' => '20px',
                            'left' => '0px',
                        ],
                    ],
                    'title_field' => '{{{ name }}} - {{{ top }}} {{{ right }}} {{{ bottom }}} {{{ left }}}',
                ]
            );

            $this->end_controls_section();
        }
    }
    
}, 1);

class Dynamic_Classes_Elementor_Kit {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
    }
    
    public function init() {
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return;
        }
        
        // Register Site Settings Tab
        add_action('elementor/kit/register_tabs', [$this, 'register_site_settings_tab'], 100);
        
        // Add controls to elements
        add_action('elementor/element/container/section_layout/after_section_end', [$this, 'add_dynamic_class_control'], 10, 2);
        add_action('elementor/element/section/section_advanced/after_section_end', [$this, 'add_dynamic_class_control'], 10, 2);
        add_action('elementor/element/column/section_advanced/after_section_end', [$this, 'add_dynamic_class_control'], 10, 2);
        
        // Generate CSS
        add_action('wp_enqueue_scripts', [$this, 'enqueue_dynamic_styles']);
        add_action('elementor/preview/enqueue_styles', [$this, 'enqueue_dynamic_styles'], 999);
    }
    
    public function admin_notice_missing_elementor() {
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'dynamic-classes-elementor'),
            '<strong>' . esc_html__('Dynamic Classes for Elementor', 'dynamic-classes-elementor') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'dynamic-classes-elementor') . '</strong>'
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }
    
    public function register_site_settings_tab($kit) {
        // Check if our tab class exists
        if (!class_exists('Dynamic_Classes_Tab')) {
            return;
        }
        
        $kit->register_tab('dynamic-classes', Dynamic_Classes_Tab::class);
    }
    
    private function get_classes_from_kit($type = 'gap') {
        if (!class_exists('\Elementor\Plugin')) {
            return [];
        }
        
        $kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
        if (!$kit) {
            return [];
        }
        
        $classes = $kit->get_settings('dce_' . $type . '_classes');
        return is_array($classes) ? $classes : [];
    }
    
    public function add_dynamic_class_control($element, $args) {
        $gap_classes = $this->get_classes_from_kit('gap');
        $padding_classes = $this->get_classes_from_kit('padding');
        $margin_classes = $this->get_classes_from_kit('margin');
        
        // Build options arrays
        $gap_options = ['' => __('None', 'dynamic-classes-elementor')];
        foreach ($gap_classes as $class) {
            if (!empty($class['name'])) {
                $gap_options[$class['name']] = $class['name'];
            }
        }
        
        $padding_options = ['' => __('None', 'dynamic-classes-elementor')];
        foreach ($padding_classes as $class) {
            if (!empty($class['name'])) {
                $padding_options[$class['name']] = $class['name'];
            }
        }
        
        $margin_options = ['' => __('None', 'dynamic-classes-elementor')];
        foreach ($margin_classes as $class) {
            if (!empty($class['name'])) {
                $margin_options[$class['name']] = $class['name'];
            }
        }
        
        $element->start_controls_section(
            'dce_dynamic_classes_section',
            [
                'label' => __('Dynamic Classes', 'dynamic-classes-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
            ]
        );
        
        // Gap control
        if (count($gap_options) > 1) {
            $element->add_control(
                'dce_gap_class',
                [
                    'label' => __('Gap Class', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'options' => $gap_options,
                    'default' => '',
                    'prefix_class' => '',
                ]
            );
        }
        
        // Padding control
        if (count($padding_options) > 1) {
            $element->add_control(
                'dce_padding_class',
                [
                    'label' => __('Padding Class', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'options' => $padding_options,
                    'default' => '',
                    'prefix_class' => '',
                ]
            );
        }
        
        // Margin control
        if (count($margin_options) > 1) {
            $element->add_control(
                'dce_margin_class',
                [
                    'label' => __('Margin Class', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'options' => $margin_options,
                    'default' => '',
                    'prefix_class' => '',
                ]
            );
        }
        
        $element->end_controls_section();
    }
    
    public function enqueue_dynamic_styles() {
        wp_register_style('dce-frontend', false);
        wp_enqueue_style('dce-frontend');

        $dynamic_css = $this->get_dynamic_css();
        if (!empty($dynamic_css)) {
            wp_add_inline_style('dce-frontend', $dynamic_css);
        }
    }
    
    private function get_dynamic_css() {
        $gap_classes = $this->get_classes_from_kit('gap');
        $padding_classes = $this->get_classes_from_kit('padding');
        $margin_classes = $this->get_classes_from_kit('margin');
        
        $css = '';
        
        // Generate gap classes
        foreach ($gap_classes as $class) {
            if (!empty($class['name']) && isset($class['row_gap']) && isset($class['column_gap'])) {
                $css .= ".{$class['name']}.e-con, .{$class['name']} > .elementor-widget-wrap, .{$class['name']} > .e-con-inner {
                    gap: {$class['row_gap']} {$class['column_gap']} !important;
                }\n";
            }
        }
        
        // Generate padding classes
        foreach ($padding_classes as $class) {
            if (!empty($class['name'])) {
                $css .= ".{$class['name']}.e-con, .{$class['name']} > .elementor-widget-wrap, .{$class['name']} > .e-con-inner {
                    padding-top: {$class['top']} !important;
                    padding-right: {$class['right']} !important;
                    padding-bottom: {$class['bottom']} !important;
                    padding-left: {$class['left']} !important;
                }\n";
            }
        }
        
        // Generate margin classes
        foreach ($margin_classes as $class) {
            if (!empty($class['name'])) {
                $css .= ".{$class['name']}.e-con, .{$class['name']}.elementor-section, .{$class['name']}.elementor-column {
                    margin-top: {$class['top']} !important;
                    margin-right: {$class['right']} !important;
                    margin-bottom: {$class['bottom']} !important;
                    margin-left: {$class['left']} !important;
                }\n";
            }
        }
        
        return $css;
    }
}

Dynamic_Classes_Elementor_Kit::get_instance();