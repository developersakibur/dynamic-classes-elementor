<?php
/**
 * AJAX Handler
 *
 * Handles three admin-only AJAX actions:
 *   dce_export  – streams all kit classes as a downloadable JSON file
 *   dce_import  – merges an uploaded JSON file into the active kit
 *   dce_reset   – restores factory defaults (replaces ALL types entirely)
 *
 * Every action is capability-gated and nonce-verified.
 *
 * @package DynamicClassesElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DCE_Ajax_Handler {

    /** Supported class types in a consistent order. */
    const TYPES = [ 'gap', 'padding', 'margin', 'min_height', 'max_width' ];

    // ── Boot ──────────────────────────────────────────────────────────────────

    public function register_hooks(): void {
        add_action( 'wp_ajax_dce_export', [ $this, 'handle_export' ] );
        add_action( 'wp_ajax_dce_import', [ $this, 'handle_import' ] );
        add_action( 'wp_ajax_dce_reset',  [ $this, 'handle_reset'  ] );
    }

    // ── Export ────────────────────────────────────────────────────────────────

    /**
     * Stream all current kit classes as a JSON file download.
     * Triggered by a plain form POST (not fetch) so the browser
     * shows a Save-As dialog without any JS blob gymnastics.
     */
    public function handle_export(): void {
        $this->verify( 'dce_export_nonce' );

        $kit     = $this->get_kit();
        $payload = [];

        foreach ( self::TYPES as $type ) {
            $classes = $kit->get_settings( 'dce_' . $type . '_classes' );
            $payload[ $type ] = is_array( $classes ) ? $classes : [];
        }

        $json     = wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
        $filename = 'dce-classes-' . gmdate( 'Y-m-d' ) . '.json';

        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Content-Length: ' . strlen( $json ) );
        header( 'Cache-Control: no-cache, no-store, must-revalidate' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        echo $json; // phpcs:ignore WordPress.Security.EscapeOutput
        exit;
    }

    // ── Import ────────────────────────────────────────────────────────────────

    /**
     * Merge an uploaded JSON file into the active kit.
     *
     * Merge strategy: for each type in the uploaded file,
     * any class whose name does not already exist in the kit
     * is appended. Existing classes are left untouched.
     * Unknown types and invalid values are silently ignored.
     */
    public function handle_import(): void {
        $this->verify( 'dce_import_nonce' );

        if ( empty( $_FILES['dce_import_file']['tmp_name'] ) ) {
            wp_send_json_error( [ 'message' => __( 'No file uploaded.', 'dynamic-classes-elementor' ) ] );
        }

        $tmp  = $_FILES['dce_import_file']['tmp_name'];
        $json = file_get_contents( $tmp );

        if ( $json === false ) {
            wp_send_json_error( [ 'message' => __( 'Could not read uploaded file.', 'dynamic-classes-elementor' ) ] );
        }

        $data = json_decode( $json, true );

        if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $data ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid JSON file.', 'dynamic-classes-elementor' ) ] );
        }

        $kit            = $this->get_kit();
        $to_update      = [];
        $imported_count = 0;

        foreach ( self::TYPES as $type ) {
            if ( empty( $data[ $type ] ) || ! is_array( $data[ $type ] ) ) {
                continue;
            }

            $existing = $kit->get_settings( 'dce_' . $type . '_classes' );
            $existing = is_array( $existing ) ? $existing : [];

            // Build a set of existing class names for fast lookup
            $existing_names = array_column( $existing, 'name' );
            $existing_names = array_flip( array_map( 'sanitize_html_class', $existing_names ) );

            foreach ( $data[ $type ] as $class ) {
                if ( empty( $class['name'] ) ) {
                    continue;
                }
                $slug = sanitize_html_class( $class['name'] );
                if ( empty( $slug ) || isset( $existing_names[ $slug ] ) ) {
                    continue; // skip duplicates
                }
                $existing[]             = $class;
                $existing_names[ $slug ] = true;
                $imported_count++;
            }

            $to_update[ 'dce_' . $type . '_classes' ] = $existing;
        }

        if ( ! empty( $to_update ) ) {
            $kit->update_settings( $to_update );
        }

        wp_send_json_success( [
            'message' => sprintf(
                /* translators: %d: number of classes imported */
                _n(
                    '%d class imported successfully.',
                    '%d classes imported successfully.',
                    $imported_count,
                    'dynamic-classes-elementor'
                ),
                $imported_count
            ),
            'count' => $imported_count,
        ] );
    }

    // ── Reset ─────────────────────────────────────────────────────────────────

    /**
     * Overwrite ALL class types with the factory defaults from JSON.
     * This is the one intentional "destructive" action — the user
     * explicitly clicks Reset and confirms the browser dialog.
     */
    public function handle_reset(): void {
        $this->verify( 'dce_reset_nonce' );

        $kit       = $this->get_kit();
        $to_update = [];

        foreach ( self::TYPES as $type ) {
            $to_update[ 'dce_' . $type . '_classes' ] = DCE_Data_Loader::get( $type );
        }

        // --- Reset Global Colors ---
        $existing_colors = $kit->get_settings( 'custom_colors' );
        $existing_colors = is_array( $existing_colors ) ? $existing_colors : [];
        $default_colors  = DCE_Data_Loader::get( 'global_colors' );
        if ( ! empty( $default_colors ) ) {
            $existing_ids = array_column( $existing_colors, '_id' );
            foreach ( $default_colors as $color ) {
                if ( ! in_array( $color['_id'], $existing_ids, true ) ) {
                    $existing_colors[] = $color;
                }
            }
            $to_update['custom_colors'] = $existing_colors;
        }

        // --- Reset Global Fonts ---
        $existing_fonts = $kit->get_settings( 'custom_typography' );
        $existing_fonts = is_array( $existing_fonts ) ? $existing_fonts : [];
        $default_fonts  = DCE_Data_Loader::get( 'global_fonts' );
        if ( ! empty( $default_fonts ) ) {
            $existing_ids = array_column( $existing_fonts, '_id' );
            foreach ( $default_fonts as $font ) {
                if ( ! in_array( $font['_id'], $existing_ids, true ) ) {
                    $existing_fonts[] = $font;
                }
            }
            $to_update['custom_typography'] = $existing_fonts;
        }

        $kit->update_settings( $to_update );

        wp_send_json_success( [
            'message' => __( 'All classes have been reset to factory defaults.', 'dynamic-classes-elementor' ),
        ] );
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Verify nonce + capability. Dies on failure.
     *
     * @param string $nonce_action
     */
    private function verify( string $nonce_action ): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Permission denied.', 'dynamic-classes-elementor' ) ], 403 );
        }

        $nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '';

        if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
            wp_send_json_error( [ 'message' => __( 'Security check failed.', 'dynamic-classes-elementor' ) ], 403 );
        }
    }

    /**
     * Return the active Elementor Kit or die with an error.
     *
     * @return \Elementor\Core\Kits\Documents\Kit
     */
    private function get_kit() {
        if ( ! class_exists( '\Elementor\Plugin' ) ) {
            wp_send_json_error( [ 'message' => __( 'Elementor not available.', 'dynamic-classes-elementor' ) ] );
        }

        $kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();

        if ( ! $kit ) {
            wp_send_json_error( [ 'message' => __( 'Could not load Elementor Kit.', 'dynamic-classes-elementor' ) ] );
        }

        return $kit;
    }
}
