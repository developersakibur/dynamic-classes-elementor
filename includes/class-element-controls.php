<?php
/**
 * Element Controls
 *
 * Injects a "Dynamic Classes" section into the Advanced tab
 * of Elementor Containers, Sections, and Columns, letting
 * users pick a saved spacing class directly in the editor.
 *
 * @package DynamicClassesElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DCE_Element_Controls {

    /**
     * Shared CSS Generator instance (used for kit data retrieval).
     *
     * @var DCE_CSS_Generator
     */
    private DCE_CSS_Generator $generator;

    public function __construct( DCE_CSS_Generator $generator ) {
        $this->generator = $generator;
    }

    // ── Hooks ─────────────────────────────────────────────────────────────────

    /**
     * Register all element hooks. Called once by the main plugin class.
     */
    public function register_hooks(): void {
        $targets = [
            'elementor/element/container/section_layout/after_section_end',
            'elementor/element/section/section_advanced/after_section_end',
            'elementor/element/column/section_advanced/after_section_end',
            'elementor/element/common/_section_style/after_section_end',
        ];

        foreach ( $targets as $hook ) {
            add_action( $hook, [ $this, 'add_controls' ], 10, 2 );
        }
    }

    // ── Control Registration ──────────────────────────────────────────────────

    /**
     * Add the "Dynamic Classes" section to an element.
     *
     * @param \Elementor\Element_Base $element
     * @param array                   $args
     */
    public function add_controls( $element, $args ): void {
        $type = $element->get_name(); // 'container', 'section', 'column', or widget name

        $gap_opts        = $this->build_options( $this->generator->get_kit_classes( 'gap' ) );
        $padding_opts    = $this->build_options( $this->generator->get_kit_classes( 'padding' ) );
        $margin_opts     = $this->build_options( $this->generator->get_kit_classes( 'margin' ) );
        $min_height_opts = $this->build_options( $this->generator->get_kit_classes( 'min_height' ) );
        $max_width_opts  = $this->build_options( $this->generator->get_kit_classes( 'max_width' ) );

        // Determine if this is a structural element or a widget
        $is_structural = in_array( $type, [ 'container', 'section', 'column' ], true );

        // If it's a widget, we only care about padding, margin, and max-width
        if ( ! $is_structural ) {
            if ( count( $padding_opts ) <= 1 && count( $margin_opts ) <= 1 && count( $max_width_opts ) <= 1 ) {
                return;
            }
        } else {
            // Structural: check all
            if ( count( $gap_opts ) <= 1 && count( $padding_opts ) <= 1 && count( $margin_opts ) <= 1 && count( $min_height_opts ) <= 1 && count( $max_width_opts ) <= 1 ) {
                return;
            }
        }

        $element->start_controls_section(
            'section_dce_classes',
            [
                'label' => esc_html__( 'Dynamic Classes', 'dynamic-classes-elementor' ),
                'tab'   => $is_structural ? \Elementor\Controls_Manager::TAB_ADVANCED : \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        if ( $is_structural && count( $gap_opts ) > 1 ) {
            $element->add_control( 'dce_gap_class', [
                'label'        => esc_html__( 'Gap Class', 'dynamic-classes-elementor' ),
                'type'         => \Elementor\Controls_Manager::SELECT,
                'options'      => $gap_opts,
                'default'      => '',
                'prefix_class' => '',
            ] );
        }

        if ( count( $padding_opts ) > 1 ) {
            $element->add_control( 'dce_padding_class', [
                'label'        => esc_html__( 'Padding Class', 'dynamic-classes-elementor' ),
                'type'         => \Elementor\Controls_Manager::SELECT,
                'options'      => $padding_opts,
                'default'      => '',
                'prefix_class' => '',
            ] );
        }

        if ( count( $margin_opts ) > 1 ) {
            $element->add_control( 'dce_margin_class', [
                'label'        => esc_html__( 'Margin Class', 'dynamic-classes-elementor' ),
                'type'         => \Elementor\Controls_Manager::SELECT,
                'options'      => $margin_opts,
                'default'      => '',
                'prefix_class' => '',
            ] );
        }

        if ( $is_structural && count( $min_height_opts ) > 1 ) {
            $element->add_control( 'dce_min_height_class', [
                'label'        => esc_html__( 'Min-Height Class', 'dynamic-classes-elementor' ),
                'type'         => \Elementor\Controls_Manager::SELECT,
                'options'      => $min_height_opts,
                'default'      => '',
                'prefix_class' => '',
            ] );
        }

        if ( count( $max_width_opts ) > 1 ) {
            $element->add_control( 'dce_max_width_class', [
                'label'        => esc_html__( 'Max-Width Class', 'dynamic-classes-elementor' ),
                'type'         => \Elementor\Controls_Manager::SELECT,
                'options'      => $max_width_opts,
                'default'      => '',
                'prefix_class' => '',
            ] );
        }

        $element->end_controls_section();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Convert a raw kit class list into a SELECT-friendly [ slug => label ] map.
     *
     * @param  array $classes
     * @return array
     */
    private function build_options( array $classes ): array {
        $options = [ '' => esc_html__( 'None', 'dynamic-classes-elementor' ) ];

        foreach ( $classes as $class ) {
            if ( empty( $class['name'] ) ) {
                continue;
            }
            $slug = sanitize_html_class( $class['name'] );
            if ( ! empty( $slug ) ) {
                $options[ $slug ] = esc_html( $class['name'] );
            }
        }

        return $options;
    }
}
