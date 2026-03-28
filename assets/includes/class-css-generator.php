<?php
/**
 * CSS Generator
 *
 * Fetches saved class data from the active Elementor Kit,
 * validates every CSS value, and returns a ready-to-output
 * stylesheet string.
 *
 * Changes in 3.6.0:
 *  - Added instance-level $kit_cache so get_kit_classes() hits
 *    kits_manager only once per type per request instead of on
 *    every element panel render.
 *
 * @package DynamicClassesElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DCE_CSS_Generator {

    /**
     * Per-request cache: type → array of classes.
     * Populated lazily on first access.
     *
     * @var array<string, array>
     */
    private array $kit_cache = [];

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Build the full CSS string for all class types.
     *
     * @return string
     */
    public function generate(): string {
        $css  = "/* Dynamic Classes for Elementor v" . DCE_VERSION . " */\n";
        $css .= $this->generate_gap_css();
        $css .= $this->generate_padding_css();
        $css .= $this->generate_margin_css();
        $css .= $this->generate_min_height_css();
        $css .= $this->generate_max_width_css();

        /**
         * Allow developers to append or modify the generated CSS.
         *
         * @param string $css  The complete generated stylesheet.
         */
        return apply_filters( 'dce_dynamic_css', $css );
    }

    // ── Kit Data Helper ───────────────────────────────────────────────────────

    /**
     * Retrieve a class list from the active Elementor Kit settings.
     * Results are cached per-instance so the kit is queried only once
     * per type per request — even when called for every panel element.
     *
     * @param  string $type  'gap' | 'padding' | 'margin' | 'min_height' | 'max_width'
     * @return array
     */
    public function get_kit_classes( string $type ): array {
        if ( isset( $this->kit_cache[ $type ] ) ) {
            return $this->kit_cache[ $type ];
        }

        $classes = [];

        try {
            if ( class_exists( '\Elementor\Plugin' ) ) {
                $kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
                if ( $kit ) {
                    $raw     = $kit->get_settings( 'dce_' . $type . '_classes' );
                    $classes = is_array( $raw ) ? $raw : [];
                }
            }
        } catch ( \Exception $e ) {
            error_log( 'DCE CSS Generator error: ' . $e->getMessage() );
        }

        $this->kit_cache[ $type ] = $classes;
        return $classes;
    }

    /**
     * Flush the instance cache.
     * Call this if kit settings are updated mid-request (e.g. after sync).
     */
    public function flush_cache(): void {
        $this->kit_cache = [];
    }

    // ── CSS Builders ─────────────────────────────────────────────────────────

    private function generate_gap_css(): string {
        $css = '';

        foreach ( $this->get_kit_classes( 'gap' ) as $class ) {
            if ( empty( $class['name'] ) ) {
                continue;
            }

            $name = sanitize_html_class( $class['name'] );
            if ( empty( $name ) ) {
                continue;
            }

            $row = $this->validate( $class['row_gap']    ?? '' );
            $col = $this->validate( $class['column_gap'] ?? '' );
            if ( $row === false || $col === false ) {
                continue;
            }

            if ( $row === $col ) {
                $css .= ".e-con-boxed.{$name} > .e-con-inner { gap: {$row} !important; }\n";
                $css .= ".e-con-full.{$name}, .e-con.{$name}.e-child { --gap: {$row} !important; --row-gap: {$row} !important; --column-gap: {$row} !important; }\n";
                $css .= ".elementor-section.{$name} > .elementor-container > .elementor-row,\n";
                $css .= ".elementor-column.{$name} > .elementor-widget-wrap { gap: {$row} !important; }\n\n";
            } else {
                $css .= ".e-con-boxed.{$name} > .e-con-inner { row-gap: {$row} !important; column-gap: {$col} !important; }\n";
                $css .= ".e-con-full.{$name}, .e-con.{$name}.e-child { --row-gap: {$row} !important; --column-gap: {$col} !important; }\n";
                $css .= ".elementor-section.{$name} > .elementor-container > .elementor-row,\n";
                $css .= ".elementor-column.{$name} > .elementor-widget-wrap { row-gap: {$row} !important; column-gap: {$col} !important; }\n\n";
            }
        }

        return $css;
    }

    private function generate_padding_css(): string {
        $css = '';

        foreach ( $this->get_kit_classes( 'padding' ) as $class ) {
            if ( empty( $class['name'] ) ) {
                continue;
            }

            $name = sanitize_html_class( $class['name'] );
            if ( empty( $name ) ) {
                continue;
            }

            $t = $this->validate( $class['top']    ?? '0' );
            $r = $this->validate( $class['right']  ?? '0' );
            $b = $this->validate( $class['bottom'] ?? '0' );
            $l = $this->validate( $class['left']   ?? '0' );
            if ( $t === false || $r === false || $b === false || $l === false ) {
                continue;
            }

            // Boxed containers → target inner wrapper
            $css .= ".e-con-boxed.{$name} > .e-con-inner {\n";
            $css .= "    padding-top: {$t} !important; padding-right: {$r} !important;\n";
            $css .= "    padding-bottom: {$b} !important; padding-left: {$l} !important;\n}\n\n";

            // Full-width / child containers → CSS custom properties
            $css .= ".e-con-full.{$name}, .e-con.{$name}.e-child {\n";
            $css .= "    --padding-top: {$t} !important; --padding-right: {$r} !important;\n";
            $css .= "    --padding-bottom: {$b} !important; --padding-left: {$l} !important;\n}\n\n";

            // Legacy sections
            $css .= ".elementor-section.{$name} > .elementor-container {\n";
            $css .= "    padding-top: {$t} !important; padding-right: {$r} !important;\n";
            $css .= "    padding-bottom: {$b} !important; padding-left: {$l} !important;\n}\n\n";

            // Legacy columns
            $css .= ".elementor-column.{$name} > .elementor-widget-wrap {\n";
            $css .= "    padding-top: {$t} !important; padding-right: {$r} !important;\n";
            $css .= "    padding-bottom: {$b} !important; padding-left: {$l} !important;\n}\n\n";

            // Widgets
            $css .= ".elementor-widget.{$name} {\n";
            $css .= "    padding-top: {$t} !important; padding-right: {$r} !important;\n";
            $css .= "    padding-bottom: {$b} !important; padding-left: {$l} !important;\n}\n\n";
        }

        return $css;
    }

    private function generate_margin_css(): string {
        $css = '';

        foreach ( $this->get_kit_classes( 'margin' ) as $class ) {
            if ( empty( $class['name'] ) ) {
                continue;
            }

            $name = sanitize_html_class( $class['name'] );
            if ( empty( $name ) ) {
                continue;
            }

            $t = $this->validate( $class['top']    ?? '0' );
            $r = $this->validate( $class['right']  ?? '0' );
            $b = $this->validate( $class['bottom'] ?? '0' );
            $l = $this->validate( $class['left']   ?? '0' );
            if ( $t === false || $r === false || $b === false || $l === false ) {
                continue;
            }

            // Flexbox/Grid containers → CSS custom properties
            $css .= ".e-con.{$name} {\n";
            $css .= "    --margin-top: {$t} !important; --margin-right: {$r} !important;\n";
            $css .= "    --margin-bottom: {$b} !important; --margin-left: {$l} !important;\n}\n\n";

            // Legacy sections
            $css .= ".elementor-section.{$name} {\n";
            $css .= "    margin-top: {$t} !important; margin-right: {$r} !important;\n";
            $css .= "    margin-bottom: {$b} !important; margin-left: {$l} !important;\n}\n\n";

            // Legacy columns
            $css .= ".elementor-column.{$name} {\n";
            $css .= "    margin-top: {$t} !important; margin-right: {$r} !important;\n";
            $css .= "    margin-bottom: {$b} !important; margin-left: {$l} !important;\n}\n\n";

            // Widgets
            $css .= ".elementor-widget.{$name} {\n";
            $css .= "    margin-top: {$t} !important; margin-right: {$r} !important;\n";
            $css .= "    margin-bottom: {$b} !important; margin-left: {$l} !important;\n}\n\n";
        }

        return $css;
    }

    private function generate_min_height_css(): string {
        $css = '';

        foreach ( $this->get_kit_classes( 'min_height' ) as $class ) {
            if ( empty( $class['name'] ) ) {
                continue;
            }

            $name = sanitize_html_class( $class['name'] );
            if ( empty( $name ) ) {
                continue;
            }

            $val = $this->validate( $class['min_height'] ?? '' );
            if ( $val === false ) {
                continue;
            }

            $css .= ".e-con.{$name} {\n";
            $css .= "    --min-height: {$val} !important;\n";
            $css .= "    min-height: {$val} !important;\n}\n\n";

            $css .= ".elementor-section.{$name}, .elementor-column.{$name} {\n";
            $css .= "    min-height: {$val} !important;\n}\n\n";
        }

        return $css;
    }

    private function generate_max_width_css(): string {
        $css = '';

        foreach ( $this->get_kit_classes( 'max_width' ) as $class ) {
            if ( empty( $class['name'] ) ) {
                continue;
            }

            $name = sanitize_html_class( $class['name'] );
            if ( empty( $name ) ) {
                continue;
            }

            $val = $this->validate( $class['max_width'] ?? '' );
            if ( $val === false ) {
                continue;
            }

            // Boxed containers → target inner content width
            $css .= ".e-con-boxed.{$name} > .e-con-inner {\n";
            $css .= "    --content-width: {$val} !important;\n";
            $css .= "    max-width: {$val} !important;\n}\n\n";

            // Full-width / child containers
            $css .= ".e-con-full.{$name}, .e-con.{$name}.e-child {\n";
            $css .= "    --width: {$val} !important;\n}\n\n";

            // Legacy sections/columns
            $css .= ".elementor-section.{$name}, .elementor-column.{$name} {\n";
            $css .= "    max-width: {$val} !important;\n}\n\n";

            // Widgets
            $css .= ".elementor-widget.{$name} {\n";
            $css .= "    max-width: {$val} !important;\n}\n\n";
        }

        return $css;
    }

    // ── Validator ─────────────────────────────────────────────────────────────

    /**
     * Validate a single CSS value.
     * Allows: 0, standard units, and CSS functions (clamp, calc, min, max, var).
     *
     * @param  string $value
     * @return string|false  Sanitised value, or false if invalid.
     */
    private function validate( string $value ) {
        $value = trim( $value );

        if ( $value === '' ) {
            return false;
        }

        if ( $value === '0' ) {
            return '0';
        }

        // CSS functions: must have balanced parentheses and safe characters only
        if ( preg_match( '/^(calc|clamp|min|max|var)\s*\(/i', $value ) ) {
            $balanced   = substr_count( $value, '(' ) === substr_count( $value, ')' );
            $safe_chars = preg_match( '/^[0-9a-z\s\(\),.\-_+*\/%]+$/i', $value );
            return ( $balanced && $safe_chars ) ? esc_attr( $value ) : false;
        }

        // Standard CSS unit values (including negative)
        if ( preg_match( '/^-?\d+(\.\d+)?(px|em|rem|%|vh|vw|vmin|vmax|ch|ex)?$/i', $value ) ) {
            return esc_attr( $value );
        }

        return false;
    }
}
