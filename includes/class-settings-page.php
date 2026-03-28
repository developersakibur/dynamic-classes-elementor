<?php
/**
 * Settings Page
 *
 * Registers a "Dynamic Classes" submenu under WordPress
 * Settings → Dynamic Classes.
 *
 * The page renders:
 *   - A prominent "Open in Elementor" button that navigates
 *     directly to the Dynamic Classes kit tab.
 *   - Export / Import / Reset controls for class management.
 *
 * @package DynamicClassesElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DCE_Settings_Page {

    // ── Boot ──────────────────────────────────────────────────────────────────

    public function register_hooks(): void {
        add_action( 'admin_menu',            [ $this, 'add_submenu'     ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets'  ] );
    }

    // ── Menu ──────────────────────────────────────────────────────────────────

    public function add_submenu(): void {
        add_options_page(
            esc_html__( 'Dynamic Classes for Elementor', 'dynamic-classes-elementor' ),
            esc_html__( 'Dynamic Classes', 'dynamic-classes-elementor' ),
            'manage_options',
            'dynamic-classes-elementor',
            [ $this, 'render_page' ]
        );
    }

    // ── Assets ────────────────────────────────────────────────────────────────

    public function enqueue_assets( string $hook ): void {
        if ( $hook !== 'settings_page_dynamic-classes-elementor' ) {
            return;
        }

        wp_enqueue_style(
            'dce-settings-page',
            DCE_PLUGIN_URL . 'assets/css/dce-settings-page.css',
            [],
            DCE_VERSION
        );

        wp_enqueue_script(
            'dce-settings-page',
            DCE_PLUGIN_URL . 'assets/js/dce-settings-page.js',
            [ 'jquery' ],
            DCE_VERSION,
            true
        );

        $kit_id     = get_option( 'elementor_active_kit' );
        $editor_url = $kit_id
            ? admin_url( 'post.php?post=' . absint( $kit_id ) . '&action=elementor#tab-dynamic-classes' )
            : '';

        wp_localize_script( 'dce-settings-page', 'DCE_Settings', [
            'ajax_url'       => admin_url( 'admin-ajax.php' ),
            'editor_url'     => $editor_url,
            'export_nonce'   => wp_create_nonce( 'dce_export_nonce' ),
            'import_nonce'   => wp_create_nonce( 'dce_import_nonce' ),
            'reset_nonce'    => wp_create_nonce( 'dce_reset_nonce' ),
            'confirm_reset'  => __( 'This will permanently replace ALL your classes with factory defaults. This cannot be undone. Are you sure?', 'dynamic-classes-elementor' ),
            'confirm_import' => __( 'New classes from the file will be merged into your existing classes. Duplicates will be skipped. Continue?', 'dynamic-classes-elementor' ),
            'i18n'           => [
                'importing'  => __( 'Importing…', 'dynamic-classes-elementor' ),
                'resetting'  => __( 'Resetting…', 'dynamic-classes-elementor' ),
                'success'    => __( 'Done!', 'dynamic-classes-elementor' ),
                'error'      => __( 'Something went wrong. Please try again.', 'dynamic-classes-elementor' ),
            ],
        ] );
    }

    // ── Page Render ───────────────────────────────────────────────────────────

    public function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $kit_id     = get_option( 'elementor_active_kit' );
        $editor_url = $kit_id
            ? admin_url( 'post.php?post=' . absint( $kit_id ) . '&action=elementor#tab-dynamic-classes' )
            : '';

        $class_counts = $this->get_class_counts();
        ?>
        <div class="wrap dce-settings-wrap">

            <!-- Header -->
            <div class="dce-header">
                <div class="dce-header__title">
                    <span class="dashicons dashicons-editor-code"></span>
                    <h1><?php esc_html_e( 'Dynamic Classes for Elementor', 'dynamic-classes-elementor' ); ?></h1>
                    <span class="dce-version">v<?php echo esc_html( DCE_VERSION ); ?></span>
                </div>
                <?php if ( $editor_url ) : ?>
                <a href="<?php echo esc_url( $editor_url ); ?>" class="dce-btn dce-btn--primary dce-btn--open" target="_blank">
                    <span class="dashicons dashicons-external"></span>
                    <?php esc_html_e( 'Open in Elementor', 'dynamic-classes-elementor' ); ?>
                </a>
                <?php endif; ?>
            </div>

            <!-- Stats bar -->
            <div class="dce-stats">
                <?php foreach ( $class_counts as $label => $count ) : ?>
                <div class="dce-stat">
                    <span class="dce-stat__count"><?php echo absint( $count ); ?></span>
                    <span class="dce-stat__label"><?php echo esc_html( $label ); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Main grid -->
            <div class="dce-grid">

                <!-- Export -->
                <div class="dce-card">
                    <div class="dce-card__icon dce-card__icon--export">
                        <span class="dashicons dashicons-download"></span>
                    </div>
                    <h2><?php esc_html_e( 'Export Classes', 'dynamic-classes-elementor' ); ?></h2>
                    <p><?php esc_html_e( 'Download all your gap, padding, margin, min-height and max-width classes as a JSON file. Use it as a backup or to migrate to another site.', 'dynamic-classes-elementor' ); ?></p>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" class="dce-export-form">
                        <input type="hidden" name="action" value="dce_export">
                        <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'dce_export_nonce' ) ); ?>">
                        <button type="submit" class="dce-btn dce-btn--secondary">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e( 'Export JSON', 'dynamic-classes-elementor' ); ?>
                        </button>
                    </form>
                </div>

                <!-- Import -->
                <div class="dce-card">
                    <div class="dce-card__icon dce-card__icon--import">
                        <span class="dashicons dashicons-upload"></span>
                    </div>
                    <h2><?php esc_html_e( 'Import Classes', 'dynamic-classes-elementor' ); ?></h2>
                    <p><?php esc_html_e( 'Upload a previously exported JSON file. New classes will be merged — duplicates are skipped and existing classes are never overwritten.', 'dynamic-classes-elementor' ); ?></p>
                    <div class="dce-import-area" id="dce-import-area">
                        <span class="dashicons dashicons-media-text"></span>
                        <span class="dce-import-area__label"><?php esc_html_e( 'Click to choose a JSON file', 'dynamic-classes-elementor' ); ?></span>
                        <input type="file" id="dce-import-file" accept=".json" style="display:none;">
                    </div>
                    <button type="button" class="dce-btn dce-btn--secondary" id="dce-import-btn" disabled>
                        <span class="dashicons dashicons-upload"></span>
                        <span id="dce-import-btn-label"><?php esc_html_e( 'Import JSON', 'dynamic-classes-elementor' ); ?></span>
                    </button>
                    <div class="dce-notice" id="dce-import-notice" style="display:none;"></div>
                </div>

                <!-- Reset -->
                <div class="dce-card dce-card--danger">
                    <div class="dce-card__icon dce-card__icon--reset">
                        <span class="dashicons dashicons-image-rotate"></span>
                    </div>
                    <h2><?php esc_html_e( 'Reset to Defaults', 'dynamic-classes-elementor' ); ?></h2>
                    <p><?php esc_html_e( 'Replace ALL your classes with the plugin\'s factory defaults. This permanently removes any custom classes you have created. Export first if you want a backup.', 'dynamic-classes-elementor' ); ?></p>
                    <button type="button" class="dce-btn dce-btn--danger" id="dce-reset-btn">
                        <span class="dashicons dashicons-image-rotate"></span>
                        <span id="dce-reset-btn-label"><?php esc_html_e( 'Reset All Classes', 'dynamic-classes-elementor' ); ?></span>
                    </button>
                    <div class="dce-notice" id="dce-reset-notice" style="display:none;"></div>
                </div>

            </div><!-- .dce-grid -->

            <!-- Footer -->
            <div class="dce-footer">
                <p>
                    <?php esc_html_e( 'After importing or resetting, go to', 'dynamic-classes-elementor' ); ?>
                    <?php if ( $editor_url ) : ?>
                        <a href="<?php echo esc_url( $editor_url ); ?>" target="_blank"><?php esc_html_e( 'Elementor → Site Settings → Dynamic Classes', 'dynamic-classes-elementor' ); ?></a>
                    <?php else : ?>
                        <?php esc_html_e( 'Elementor → Site Settings → Dynamic Classes', 'dynamic-classes-elementor' ); ?>
                    <?php endif; ?>
                    <?php esc_html_e( 'to review your classes.', 'dynamic-classes-elementor' ); ?>
                </p>
            </div>

        </div><!-- .wrap -->
        <?php
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Return a [ label => count ] array of how many classes exist per type.
     *
     * @return array<string, int>
     */
    private function get_class_counts(): array {
        if ( ! class_exists( '\Elementor\Plugin' ) ) {
            return [];
        }

        try {
            $kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
            if ( ! $kit ) {
                return [];
            }
        } catch ( \Exception $e ) {
            return [];
        }

        $labels = [
            'gap'        => __( 'Gap',        'dynamic-classes-elementor' ),
            'padding'    => __( 'Padding',    'dynamic-classes-elementor' ),
            'margin'     => __( 'Margin',     'dynamic-classes-elementor' ),
            'min_height' => __( 'Min-Height', 'dynamic-classes-elementor' ),
            'max_width'  => __( 'Max-Width',  'dynamic-classes-elementor' ),
        ];

        $counts = [];
        foreach ( $labels as $type => $label ) {
            $classes          = $kit->get_settings( 'dce_' . $type . '_classes' );
            $counts[ $label ] = is_array( $classes ) ? count( $classes ) : 0;
        }

        return $counts;
    }
}
