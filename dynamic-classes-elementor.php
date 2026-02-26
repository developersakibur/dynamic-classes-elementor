<?php
/**
 * Plugin Name: Dynamic Classes for Elementor
 * Description: Add dynamic CSS classes (gap, padding, margin) with clamp() support via Elementor Site Settings.
 * Version:     3.3.0
 * Author:      DEVSR
 * Text Domain: dynamic-classes-elementor
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package DynamicClassesElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ── Constants ─────────────────────────────────────────────────────────────────

define( 'DCE_VERSION',    '3.3.0' );
define( 'DCE_PLUGIN_FILE', __FILE__ );
define( 'DCE_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'DCE_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );

// ── Autoload includes ─────────────────────────────────────────────────────────
//
// Load order matters:
//   1. Data Loader  – static utility, no dependencies
//   2. CSS Generator – depends on nothing
//   3. Element Controls – receives CSS Generator via constructor
//   4. Plugin (main class) – wires everything together
//
// Note: class-settings-tab.php is loaded lazily inside DCE_Plugin::register_settings_tab_class()
//       because it extends Tab_Base which only exists after elementor/init fires.

require_once DCE_PLUGIN_DIR . 'includes/class-data-loader.php';
require_once DCE_PLUGIN_DIR . 'includes/class-css-generator.php';
require_once DCE_PLUGIN_DIR . 'includes/class-element-controls.php';
require_once DCE_PLUGIN_DIR . 'includes/class-plugin.php';

// ── Bootstrap ─────────────────────────────────────────────────────────────────

DCE_Plugin::get_instance();
