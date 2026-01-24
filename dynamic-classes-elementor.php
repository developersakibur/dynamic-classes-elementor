<?php
/**
 * Plugin Name: Dynamic Classes for Elementor
 * Plugin URI: https://yourwebsite.com
 * Description: Add dynamic, responsive CSS classes (gap, padding, margin, etc.) to Elementor with admin controls
 * Version: 2.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * Text Domain: dynamic-classes-elementor
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Elementor tested up to: 3.19
 * Elementor Pro tested up to: 3.19
 */

if (!defined('ABSPATH')) {
    exit;
}

class Dynamic_Classes_Elementor {
    
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
        
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        
        // Use wp_enqueue_scripts for frontend and Elementor preview
        add_action('wp_enqueue_scripts', [$this, 'enqueue_dynamic_styles']);
        add_action('elementor/preview/enqueue_styles', [$this, 'enqueue_dynamic_styles'], 999);
        
        add_action('elementor/element/container/section_layout/after_section_end', [$this, 'add_dynamic_class_control'], 10, 2);
        add_action('elementor/element/section/section_advanced/after_section_end', [$this, 'add_dynamic_class_control'], 10, 2);
        add_action('elementor/element/column/section_advanced/after_section_end', [$this, 'add_dynamic_class_control'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        
        // AJAX handlers
        add_action('wp_ajax_dce_save_classes', [$this, 'ajax_save_classes']);
        add_action('wp_ajax_dce_delete_class', [$this, 'ajax_delete_class']);
    }
    
    public function admin_notice_missing_elementor() {
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'dynamic-classes-elementor'),
            '<strong>' . esc_html__('Dynamic Classes for Elementor', 'dynamic-classes-elementor') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'dynamic-classes-elementor') . '</strong>'
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Dynamic Classes', 'dynamic-classes-elementor'),
            __('Dynamic Classes', 'dynamic-classes-elementor'),
            'manage_options',
            'dynamic-classes-elementor',
            [$this, 'admin_page'],
            'dashicons-editor-code',
            59
        );
    }
    
    public function register_settings() {
        register_setting('dce_settings', 'dce_gap_classes');
        register_setting('dce_settings', 'dce_padding_classes');
        register_setting('dce_settings', 'dce_margin_classes');
    }
    
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_dynamic-classes-elementor' !== $hook) {
            return;
        }
        
        wp_enqueue_script('dce-admin-js', plugin_dir_url(__FILE__) . 'admin.js', ['jquery'], '2.0.0', true);
        wp_localize_script('dce-admin-js', 'dceAdmin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dce_admin_nonce')
        ]);
        
        wp_enqueue_style('dce-admin-css', plugin_dir_url(__FILE__) . 'admin.css', [], '2.0.0');
    }
    
    public function admin_page() {
        $gap_classes = get_option('dce_gap_classes', []);
        $padding_classes = get_option('dce_padding_classes', []);
        $margin_classes = get_option('dce_margin_classes', []);
        ?>
        <div class="wrap dce-admin-wrap">
            <h1><?php echo esc_html__('Dynamic Classes for Elementor', 'dynamic-classes-elementor'); ?></h1>
            
            <div class="dce-tabs">
                <button class="dce-tab-btn active" data-tab="gap">
                    <span class="dashicons dashicons-editor-table"></span>
                    <?php esc_html_e('Gap Classes', 'dynamic-classes-elementor'); ?>
                </button>
                <button class="dce-tab-btn" data-tab="padding">
                    <span class="dashicons dashicons-align-center"></span>
                    <?php esc_html_e('Padding Classes', 'dynamic-classes-elementor'); ?>
                </button>
                <button class="dce-tab-btn" data-tab="margin">
                    <span class="dashicons dashicons-move"></span>
                    <?php esc_html_e('Margin Classes', 'dynamic-classes-elementor'); ?>
                </button>
            </div>
            
            <form id="dce-main-form" method="post">
                <?php wp_nonce_field('dce_save_action', 'dce_nonce'); ?>
                
                <!-- Gap Tab -->
                <div class="dce-tab-content active" id="tab-gap">
                    <div class="dce-header">
                        <h2><?php esc_html_e('Gap Classes', 'dynamic-classes-elementor'); ?></h2>
                        <button type="button" class="button button-primary dce-add-class" data-type="gap">
                            <span class="dashicons dashicons-plus-alt"></span>
                            <?php esc_html_e('Add New Gap Class', 'dynamic-classes-elementor'); ?>
                        </button>
                    </div>
                    
                    <div id="gap-classes-container">
                        <?php if (empty($gap_classes)): ?>
                            <p class="dce-empty-message"><?php esc_html_e('No gap classes yet. Click "Add New Gap Class" to create one.', 'dynamic-classes-elementor'); ?></p>
                        <?php else: ?>
                            <?php foreach ($gap_classes as $index => $class): ?>
                                <?php $this->render_gap_class_row($index, $class); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Padding Tab -->
                <div class="dce-tab-content" id="tab-padding">
                    <div class="dce-header">
                        <h2><?php esc_html_e('Padding Classes', 'dynamic-classes-elementor'); ?></h2>
                        <button type="button" class="button button-primary dce-add-class" data-type="padding">
                            <span class="dashicons dashicons-plus-alt"></span>
                            <?php esc_html_e('Add New Padding Class', 'dynamic-classes-elementor'); ?>
                        </button>
                    </div>
                    
                    <div id="padding-classes-container">
                        <?php if (empty($padding_classes)): ?>
                            <p class="dce-empty-message"><?php esc_html_e('No padding classes yet. Click "Add New Padding Class" to create one.', 'dynamic-classes-elementor'); ?></p>
                        <?php else: ?>
                            <?php foreach ($padding_classes as $index => $class): ?>
                                <?php $this->render_padding_class_row($index, $class); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Margin Tab -->
                <div class="dce-tab-content" id="tab-margin">
                    <div class="dce-header">
                        <h2><?php esc_html_e('Margin Classes', 'dynamic-classes-elementor'); ?></h2>
                        <button type="button" class="button button-primary dce-add-class" data-type="margin">
                            <span class="dashicons dashicons-plus-alt"></span>
                            <?php esc_html_e('Add New Margin Class', 'dynamic-classes-elementor'); ?>
                        </button>
                    </div>
                    
                    <div id="margin-classes-container">
                        <?php if (empty($margin_classes)): ?>
                            <p class="dce-empty-message"><?php esc_html_e('No margin classes yet. Click "Add New Margin Class" to create one.', 'dynamic-classes-elementor'); ?></p>
                        <?php else: ?>
                            <?php foreach ($margin_classes as $index => $class): ?>
                                <?php $this->render_margin_class_row($index, $class); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="dce-save-section">
                    <button type="submit" class="button button-primary button-large">
                        <span class="dashicons dashicons-saved"></span>
                        <?php esc_html_e('Save All Changes', 'dynamic-classes-elementor'); ?>
                    </button>
                    <span class="dce-save-message"></span>
                </div>
            </form>
        </div>
        
        <!-- Templates for new rows -->
        <script type="text/template" id="dce-gap-template">
            <?php $this->render_gap_class_row('{{INDEX}}', ['name' => '', 'row_gap' => '', 'column_gap' => '']); ?>
        </script>
        
        <script type="text/template" id="dce-padding-template">
            <?php $this->render_padding_class_row('{{INDEX}}', ['name' => '', 'top' => '', 'right' => '', 'bottom' => '', 'left' => '']); ?>
        </script>
        
        <script type="text/template" id="dce-margin-template">
            <?php $this->render_margin_class_row('{{INDEX}}', ['name' => '', 'top' => '', 'right' => '', 'bottom' => '', 'left' => '']); ?>
        </script>
        <?php
    }
    
    private function render_gap_class_row($index, $class) {
        ?>
        <div class="dce-class-row" data-index="<?php echo esc_attr($index); ?>">
            <div class="dce-class-grid">
                <div class="dce-class-name">
                    <label><?php esc_html_e('Class Name', 'dynamic-classes-elementor'); ?></label>
                    <input type="text" 
                           name="gap_classes[<?php echo esc_attr($index); ?>][name]" 
                           value="<?php echo esc_attr($class['name'] ?? ''); ?>" 
                           placeholder="gap-custom-1"
                           required>
                </div>
                
                <div class="dce-class-value">
                    <label><?php esc_html_e('Row Gap', 'dynamic-classes-elementor'); ?></label>
                    <input type="text" 
                           name="gap_classes[<?php echo esc_attr($index); ?>][row_gap]" 
                           value="<?php echo esc_attr($class['row_gap'] ?? ''); ?>" 
                           placeholder="20px or 1.5rem"
                           required>
                </div>
                
                <div class="dce-class-value">
                    <label><?php esc_html_e('Column Gap', 'dynamic-classes-elementor'); ?></label>
                    <input type="text" 
                           name="gap_classes[<?php echo esc_attr($index); ?>][column_gap]" 
                           value="<?php echo esc_attr($class['column_gap'] ?? ''); ?>" 
                           placeholder="20px or 1.5rem"
                           required>
                </div>
                
                <div class="dce-class-actions">
                    <button type="button" class="button dce-delete-class" title="<?php esc_attr_e('Delete', 'dynamic-classes-elementor'); ?>">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_padding_class_row($index, $class) {
        ?>
        <div class="dce-class-row" data-index="<?php echo esc_attr($index); ?>">
            <div class="dce-class-grid dce-class-grid-4">
                <div class="dce-class-name">
                    <label><?php esc_html_e('Class Name', 'dynamic-classes-elementor'); ?></label>
                    <input type="text" 
                           name="padding_classes[<?php echo esc_attr($index); ?>][name]" 
                           value="<?php echo esc_attr($class['name'] ?? ''); ?>" 
                           placeholder="padding-custom-1"
                           required>
                </div>
                
                <div class="dce-class-value">
                    <label><?php esc_html_e('Top', 'dynamic-classes-elementor'); ?></label>
                    <input type="text" 
                           name="padding_classes[<?php echo esc_attr($index); ?>][top]" 
                           value="<?php echo esc_attr($class['top'] ?? ''); ?>" 
                           placeholder="10px"
                           required>
                </div>
                
                <div class="dce-class-value">
                    <label><?php esc_html_e('Right', 'dynamic-classes-elementor'); ?></label>
                    <input type="text" 
                           name="padding_classes[<?php echo esc_attr($index); ?>][right]" 
                           value="<?php echo esc_attr($class['right'] ?? ''); ?>" 
                           placeholder="10px"
                           required>
                </div>
                
                <div class="dce-class-value">
                    <label><?php esc_html_e('Bottom', 'dynamic-classes-elementor'); ?></label>
                    <input type="text" 
                           name="padding_classes[<?php echo esc_attr($index); ?>][bottom]" 
                           value="<?php echo esc_attr($class['bottom'] ?? ''); ?>" 
                           placeholder="10px"
                           required>
                </div>
                
                <div class="dce-class-value">
                    <label><?php esc_html_e('Left', 'dynamic-classes-elementor'); ?></label>
                    <input type="text" 
                           name="padding_classes[<?php echo esc_attr($index); ?>][left]" 
                           value="<?php echo esc_attr($class['left'] ?? ''); ?>" 
                           placeholder="10px"
                           required>
                </div>
                
                <div class="dce-class-actions">
                    <button type="button" class="button dce-delete-class" title="<?php esc_attr_e('Delete', 'dynamic-classes-elementor'); ?>">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_margin_class_row($index, $class) {
        ?>
        <div class="dce-class-row" data-index="<?php echo esc_attr($index); ?>">
            <div class="dce-class-grid dce-class-grid-4">
                <div class="dce-class-name">
                    <label><?php esc_html_e('Class Name', 'dynamic-classes-elementor'); ?></label>
                    <input type="text" 
                           name="margin_classes[<?php echo esc_attr($index); ?>][name]" 
                           value="<?php echo esc_attr($class['name'] ?? ''); ?>" 
                           placeholder="margin-custom-1"
                           required>
                </div>
                
                <div class="dce-class-value">
                    <label><?php esc_html_e('Top', 'dynamic-classes-elementor'); ?></label>
                    <input type="text" 
                           name="margin_classes[<?php echo esc_attr($index); ?>][top]" 
                           value="<?php echo esc_attr($class['top'] ?? ''); ?>" 
                           placeholder="10px"
                           required>
                </div>
                
                <div class="dce-class-value">
                    <label><?php esc_html_e('Right', 'dynamic-classes-elementor'); ?></label>
                    <input type="text" 
                           name="margin_classes[<?php echo esc_attr($index); ?>][right]" 
                           value="<?php echo esc_attr($class['right'] ?? ''); ?>" 
                           placeholder="10px"
                           required>
                </div>
                
                <div class="dce-class-value">
                    <label><?php esc_html_e('Bottom', 'dynamic-classes-elementor'); ?></label>
                    <input type="text" 
                           name="margin_classes[<?php echo esc_attr($index); ?>][bottom]" 
                           value="<?php echo esc_attr($class['bottom'] ?? ''); ?>" 
                           placeholder="10px"
                           required>
                </div>
                
                <div class="dce-class-value">
                    <label><?php esc_html_e('Left', 'dynamic-classes-elementor'); ?></label>
                    <input type="text" 
                           name="margin_classes[<?php echo esc_attr($index); ?>][left]" 
                           value="<?php echo esc_attr($class['left'] ?? ''); ?>" 
                           placeholder="10px"
                           required>
                </div>
                
                <div class="dce-class-actions">
                    <button type="button" class="button dce-delete-class" title="<?php esc_attr_e('Delete', 'dynamic-classes-elementor'); ?>">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function ajax_save_classes() {
        check_ajax_referer('dce_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $gap_classes = isset($_POST['gap_classes']) ? $_POST['gap_classes'] : [];
        $padding_classes = isset($_POST['padding_classes']) ? $_POST['padding_classes'] : [];
        $margin_classes = isset($_POST['margin_classes']) ? $_POST['margin_classes'] : [];
        
        // Sanitize and save
        $gap_classes = array_map(function($class) {
            return [
                'name' => sanitize_text_field($class['name']),
                'row_gap' => sanitize_text_field($class['row_gap']),
                'column_gap' => sanitize_text_field($class['column_gap']),
            ];
        }, $gap_classes);
        
        $padding_classes = array_map(function($class) {
            return [
                'name' => sanitize_text_field($class['name']),
                'top' => sanitize_text_field($class['top']),
                'right' => sanitize_text_field($class['right']),
                'bottom' => sanitize_text_field($class['bottom']),
                'left' => sanitize_text_field($class['left']),
            ];
        }, $padding_classes);
        
        $margin_classes = array_map(function($class) {
            return [
                'name' => sanitize_text_field($class['name']),
                'top' => sanitize_text_field($class['top']),
                'right' => sanitize_text_field($class['right']),
                'bottom' => sanitize_text_field($class['bottom']),
                'left' => sanitize_text_field($class['left']),
            ];
        }, $margin_classes);
        
        update_option('dce_gap_classes', $gap_classes);
        update_option('dce_padding_classes', $padding_classes);
        update_option('dce_margin_classes', $margin_classes);
        
        wp_send_json_success(['message' => 'Classes saved successfully!']);
    }

    public function enqueue_dynamic_styles() {
        // Register a dummy stylesheet to attach the inline styles to.
        wp_register_style('dce-frontend', false);
        wp_enqueue_style('dce-frontend');

        $dynamic_css = $this->get_dynamic_css();

        if (!empty($dynamic_css)) {
            wp_add_inline_style('dce-frontend', $dynamic_css);
        }
    }
    
    private function get_dynamic_css() {
        $gap_classes = get_option('dce_gap_classes', []);
        $padding_classes = get_option('dce_padding_classes', []);
        $margin_classes = get_option('dce_margin_classes', []);
        
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
                $css .= ".{$class['name']}.e-con {
                    margin-top: {$class['top']} !important;
                    margin-right: {$class['right']} !important;
                    margin-bottom: {$class['bottom']} !important;
                    margin-left: {$class['left']} !important;
                }\n";
            }
        }
        
        return $css;
    }
    
    public function add_dynamic_class_control($element, $args) {
        $gap_classes = get_option('dce_gap_classes', []);
        $padding_classes = get_option('dce_padding_classes', []);
        $margin_classes = get_option('dce_margin_classes', []);
        
        // Build options arrays
        $gap_options = ['' => __('Select Gap Class', 'dynamic-classes-elementor')];
        foreach ($gap_classes as $class) {
            if (!empty($class['name'])) {
                $gap_options[$class['name']] = $class['name'];
            }
        }
        
        $padding_options = ['' => __('Select Padding Class', 'dynamic-classes-elementor')];
        foreach ($padding_classes as $class) {
            if (!empty($class['name'])) {
                $padding_options[$class['name']] = $class['name'];
            }
        }
        
        $margin_options = ['' => __('Select Margin Class', 'dynamic-classes-elementor')];
        foreach ($margin_classes as $class) {
            if (!empty($class['name'])) {
                $margin_options[$class['name']] = $class['name'];
            }
        }
        
        $element->start_controls_section(
            'dce_dynamic_classes_section',
            [
                'label' => __('Dynamic Classes', 'dynamic-classes-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        // Gap control
        if (!empty($gap_classes)) {
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
        if (!empty($padding_classes)) {
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
        if (!empty($margin_classes)) {
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
}

Dynamic_Classes_Elementor::get_instance();