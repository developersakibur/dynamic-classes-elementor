<?php
/**
 * Plugin Name: Dynamic Classes for Elementor
 * Description: Add dynamic CSS classes (gap, padding, margin) with clamp() support via Elementor Site Settings
 * Version: 3.2.0
 * Author: DEVSR
 * Text Domain: dynamic-classes-elementor
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin version constant
define('DCE_VERSION', '3.2.0');
define('DCE_PLUGIN_FILE', __FILE__);
define('DCE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DCE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load the tab class only after Elementor is fully loaded
add_action('elementor/init', function() {
    
    // Check if Tab_Base class exists before defining our tab
    if (!class_exists('\Elementor\Core\Kits\Documents\Tabs\Tab_Base')) {
        return;
    }
    
    /**
     * Dynamic Classes Tab for Elementor Site Settings
     */
    class Dynamic_Classes_Tab extends \Elementor\Core\Kits\Documents\Tabs\Tab_Base {
        
        public function get_id() {
            return 'dynamic-classes';
        }

        public function get_title() {
            return esc_html__('Dynamic Classes', 'dynamic-classes-elementor');
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
                    'label' => esc_html__('Gap Classes', 'dynamic-classes-elementor'),
                    'tab' => $this->get_id(),
                ]
            );

            $gap_repeater = new \Elementor\Repeater();

            $gap_repeater->add_control(
                'name',
                [
                    'label' => esc_html__('Class Name', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => 'gap-custom-1',
                ]
            );

            $gap_repeater->add_control(
                'row_gap',
                [
                    'label' => esc_html__('Row Gap', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => 'clamp(1rem, 3vw, 3rem)',
                ]
            );

            $gap_repeater->add_control(
                'column_gap',
                [
                    'label' => esc_html__('Column Gap', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => 'clamp(1rem, 3vw, 3rem)',
                ]
            );

            $this->add_control(
                'dce_gap_classes',
                [
                    'label' => esc_html__('Gap Classes', 'dynamic-classes-elementor'),
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
                            'row_gap' => 'clamp(1.25rem, 2vw, 2rem)',
                            'column_gap' => 'clamp(1.25rem, 2vw, 2rem)',
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
                    'label' => esc_html__('Padding Classes', 'dynamic-classes-elementor'),
                    'tab' => $this->get_id(),
                ]
            );

            $padding_repeater = new \Elementor\Repeater();

            $padding_repeater->add_control(
                'name',
                [
                    'label' => esc_html__('Class Name', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => 'padding-custom-1',
                ]
            );

            $padding_repeater->add_control(
                'top',
                [
                    'label' => esc_html__('Top', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => 'clamp(1rem, 3vw, 3rem)',
                ]
            );

            $padding_repeater->add_control(
                'right',
                [
                    'label' => esc_html__('Right', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => 'clamp(1rem, 3vw, 3rem)',
                ]
            );

            $padding_repeater->add_control(
                'bottom',
                [
                    'label' => esc_html__('Bottom', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => 'clamp(1rem, 3vw, 3rem)',
                ]
            );

            $padding_repeater->add_control(
                'left',
                [
                    'label' => esc_html__('Left', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => 'clamp(1rem, 3vw, 3rem)',
                ]
            );

            $this->add_control(
                'dce_padding_classes',
                [
                    'label' => esc_html__('Padding Classes', 'dynamic-classes-elementor'),
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
                            'top' => 'clamp(1rem, 3vw, 3rem)',
                            'right' => 'clamp(1rem, 3vw, 3rem)',
                            'bottom' => 'clamp(1rem, 3vw, 3rem)',
                            'left' => 'clamp(1rem, 3vw, 3rem)',
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
                    'label' => esc_html__('Margin Classes', 'dynamic-classes-elementor'),
                    'tab' => $this->get_id(),
                ]
            );

            $margin_repeater = new \Elementor\Repeater();

            $margin_repeater->add_control(
                'name',
                [
                    'label' => esc_html__('Class Name', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => 'margin-custom-1',
                ]
            );

            $margin_repeater->add_control(
                'top',
                [
                    'label' => esc_html__('Top', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => 'clamp(1rem, 3vw, 3rem)',
                ]
            );

            $margin_repeater->add_control(
                'right',
                [
                    'label' => esc_html__('Right', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => '0',
                ]
            );

            $margin_repeater->add_control(
                'bottom',
                [
                    'label' => esc_html__('Bottom', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => 'clamp(1rem, 3vw, 3rem)',
                ]
            );

            $margin_repeater->add_control(
                'left',
                [
                    'label' => esc_html__('Left', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => '0',
                ]
            );

            $this->add_control(
                'dce_margin_classes',
                [
                    'label' => esc_html__('Margin Classes', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::REPEATER,
                    'fields' => $margin_repeater->get_controls(),
                    'default' => [
                        [
                            'name' => 'margin-sm',
                            'top' => '10px',
                            'right' => '0',
                            'bottom' => '10px',
                            'left' => '0',
                        ],
                        [
                            'name' => 'margin-md',
                            'top' => 'clamp(1rem, 3vw, 3rem)',
                            'right' => '0',
                            'bottom' => 'clamp(1rem, 3vw, 3rem)',
                            'left' => '0',
                        ],
                    ],
                    'title_field' => '{{{ name }}} - {{{ top }}} {{{ right }}} {{{ bottom }}} {{{ left }}}',
                ]
            );

            $this->end_controls_section();
        }
    }
});

/**
 * Main Plugin Class
 */
class Dynamic_Classes_Elementor_Kit {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Check if Elementor is installed and activated
        add_action('plugins_loaded', [$this, 'init']);
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Check for Elementor
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return;
        }
        
        // Check Elementor version
        if (!version_compare(ELEMENTOR_VERSION, '3.5.0', '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return;
        }
        
        // Load text domain for translations
        add_action('init', [$this, 'load_textdomain']);
        
        // Register Site Settings Tab
        add_action('elementor/kit/register_tabs', [$this, 'register_site_settings_tab'], 100);
        
        // Add controls to elements
        add_action('elementor/element/container/section_layout/after_section_end', [$this, 'add_dynamic_class_control'], 10, 2);
        add_action('elementor/element/section/section_advanced/after_section_end', [$this, 'add_dynamic_class_control'], 10, 2);
        add_action('elementor/element/column/section_advanced/after_section_end', [$this, 'add_dynamic_class_control'], 10, 2);
        
        // Enqueue styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_dynamic_styles']);
        add_action('elementor/editor/after_enqueue_styles', [$this, 'enqueue_dynamic_styles']);
        
        // Add settings link to plugins page
        add_filter('plugin_action_links_' . plugin_basename(DCE_PLUGIN_FILE), [$this, 'add_settings_link']);
    }
    
    /**
     * Load text domain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain('dynamic-classes-elementor', false, dirname(plugin_basename(DCE_PLUGIN_FILE)) . '/languages');
    }
    
    /**
     * Admin notice if Elementor is missing
     */
    public function admin_notice_missing_elementor() {
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'dynamic-classes-elementor'),
            '<strong>' . esc_html__('Dynamic Classes for Elementor', 'dynamic-classes-elementor') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'dynamic-classes-elementor') . '</strong>'
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message);
    }
    
    /**
     * Admin notice for minimum Elementor version
     */
    public function admin_notice_minimum_elementor_version() {
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'dynamic-classes-elementor'),
            '<strong>' . esc_html__('Dynamic Classes for Elementor', 'dynamic-classes-elementor') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'dynamic-classes-elementor') . '</strong>',
            '3.5.0'
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message);
    }
    
    /**
     * Register the Dynamic Classes tab in Site Settings
     */
    public function register_site_settings_tab($kit) {
        // Capability check - only administrators can access
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Check if our tab class exists
        if (!class_exists('Dynamic_Classes_Tab')) {
            return;
        }
        
        $kit->register_tab('dynamic-classes', Dynamic_Classes_Tab::class);
    }
    
    /**
     * Get classes from kit settings
     * 
     * @param string $type Type of classes (gap, padding, margin)
     * @return array
     */
    private function get_classes_from_kit($type) {
        try {
            if (!class_exists('\Elementor\Plugin')) {
                return [];
            }
            
            $kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
            if (!$kit) {
                return [];
            }
            
            $classes = $kit->get_settings('dce_' . $type . '_classes');
            return is_array($classes) ? $classes : [];
            
        } catch (Exception $e) {
            error_log('DCE Error in get_classes_from_kit: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validate and sanitize CSS value with clamp() support
     * 
     * @param string $value CSS value to validate
     * @return string|false Validated value or false if invalid
     */
    private function validate_css_value($value) {
        if (empty($value)) {
            return false;
        }
        
        $value = trim($value);
        
        // Allow 0 without unit
        if ($value === '0') {
            return '0';
        }
        
        // Allow CSS functions: calc(), clamp(), min(), max(), var()
        if (preg_match('/^(calc|clamp|min|max|var)\s*\(/i', $value)) {
            // Basic validation: must have balanced parentheses
            if (substr_count($value, '(') === substr_count($value, ')')) {
                // Additional safety: remove any potentially dangerous characters
                // Allow: numbers, units, operators, spaces, commas, parentheses
                if (preg_match('/^[0-9a-z\s\(\),.\-+*\/vwrempxhcin%]+$/i', $value)) {
                    return esc_attr($value);
                }
            }
            return false;
        }
        
        // Allow standard CSS units (including negative values)
        if (preg_match('/^-?\d+(\.\d+)?(px|em|rem|%|vh|vw|vmin|vmax|ch|ex)?$/i', $value)) {
            return esc_attr($value);
        }
        
        return false;
    }
    
    /**
     * Add dynamic class controls to elements
     */
    public function add_dynamic_class_control($element, $args) {
        $gap_classes = $this->get_classes_from_kit('gap');
        $padding_classes = $this->get_classes_from_kit('padding');
        $margin_classes = $this->get_classes_from_kit('margin');
        
        // Build options arrays
        $gap_options = ['' => esc_html__('None', 'dynamic-classes-elementor')];
        foreach ($gap_classes as $class) {
            if (!empty($class['name'])) {
                $sanitized_name = sanitize_html_class($class['name']);
                if (!empty($sanitized_name)) {
                    $gap_options[$sanitized_name] = esc_html($class['name']);
                }
            }
        }
        
        $padding_options = ['' => esc_html__('None', 'dynamic-classes-elementor')];
        foreach ($padding_classes as $class) {
            if (!empty($class['name'])) {
                $sanitized_name = sanitize_html_class($class['name']);
                if (!empty($sanitized_name)) {
                    $padding_options[$sanitized_name] = esc_html($class['name']);
                }
            }
        }
        
        $margin_options = ['' => esc_html__('None', 'dynamic-classes-elementor')];
        foreach ($margin_classes as $class) {
            if (!empty($class['name'])) {
                $sanitized_name = sanitize_html_class($class['name']);
                if (!empty($sanitized_name)) {
                    $margin_options[$sanitized_name] = esc_html($class['name']);
                }
            }
        }
        
        // Only add section if there are classes defined
        if (count($gap_options) <= 1 && count($padding_options) <= 1 && count($margin_options) <= 1) {
            return;
        }
        
        $element->start_controls_section(
            'section_dce_classes',
            [
                'label' => esc_html__('Dynamic Classes', 'dynamic-classes-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
            ]
        );
        
        // Gap control
        if (count($gap_options) > 1) {
            $element->add_control(
                'dce_gap_class',
                [
                    'label' => esc_html__('Gap Class', 'dynamic-classes-elementor'),
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
                    'label' => esc_html__('Padding Class', 'dynamic-classes-elementor'),
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
                    'label' => esc_html__('Margin Class', 'dynamic-classes-elementor'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'options' => $margin_options,
                    'default' => '',
                    'prefix_class' => '',
                ]
            );
        }
        
        $element->end_controls_section();
    }
    
    /**
     * Enqueue dynamic styles (no caching)
     */
    public function enqueue_dynamic_styles() {
        wp_register_style('dce-frontend', false, [], DCE_VERSION);
        wp_enqueue_style('dce-frontend');

        $dynamic_css = $this->generate_dynamic_css();
        if (!empty($dynamic_css)) {
            wp_add_inline_style('dce-frontend', $dynamic_css);
        }
    }
    
    /**
     * Generate dynamic CSS (without caching)
     * 
     * @return string Generated CSS
     */
    private function generate_dynamic_css() {
        $gap_classes = $this->get_classes_from_kit('gap');
        $padding_classes = $this->get_classes_from_kit('padding');
        $margin_classes = $this->get_classes_from_kit('margin');
        
        $css = "/* Dynamic Classes for Elementor v" . DCE_VERSION . " */\n";
        
        // Generate gap classes - FIXED SELECTORS
        foreach ($gap_classes as $class) {
            if (empty($class['name'])) {
                continue;
            }
            
            $class_name = sanitize_html_class($class['name']);
            if (empty($class_name)) {
                continue;
            }
            
            $row_gap = $this->validate_css_value($class['row_gap'] ?? '');
            $column_gap = $this->validate_css_value($class['column_gap'] ?? '');
            
            if ($row_gap === false || $column_gap === false) {
                continue;
            }
            
            // Gap - using CSS custom properties for Elementor compatibility
            if ($row_gap === $column_gap) {
                // Same gap for both directions
                // Boxed containers
                $css .= ".e-con-boxed.{$class_name} > .e-con-inner {\n";
                $css .= "    gap: {$row_gap} !important;\n";
                $css .= "}\n\n";
                
                // Full-width containers (including child containers)
                $css .= ".e-con-full.{$class_name},\n";
                $css .= ".e-con.{$class_name}.e-child {\n";
                $css .= "    --gap: {$row_gap} !important;\n";
                $css .= "    --row-gap: {$row_gap} !important;\n";
                $css .= "    --column-gap: {$row_gap} !important;\n";
                $css .= "}\n\n";
                
                // Legacy sections and columns
                $css .= ".elementor-section.{$class_name} > .elementor-container > .elementor-row,\n";
                $css .= ".elementor-column.{$class_name} > .elementor-widget-wrap {\n";
                $css .= "    gap: {$row_gap} !important;\n";
                $css .= "}\n\n";
            } else {
                // Different row and column gaps
                // Boxed containers
                $css .= ".e-con-boxed.{$class_name} > .e-con-inner {\n";
                $css .= "    row-gap: {$row_gap} !important;\n";
                $css .= "    column-gap: {$column_gap} !important;\n";
                $css .= "}\n\n";
                
                // Full-width containers (including child containers)
                $css .= ".e-con-full.{$class_name},\n";
                $css .= ".e-con.{$class_name}.e-child {\n";
                $css .= "    --row-gap: {$row_gap} !important;\n";
                $css .= "    --column-gap: {$column_gap} !important;\n";
                $css .= "}\n\n";
                
                // Legacy sections and columns
                $css .= ".elementor-section.{$class_name} > .elementor-container > .elementor-row,\n";
                $css .= ".elementor-column.{$class_name} > .elementor-widget-wrap {\n";
                $css .= "    row-gap: {$row_gap} !important;\n";
                $css .= "    column-gap: {$column_gap} !important;\n";
                $css .= "}\n\n";
            }
        }
        
        // Generate padding classes - FIXED SELECTORS
        foreach ($padding_classes as $class) {
            if (empty($class['name'])) {
                continue;
            }
            
            $class_name = sanitize_html_class($class['name']);
            if (empty($class_name)) {
                continue;
            }
            
            $top = $this->validate_css_value($class['top'] ?? '0');
            $right = $this->validate_css_value($class['right'] ?? '0');
            $bottom = $this->validate_css_value($class['bottom'] ?? '0');
            $left = $this->validate_css_value($class['left'] ?? '0');
            
            if ($top === false || $right === false || $bottom === false || $left === false) {
                continue;
            }
            
            // Padding - using CSS custom properties for containers
            // For boxed containers (.e-con-boxed), padding applies to .e-con-inner
            $css .= ".e-con-boxed.{$class_name} > .e-con-inner {\n";
            $css .= "    padding-top: {$top} !important;\n";
            $css .= "    padding-right: {$right} !important;\n";
            $css .= "    padding-bottom: {$bottom} !important;\n";
            $css .= "    padding-left: {$left} !important;\n";
            $css .= "}\n\n";
            
            // For full-width containers (.e-con-full) and child containers, use CSS variables
            $css .= ".e-con-full.{$class_name},\n";
            $css .= ".e-con.{$class_name}.e-child {\n";
            $css .= "    --padding-top: {$top} !important;\n";
            $css .= "    --padding-right: {$right} !important;\n";
            $css .= "    --padding-bottom: {$bottom} !important;\n";
            $css .= "    --padding-left: {$left} !important;\n";
            $css .= "}\n\n";
            
            // Sections (legacy) - direct properties
            $css .= ".elementor-section.{$class_name} > .elementor-container {\n";
            $css .= "    padding-top: {$top} !important;\n";
            $css .= "    padding-right: {$right} !important;\n";
            $css .= "    padding-bottom: {$bottom} !important;\n";
            $css .= "    padding-left: {$left} !important;\n";
            $css .= "}\n\n";
            
            // Columns (legacy)
            $css .= ".elementor-column.{$class_name} > .elementor-widget-wrap {\n";
            $css .= "    padding-top: {$top} !important;\n";
            $css .= "    padding-right: {$right} !important;\n";
            $css .= "    padding-bottom: {$bottom} !important;\n";
            $css .= "    padding-left: {$left} !important;\n";
            $css .= "}\n\n";
        }
        
        // Generate margin classes
        foreach ($margin_classes as $class) {
            if (empty($class['name'])) {
                continue;
            }
            
            $class_name = sanitize_html_class($class['name']);
            if (empty($class_name)) {
                continue;
            }
            
            $top = $this->validate_css_value($class['top'] ?? '0');
            $right = $this->validate_css_value($class['right'] ?? '0');
            $bottom = $this->validate_css_value($class['bottom'] ?? '0');
            $left = $this->validate_css_value($class['left'] ?? '0');
            
            if ($top === false || $right === false || $bottom === false || $left === false) {
                continue;
            }
            
            // Margin applies to outer container - using CSS custom properties for Elementor compatibility
            // Containers (Flexbox/Grid)
            $css .= ".e-con.{$class_name} {\n";
            $css .= "    --margin-top: {$top} !important;\n";
            $css .= "    --margin-right: {$right} !important;\n";
            $css .= "    --margin-bottom: {$bottom} !important;\n";
            $css .= "    --margin-left: {$left} !important;\n";
            $css .= "}\n\n";
            
            // Sections (legacy) - direct properties
            $css .= ".elementor-section.{$class_name} {\n";
            $css .= "    margin-top: {$top} !important;\n";
            $css .= "    margin-right: {$right} !important;\n";
            $css .= "    margin-bottom: {$bottom} !important;\n";
            $css .= "    margin-left: {$left} !important;\n";
            $css .= "}\n\n";
            
            // Columns (legacy) - direct properties
            $css .= ".elementor-column.{$class_name} {\n";
            $css .= "    margin-top: {$top} !important;\n";
            $css .= "    margin-right: {$right} !important;\n";
            $css .= "    margin-bottom: {$bottom} !important;\n";
            $css .= "    margin-left: {$left} !important;\n";
            $css .= "}\n\n";
        }
        
        // Apply filters to allow developers to modify CSS
        $css = apply_filters('dce_dynamic_css', $css);
        
        return $css;
    }
    
    /**
     * Add settings link to plugin actions
     */
    public function add_settings_link($links) {
        // Check user capability
        if (!current_user_can('manage_options')) {
            return $links;
        }
        
        $kit_id = get_option('elementor_active_kit');
        if ($kit_id) {
            $settings_link = sprintf(
                '<a href="%s">%s</a>',
                esc_url(admin_url('post.php?post=' . absint($kit_id) . '&action=elementor#tab-dynamic-classes')),
                esc_html__('Settings', 'dynamic-classes-elementor')
            );
            array_unshift($links, $settings_link);
        }
        return $links;
    }
}

// Initialize the plugin
Dynamic_Classes_Elementor_Kit::get_instance();