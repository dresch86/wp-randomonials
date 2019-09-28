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
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Randomonials
 * @subpackage Randomonials/includes
 * @author     Daniel Resch <primesoftwarenetworks@gmail.com>
 */
class Randomonials_Activator {
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate($network_wide) {
		if (is_multisite() && $network_wide) {
			$sites = get_sites();

			foreach ($sites as $site) {
				$json_data_file = RANDOMONIAL_DATA_PATH . 'blog_id_' . $site->blog_id . '.json';

				if (!file_exists($json_data_file)) {
					if (is_writable(RANDOMONIAL_DATA_PATH)) {
						$result = file_put_contents($json_data_file, '{"entries":[]}');

						if (($result === 0) || ($result === false)) {
							log_randomonial_error('Failed to create randomonial data file [' . $json_data_file . ']');
						}
					}
					else {
						log_randomonial_error('Randomonial data directory is not writable. Check the permissions.');
					}
				}
			}
		}
		else {
			$json_data_file = RANDOMONIAL_PLUGIN_PATH . 'data/blog_id_' . get_current_blog_id() . '.json';

			if (!file_exists($json_data_file)) {
				if (is_writable(RANDOMONIAL_DATA_PATH)) {
					$result = file_put_contents($json_data_file, '{"entries":[]}');

					if (($result === 0) || ($result === false)) {
						log_randomonial_error('Failed to create randomonial data file [' . $json_data_file . ']');
					}
				}
				else {
					log_randomonial_error('Randomonial data directory is not writable. Check the permissions.');
				}
			}
		}
	}
}