<?php
/**
 * Randomonials is a plugin for WordPress that manages and displays
 * testimonials in a randomized order.
 * Copyright (C) 2019 by Daniel Resch
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3 of the License.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 * 
 * @link              https://github.com/dresch86
 * @since             1.0.0
 * @package           Randomonials
 *
 * @wordpress-plugin
 * Plugin Name:       Randomonials
 * Plugin URI:        https://github.com/dresch86/wp-randomonials
 * Description:       This plugin generates testimonials in a random order for aesthetic appeal.
 * Version:           1.0.0
 * Author:            Daniel Resch
 * Author URI:        https://github.com/dresch86
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.en.html
 * Text Domain:       randomonials
 * Domain Path:       /languages
 */

if (!defined( 'WPINC' )) {
	die;
}

/**
 * Currently plugin version.
 */
define('RANDOMONIALS_VERSION', '1.0.0');

/**
 * Absolute path to the plugin directory for convenience.
 */
define('RANDOMONIAL_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * Public facing URL to the plugin directory for convenience.
 */
define('RANDOMONIAL_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Absolute path to the data file directory for convenience.
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
 * This action is documented in includes/class-randomonials-activator.php
 */
function activate_randomonials($network_wide) {
	require_once RANDOMONIAL_PLUGIN_PATH . 'includes/class-randomonials-activator.php';
	Randomonials_Activator::activate($network_wide);
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-randomonials-deactivator.php
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