<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.primesoftwarenetworks.com
 * @since             1.0.0
 * @package           Randomonials
 *
 * @wordpress-plugin
 * Plugin Name:       Randomonials
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       This plugin generates testimonials in a random order for aesthetic appeal.
 * Version:           1.0.0
 * Author:            Daniel Resch
 * Author URI:        http://www.primesoftwarenetworks.com
 * License:           Apache 2.0
 * License URI:       https://www.apache.org/licenses/LICENSE-2.0.txt
 * Text Domain:       randomonials
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined( 'WPINC' )) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define('RANDOMONIALS_VERSION', '1.0.0');

/**
 * Absolute path to the plugin directory for convenience.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define('RANDOMONIAL_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * Public facing URL to the plugin directory for convenience.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define('RANDOMONIAL_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Absolute path to the data file directory for convenience.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define('RANDOMONIAL_DATA_PATH', RANDOMONIAL_PLUGIN_PATH . 'data/');

function log_randomonial_error() {
	if (WP_DEBUG === true) {
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) );
		} else {
			error_log( $log );
		}
	}
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_randomonials($network_wide) {
	require_once RANDOMONIAL_PLUGIN_PATH . 'includes/class-randomonials-activator.php';
	Randomonials_Activator::activate($network_wide);
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_randomonials() {
	require_once RANDOMONIAL_PLUGIN_PATH . 'includes/class-randomonials-deactivator.php';
	Randomonials_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_randomonials' );
register_deactivation_hook( __FILE__, 'deactivate_randomonials' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require RANDOMONIAL_PLUGIN_PATH . 'includes/class-randomonials.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_randomonials() {
	$plugin = new Randomonials();
	$plugin->run();
}

run_randomonials();