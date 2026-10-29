<?php
/**
 * Plugin Name:       Practice Areas & Attorneys
 * Plugin URI:        https://example.com/practice-areas-attorneys
 * Description:       Registers Practice Area and Attorney custom post types with clean admin fields, front-end templates, and shortcodes. A portfolio code sample demonstrating custom WordPress plugin development.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Jon
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       paa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PAA_VERSION', '1.0.0' );
define( 'PAA_FILE', __FILE__ );
define( 'PAA_DIR', plugin_dir_path( __FILE__ ) );
define( 'PAA_URL', plugin_dir_url( __FILE__ ) );

require_once PAA_DIR . 'includes/class-paa-post-types.php';
require_once PAA_DIR . 'includes/class-paa-meta-boxes.php';
require_once PAA_DIR . 'includes/class-paa-shortcodes.php';
require_once PAA_DIR . 'includes/class-paa-templates.php';

/**
 * Runs on plugin activation: registers post types, then flushes rewrite rules
 * so the new archive/single URLs work immediately.
 */
function paa_activate() {
	PAA_Post_Types::register();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'paa_activate' );

/**
 * Runs on plugin deactivation: flushes rewrite rules to drop our URLs cleanly.
 */
function paa_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'paa_deactivate' );

/**
 * Boot the plugin.
 */
function paa_init() {
	PAA_Post_Types::hooks();
	PAA_Meta_Boxes::hooks();
	PAA_Shortcodes::hooks();
	PAA_Templates::hooks();
}
add_action( 'plugins_loaded', 'paa_init' );
