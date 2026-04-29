<?php
/**
 * Main Plugin Class
 *
 * Bootstraps the plugin: checks requirements, loads includes,
 * wires up WordPress / Elementor hooks, and delegates work to
 * the specialised classes.
 *
 * Changes in 3.7.0:
 *  - Registers DCE_Settings_Page (WP submenu + export/import/reset UI).
 *  - Registers DCE_Ajax_Handler (export, import, reset AJAX actions).
 *  - Settings link on Plugins page now points to the new WP submenu.
 *
 * Changes in 3.6.0:
 *  - enqueue_styles() skips non-editor admin pages.
 *  - sync_default_data() only seeds empty class types (never overwrites).
 *  - CSS Generator instance cache (get_kit_classes once per type/request).
 *
 * @package DynamicClassesElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DCE_Plugin {

    private static ?DCE_Plugin $instance = null;

    private DCE_CSS_Generator    $css_generator;
    private DCE_Element_Controls $element_controls;
    private DCE_Ajax_Handler     $ajax_handler;
    private DCE_Settings_Page    $settings_page;

    public static function get_instance(): self {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    public function init(): void {
        if ( ! $this->check_requirements() ) {
            return;
        }
        $this->load_dependencies();
        $this->register_hooks();
        $this->maybe_sync_default_data();
    }

    private function check_requirements(): bool {
        if ( ! did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', [ $this, 'notice_missing_elementor' ] );
            return false;
        }
        if ( ! version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) {
            add_action( 'admin_notices', [ $this, 'notice_old_elementor' ] );
            return false;
        }
        return true;
    }

    private function load_dependencies(): void {
        $this->css_generator    = new DCE_CSS_Generator();
        $this->element_controls = new DCE_Element_Controls( $this->css_generator );
        $this->ajax_handler     = new DCE_Ajax_Handler();
        $this->settings_page    = new DCE_Settings_Page();
    }

    private function register_hooks(): void {
        add_action( 'init', [ $this, 'load_textdomain' ] );

        add_action( 'elementor/init',              [ $this, 'register_settings_tab_class' ] );
        add_action( 'elementor/kit/register_tabs', [ $this, 'register_settings_tab' ], 100 );

        $this->element_controls->register_hooks();
        $this->ajax_handler->register_hooks();
        $this->settings_page->register_hooks();

        add_action( 'wp_enqueue_scripts',                      [ $this, 'enqueue_styles'         ] );
        add_action( 'elementor/preview/enqueue_styles',        [ $this, 'enqueue_styles'         ] );
        add_action( 'elementor/editor/after_enqueue_scripts',  [ $this, 'enqueue_editor_scripts' ] );

        add_filter(
            'plugin_action_links_' . plugin_basename( DCE_PLUGIN_FILE ),
            [ $this, 'add_settings_link' ]
        );
    }

    // ── i18n ──────────────────────────────────────────────────────────────────

    public function load_textdomain(): void {
        load_plugin_textdomain(
            'dynamic-classes-elementor',
            false,
            dirname( plugin_basename( DCE_PLUGIN_FILE ) ) . '/languages'
        );
    }

    // ── Editor ────────────────────────────────────────────────────────────────

    public function enqueue_editor_scripts(): void {
        wp_enqueue_style(  'dce-editor', DCE_PLUGIN_URL . 'assets/css/dce-editor.css', [], DCE_VERSION );
        wp_enqueue_script( 'dce-editor', DCE_PLUGIN_URL . 'assets/js/dce-editor.js',  [ 'jquery' ], DCE_VERSION, true );
        wp_localize_script( 'dce-editor', 'DCE_Editor', [ 'template' => $this->get_calculator_template() ] );
    }

    private function get_calculator_template(): string {
        ob_start();
        ?>
        <div id="dce-calculator-wrapper" style="display: none;">
            <div class="dce-calculator-container">
                <div class="header">
                    <h1>clamp() Generator</h1>
                    <div class="toggle-container">
                        <label class="toggle-switch">
                            <input type="checkbox" id="configToggle" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="input-grid">
                    <div class="input-group">
                        <input id="minSize" type="number" value="" step="1" placeholder="Min">
                    </div>
                    <div class="input-group">
                        <input id="maxSize" type="number" value="" step="1" placeholder="Max" autofocus>
                    </div>
                </div>
                <div class="radio-grid">
                    <div class="radio-option">
                        <input type="radio" name="propertyType" id="radioText" value="text" checked>
                        <label for="radioText">Text</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" name="propertyType" id="radioPadding" value="padding">
                        <label for="radioPadding">Spacing</label>
                    </div>
                </div>
                <div class="config-section">
                    <div class="config-row">
                        <div class="config-pair">
                            <input id="configMinValue" type="number" min="1" max="500"  step="1" placeholder="Min Val">
                            <input id="configMinVp"    type="number" min="1" max="2000" step="1" placeholder="Min VP">
                        </div>
                        <div class="config-pair">
                            <input id="configMaxValue" type="number" min="1" max="500"  step="1" placeholder="Max Val">
                            <input id="configMaxVp"    type="number" min="1" max="2000" step="1" placeholder="Max VP">
                        </div>
                    </div>
                    <div class="config-row">
                        <div class="config-pair">
                            <input id="minWidth" type="number" min="1" max="5000" step="1" placeholder="Min Width">
                            <input id="maxWidth" type="number" min="1" max="5000" step="1" placeholder="Max Width">
                        </div>
                    </div>
                    <div class="config-row config-pair">
                        <div class="slider-row">
                            <div class="vp-badge-small" id="sliderMinBadge">
                                <span id="sliderCurrentVp">375</span>
                            </div>
                            <div class="slider-wrap-preview">
                                <div class="track-bg-preview"></div>
                                <div class="track-fill-preview" id="sliderTrackFill"></div>
                                <input type="range" id="vpPreviewSlider" min="375" max="1440" value="375" step="1">
                            </div>
                            <div class="vp-badge-small" id="sliderMaxBadge">
                                <span id="sliderCurrentClamp">00.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="result" class="clamp-output">0px, 0.00px + 0.00vw, 0px</div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // ── Kit Settings Tab ──────────────────────────────────────────────────────

    public function register_settings_tab_class(): void {
        if ( class_exists( '\Elementor\Core\Kits\Documents\Tabs\Tab_Base' ) ) {
            require_once DCE_PLUGIN_DIR . 'includes/class-settings-tab.php';
        }
    }

    public function register_settings_tab( $kit ): void {
        if ( ! current_user_can( 'manage_options' ) ) return;
        if ( class_exists( 'DCE_Settings_Tab' ) ) {
            $kit->register_tab( 'dynamic-classes', DCE_Settings_Tab::class );
        }
    }

    // ── CSS Output ────────────────────────────────────────────────────────────

    public function enqueue_styles(): void {
        wp_register_style( 'dce-frontend', false, [], DCE_VERSION );
        wp_enqueue_style( 'dce-frontend' );
        $css = $this->css_generator->generate();
        if ( ! empty( $css ) ) {
            wp_add_inline_style( 'dce-frontend', $css );
        }
    }

    // ── Data Sync ─────────────────────────────────────────────────────────────

    private function maybe_sync_default_data(): void {
        $saved_version = get_option( 'dce_version', '0.0.0' );
        if ( version_compare( $saved_version, DCE_VERSION, '<' ) ) {
            add_action( 'elementor/init', [ $this, 'sync_default_data' ] );
            update_option( 'dce_version', DCE_VERSION );
        }
    }

    /**
     * Seed defaults only for types that are completely empty.
     * User classes are NEVER overwritten.
     */
    public function sync_default_data(): void {
        try {
            if ( ! class_exists( '\Elementor\Plugin' ) ) return;

            $kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
            if ( ! $kit ) return;

            $types = [ 'gap', 'padding', 'margin', 'min_height', 'max_width' ];
            $to_update = [];

            foreach ( $types as $type ) {
                $key      = 'dce_' . $type . '_classes';
                $existing = $kit->get_settings( $key );
                if ( empty( $existing ) ) {
                    $defaults = DCE_Data_Loader::get( $type );
                    if ( ! empty( $defaults ) ) {
                        $to_update[ $key ] = $defaults;
                    }
                }
            }

            // --- Global Colors ---
            $existing_colors = $kit->get_settings( 'custom_colors' );
            $existing_colors = is_array( $existing_colors ) ? $existing_colors : [];
            $default_colors  = DCE_Data_Loader::get( 'global_colors' );

            if ( ! empty( $default_colors ) ) {
                $existing_ids = array_column( $existing_colors, '_id' );
                $added_colors = 0;
                foreach ( $default_colors as $color ) {
                    if ( ! in_array( $color['_id'], $existing_ids, true ) ) {
                        $existing_colors[] = $color;
                        $added_colors++;
                    }
                }
                if ( $added_colors > 0 ) {
                    $to_update['custom_colors'] = $existing_colors;
                }
            }

            // --- Global Fonts ---
            $existing_fonts = $kit->get_settings( 'custom_typography' );
            $existing_fonts = is_array( $existing_fonts ) ? $existing_fonts : [];
            $default_fonts  = DCE_Data_Loader::get( 'global_fonts' );

            if ( ! empty( $default_fonts ) ) {
                $existing_ids = array_column( $existing_fonts, '_id' );
                $added_fonts = 0;
                foreach ( $default_fonts as $font ) {
                    if ( ! in_array( $font['_id'], $existing_ids, true ) ) {
                        $existing_fonts[] = $font;
                        $added_fonts++;
                    }
                }
                if ( $added_fonts > 0 ) {
                    $to_update['custom_typography'] = $existing_fonts;
                }
            }

            if ( ! empty( $to_update ) ) {
                $kit->update_settings( $to_update );
                $this->css_generator->flush_cache();
            }
        } catch ( \Exception $e ) {
            error_log( 'DCE Sync error: ' . $e->getMessage() );
        }
    }

    // ── Admin Notices ─────────────────────────────────────────────────────────

    public function notice_missing_elementor(): void {
        $this->render_notice( sprintf(
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'dynamic-classes-elementor' ),
            '<strong>' . esc_html__( 'Dynamic Classes for Elementor', 'dynamic-classes-elementor' ) . '</strong>',
            '<strong>Elementor</strong>'
        ) );
    }

    public function notice_old_elementor(): void {
        $this->render_notice( sprintf(
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'dynamic-classes-elementor' ),
            '<strong>' . esc_html__( 'Dynamic Classes for Elementor', 'dynamic-classes-elementor' ) . '</strong>',
            '<strong>Elementor</strong>',
            '3.5.0'
        ) );
    }

    private function render_notice( string $message ): void {
        printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message );
    }

    // ── Plugins Page Link ─────────────────────────────────────────────────────

    public function add_settings_link( array $links ): array {
        if ( ! current_user_can( 'manage_options' ) ) return $links;
        $url = admin_url( 'options-general.php?page=dynamic-classes-elementor' );
        array_unshift(
            $links,
            sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html__( 'Settings', 'dynamic-classes-elementor' ) )
        );
        return $links;
    }
}
