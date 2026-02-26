<?php
/**
 * Main Plugin Class
 *
 * Bootstraps the plugin: checks requirements, loads includes,
 * wires up WordPress / Elementor hooks, and delegates work to
 * the specialised classes.
 *
 * @package DynamicClassesElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DCE_Plugin {

    private static ?DCE_Plugin $instance = null;

    private DCE_CSS_Generator   $css_generator;
    private DCE_Element_Controls $element_controls;

    // ── Singleton ─────────────────────────────────────────────────────────────

    public static function get_instance(): self {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    // ── Boot ──────────────────────────────────────────────────────────────────

    /**
     * Run requirement checks then wire up all hooks.
     * Fires on `plugins_loaded`.
     */
    public function init(): void {

        if ( ! $this->check_requirements() ) {
            return;
        }

        $this->load_dependencies();
        $this->register_hooks();
    }

    /**
     * Verify Elementor is present and recent enough.
     *
     * @return bool
     */
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

    /**
     * Instantiate all helper classes.
     * DCE_Data_Loader is static so no instantiation needed.
     */
    private function load_dependencies(): void {
        $this->css_generator    = new DCE_CSS_Generator();
        $this->element_controls = new DCE_Element_Controls( $this->css_generator );
    }

    /**
     * Register every WordPress / Elementor hook used by the plugin.
     */
    private function register_hooks(): void {

        // i18n
        add_action( 'init', [ $this, 'load_textdomain' ] );

        // Site Settings tab (registered inside elementor/init to ensure Tab_Base exists)
        add_action( 'elementor/init', [ $this, 'register_settings_tab_class' ] );
        add_action( 'elementor/kit/register_tabs', [ $this, 'register_settings_tab' ], 100 );

        // Element panel controls
        $this->element_controls->register_hooks();

        // CSS output – frontend and editor
        add_action( 'wp_enqueue_scripts',                    [ $this, 'enqueue_styles' ] );
        add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'enqueue_styles' ] );

        // Editor integration
        add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ] );

        // Plugins list settings link
        add_filter( 'plugin_action_links_' . plugin_basename( DCE_PLUGIN_FILE ), [ $this, 'add_settings_link' ] );
    }

    // ── i18n ──────────────────────────────────────────────────────────────────

    public function load_textdomain(): void {
        load_plugin_textdomain(
            'dynamic-classes-elementor',
            false,
            dirname( plugin_basename( DCE_PLUGIN_FILE ) ) . '/languages'
        );
    }

    // ── Editor Integration ────────────────────────────────────────────────────

    public function enqueue_editor_scripts(): void {
        wp_enqueue_style(
            'dce-editor',
            DCE_PLUGIN_URL . 'assets/css/dce-editor.css',
            [],
            DCE_VERSION
        );

        wp_enqueue_script(
            'dce-editor',
            DCE_PLUGIN_URL . 'assets/js/dce-editor.js',
            [ 'jquery' ],
            DCE_VERSION,
            true
        );

        wp_localize_script( 'dce-editor', 'DCE_Editor', [
            'template' => $this->get_calculator_template(),
        ] );
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
                        <input type="checkbox" id="configToggle">
                        <span class="toggle-slider"></span>
                    </label>
                    </div>
                </div>
                
                <div class="input-grid">
                    <div class="input-group">
                    <input id="minSize" type="number" value="" min="5" max="800" step="1" placeholder="Min">
                    </div>
                    <div class="input-group">
                    <input id="maxSize" type="number" value="" min="10" max="1000" step="1" placeholder="Max" autofocus>
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
                        <input id="configMinValue" type="number" min="1" max="500" step="1" placeholder="Min Val">
                        <input id="configMinVp" type="number" min="1" max="2000" step="1" placeholder="Min VP">
                    </div>
                    <div class="config-pair">
                        <input id="configMaxValue" type="number" min="1" max="500" step="1" placeholder="Max Val">
                        <input id="configMaxVp" type="number" min="1" max="2000" step="1" placeholder="Max VP">
                    </div>
                    </div>
                    <div class="config-row">
                    <div class="config-pair">
                        <input id="minWidth" type="number" min="1" max="5000" step="1" placeholder="Min Width">
                        <input id="maxWidth" type="number" min="1" max="5000" step="1" placeholder="Max Width">
                    </div>
                    </div>
                </div>

                <div id="result" class="clamp-output">clamp(00px, 00.0px + 0.00vw, 0px)</div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // ── Settings Tab ──────────────────────────────────────────────────────────

    /**
     * Define DCE_Settings_Tab once Tab_Base is available (inside elementor/init).
     */
    public function register_settings_tab_class(): void {
        if ( class_exists( '\Elementor\Core\Kits\Documents\Tabs\Tab_Base' ) ) {
            require_once DCE_PLUGIN_DIR . 'includes/class-settings-tab.php';
        }
    }

    /**
     * Register the tab with the active kit (capability-gated).
     */
    public function register_settings_tab( $kit ): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
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

    // ── Admin Notices ─────────────────────────────────────────────────────────

    public function notice_missing_elementor(): void {
        $this->render_notice( sprintf(
            /* translators: 1: plugin name, 2: Elementor */
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'dynamic-classes-elementor' ),
            '<strong>' . esc_html__( 'Dynamic Classes for Elementor', 'dynamic-classes-elementor' ) . '</strong>',
            '<strong>Elementor</strong>'
        ) );
    }

    public function notice_old_elementor(): void {
        $this->render_notice( sprintf(
            /* translators: 1: plugin name, 2: Elementor, 3: version number */
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
        if ( ! current_user_can( 'manage_options' ) ) {
            return $links;
        }
        $kit_id = get_option( 'elementor_active_kit' );
        if ( $kit_id ) {
            $url = admin_url( 'post.php?post=' . absint( $kit_id ) . '&action=elementor#tab-dynamic-classes' );
            array_unshift( $links, sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html__( 'Settings', 'dynamic-classes-elementor' ) ) );
        }
        return $links;
    }
}
