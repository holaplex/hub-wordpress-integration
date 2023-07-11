<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://holaplex.com
 * @since             1.0.0
 * @package           Holaplex_Wp
 *
 * @wordpress-plugin
 * Plugin Name:       Holaplex Integration Plugin for WordPress
 * Plugin URI:        http://holaplex.com/holaplex-wp/
 * Description:       This plugin allows you to sync and mint NFTs created on Holaplex directly through your WordPress site.
 * Version:           1.0.13
 * Author:            Holaplex.com
 * Author URI:        http://holaplex.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       holaplex-wp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.0.13' );


define( 'HOLAPLEX_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'HOLAPLEX_NONCE', 'holaplex_ajax_nonce' );
define( 'HOLAPLEX_MY_ACCOUNT_ENDPOINT', 'holaplex_nft' );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-holaplex-wp-activator.php
 */
function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-holaplex-wp-activator.php';
	Holaplex_Wp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-holaplex-wp-deactivator.php
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-holaplex-wp-deactivator.php';
	Holaplex_Wp_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-holaplex-core.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-holaplex-wp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin_name() {

	$plugin = new Holaplex_Wp();
	$plugin->run();

}
run_plugin_name();
