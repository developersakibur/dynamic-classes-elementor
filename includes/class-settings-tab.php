<?php
/**
 * Elementor Site Settings Tab
 *
 * Registers the "Dynamic Classes" tab inside
 * Elementor → Site Settings → Settings group.
 *
 * @package DynamicClassesElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DCE_Settings_Tab extends \Elementor\Core\Kits\Documents\Tabs\Tab_Base {

    // ── Identity ──────────────────────────────────────────────────────────────

    public function get_id(): string {
        return 'dynamic-classes';
    }

    public function get_title(): string {
        return esc_html__( 'Dynamic Classes', 'dynamic-classes-elementor' );
    }

    public function get_group(): string {
        return 'settings';
    }

    public function get_icon(): string {
        return 'eicon-code';
    }

    // ── Controls ──────────────────────────────────────────────────────────────

    protected function register_tab_controls(): void {
        $this->register_gap_section();
        $this->register_padding_section();
        $this->register_margin_section();
        $this->register_min_height_section();
        $this->register_max_width_section();
    }

    // ── Gap Section ───────────────────────────────────────────────────────────

    private function register_gap_section(): void {
        $this->start_controls_section(
            'section_gap_classes',
            [
                'label' => esc_html__( 'Gap Classes', 'dynamic-classes-elementor' ),
                'tab'   => $this->get_id(),
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control( 'name', [
            'label'       => esc_html__( 'Class Name', 'dynamic-classes-elementor' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => 'gap-custom-1',
        ] );

        $repeater->add_control( 'row_gap', [
            'label'       => esc_html__( 'Row Gap', 'dynamic-classes-elementor' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => 'clamp(1rem, 3vw, 3rem)',
        ] );

        $repeater->add_control( 'column_gap', [
            'label'       => esc_html__( 'Column Gap', 'dynamic-classes-elementor' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => 'clamp(1rem, 3vw, 3rem)',
        ] );

        $this->add_control( 'dce_gap_classes', [
            'label'       => esc_html__( 'Gap Classes', 'dynamic-classes-elementor' ),
            'type'        => \Elementor\Controls_Manager::REPEATER,
            'fields'      => $repeater->get_controls(),
            'default'     => DCE_Data_Loader::get( 'gap' ),
            'title_field' => '{{{ name }}} — Row: {{{ row_gap }}}, Col: {{{ column_gap }}}',
        ] );

        $this->end_controls_section();
    }

    // ── Padding Section ───────────────────────────────────────────────────────

    private function register_padding_section(): void {
        $this->start_controls_section(
            'section_padding_classes',
            [
                'label' => esc_html__( 'Padding Classes', 'dynamic-classes-elementor' ),
                'tab'   => $this->get_id(),
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control( 'name',   [ 'label' => esc_html__( 'Class Name', 'dynamic-classes-elementor' ), 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'padding-custom-1' ] );
        $repeater->add_control( 'top',    [ 'label' => esc_html__( 'Top',        'dynamic-classes-elementor' ), 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'clamp(1rem, 3vw, 3rem)' ] );
        $repeater->add_control( 'right',  [ 'label' => esc_html__( 'Right',      'dynamic-classes-elementor' ), 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'clamp(1rem, 3vw, 3rem)' ] );
        $repeater->add_control( 'bottom', [ 'label' => esc_html__( 'Bottom',     'dynamic-classes-elementor' ), 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'clamp(1rem, 3vw, 3rem)' ] );
        $repeater->add_control( 'left',   [ 'label' => esc_html__( 'Left',       'dynamic-classes-elementor' ), 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'clamp(1rem, 3vw, 3rem)' ] );

        $this->add_control( 'dce_padding_classes', [
            'label'       => esc_html__( 'Padding Classes', 'dynamic-classes-elementor' ),
            'type'        => \Elementor\Controls_Manager::REPEATER,
            'fields'      => $repeater->get_controls(),
            'default'     => DCE_Data_Loader::get( 'padding' ),
            'title_field' => '{{{ name }}} — {{{ top }}} {{{ right }}} {{{ bottom }}} {{{ left }}}',
        ] );

        $this->end_controls_section();
    }

    // ── Margin Section ────────────────────────────────────────────────────────

    private function register_margin_section(): void {
        $this->start_controls_section(
            'section_margin_classes',
            [
                'label' => esc_html__( 'Margin Classes', 'dynamic-classes-elementor' ),
                'tab'   => $this->get_id(),
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control( 'name',   [ 'label' => esc_html__( 'Class Name', 'dynamic-classes-elementor' ), 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'margin-custom-1' ] );
        $repeater->add_control( 'top',    [ 'label' => esc_html__( 'Top',        'dynamic-classes-elementor' ), 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'clamp(1rem, 3vw, 3rem)' ] );
        $repeater->add_control( 'right',  [ 'label' => esc_html__( 'Right',      'dynamic-classes-elementor' ), 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => '0' ] );
        $repeater->add_control( 'bottom', [ 'label' => esc_html__( 'Bottom',     'dynamic-classes-elementor' ), 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'clamp(1rem, 3vw, 3rem)' ] );
        $repeater->add_control( 'left',   [ 'label' => esc_html__( 'Left',       'dynamic-classes-elementor' ), 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => '0' ] );

        $this->add_control( 'dce_margin_classes', [
            'label'       => esc_html__( 'Margin Classes', 'dynamic-classes-elementor' ),
            'type'        => \Elementor\Controls_Manager::REPEATER,
            'fields'      => $repeater->get_controls(),
            'default'     => DCE_Data_Loader::get( 'margin' ),
            'title_field' => '{{{ name }}} — {{{ top }}} {{{ right }}} {{{ bottom }}} {{{ left }}}',
        ] );

        $this->end_controls_section();
    }

    // ── Min-Height Section ────────────────────────────────────────────────────

    private function register_min_height_section(): void {
        $this->start_controls_section(
            'section_min_height_classes',
            [
                'label' => esc_html__( 'Min-Height Classes', 'dynamic-classes-elementor' ),
                'tab'   => $this->get_id(),
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control( 'name', [
            'label'       => esc_html__( 'Class Name', 'dynamic-classes-elementor' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => 'min-height-custom-1',
        ] );

        $repeater->add_control( 'min_height', [
            'label'       => esc_html__( 'Min Height', 'dynamic-classes-elementor' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => '400px',
        ] );

        $this->add_control( 'dce_min_height_classes', [
            'label'       => esc_html__( 'Min-Height Classes', 'dynamic-classes-elementor' ),
            'type'        => \Elementor\Controls_Manager::REPEATER,
            'fields'      => $repeater->get_controls(),
            'default'     => DCE_Data_Loader::get( 'min_height' ),
            'title_field' => '{{{ name }}} — {{{ min_height }}}',
        ] );

        $this->end_controls_section();
    }

    // ── Max-Width Section ─────────────────────────────────────────────────────

    private function register_max_width_section(): void {
        $this->start_controls_section(
            'section_max_width_classes',
            [
                'label' => esc_html__( 'Max-Width Classes', 'dynamic-classes-elementor' ),
                'tab'   => $this->get_id(),
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control( 'name', [
            'label'       => esc_html__( 'Class Name', 'dynamic-classes-elementor' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => 'max-width-custom-1',
        ] );

        $repeater->add_control( 'max_width', [
            'label'       => esc_html__( 'Max Width', 'dynamic-classes-elementor' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => '800px',
        ] );

        $this->add_control( 'dce_max_width_classes', [
            'label'       => esc_html__( 'Max-Width Classes', 'dynamic-classes-elementor' ),
            'type'        => \Elementor\Controls_Manager::REPEATER,
            'fields'      => $repeater->get_controls(),
            'default'     => DCE_Data_Loader::get( 'max_width' ),
            'title_field' => '{{{ name }}} — {{{ max_width }}}',
        ] );

        $this->end_controls_section();
    }
}
