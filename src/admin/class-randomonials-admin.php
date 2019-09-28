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
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Randomonials
 * @subpackage Randomonials/public
 * @author     Daniel Resch <primesoftwarenetworks@gmail.com>
 */
class Randomonials_Admin {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;
	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * AJAX operations this plugin supports.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $supported_ajax_operations    AJAX operations this plugin will handle.
	 */
	private static $supported_ajax_operations = ['get-item', 'add-item', 'edit-item', 'delete-items', 'reorder-items'];

	/**
	 * Self closing tags.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $selfClosing    Tags that should not have a value.
	 */	
	private static $selfClosing = array('area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr');

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	private function validate_randomonial($dataStringified) {
		// Sets $randomonial_template using NOWDOC
		require_once RANDOMONIAL_PLUGIN_PATH . 'public/partials/randomonial_page.php';
		$randomonial_template = json_decode($randomonial_template);

		$dataInbound = json_decode(html_entity_decode(stripslashes($dataStringified)));
		$missing_fields = [];
		$invalid_fields = [];
		$missing_attrs = [];
		$invalid_attrs = [];
		
		foreach (['core', 'custom'] as $root_group) {
			$randomonial_fields = array_keys(get_object_vars($randomonial_template->fields->{$root_group}));

			foreach ($randomonial_fields as $randomonial_field) {
				if (property_exists($dataInbound, $randomonial_field)) {
					$template_type = &$randomonial_template->fields->{$root_group}->{$randomonial_field}->type;
					$template_attributes = &$randomonial_template->fields->{$root_group}->{$randomonial_field}->attributes;
	
					if (!in_array($template_type, self::$selfClosing)) {
						$sanitized = trim($dataInbound->{$randomonial_field}->value);
						$sanitized = strip_tags($sanitized, '<br><p><em><strong><mark>');

						if (strlen($sanitized) > 1) {
							$dataInbound->{$randomonial_field}->value = $sanitized;
						}
						else {
							$invalid_fields[] = $randomonial_field . ':value';
						}
					}
	
					if (count($template_attributes) > 0) {
						if (property_exists($dataInbound->{$randomonial_field}, 'attributes')) {
							$attr_pairs_sanitized = [];
							$attr_keys_validated = [];
	
							foreach ($dataInbound->{$randomonial_field}->attributes as $attribute) {
								$attribute_frags = explode('=', $attribute, 2);

								if (in_array($attribute_frags[0], $template_attributes)) {
									$attribute_frags[1] = trim(strip_tags($attribute_frags[1]));

									if (!empty($attribute_frags[1])) {
										$attr_pairs_sanitized[] = $attribute_frags[0] . '=' . $attribute_frags[1];
										$attr_keys_validated[] = $attribute_frags[0];
									}
									else {
										$invalid_attrs[] = $randomonial_field . ':' . $attribute_frags[0];
									}
								}
								else {
									$invalid_attrs[] = $randomonial_field . ':' . $attribute_frags[0];
								}
							}
	
							$attrs_missing_result = array_diff($template_attributes, $attr_keys_validated);
	
							if (count($attrs_missing_result) == 0) {
								$dataInbound->{$randomonial_field}->attributes = $attr_pairs_sanitized;
							}
							else {
								foreach ($attrs_missing_result as $attr_missing_result) {
									$missing_attrs[] = $randomonial_field . ':' . $attr_missing_result;
								}
							}
						}
						else {
							foreach ($template_attributes as $attribute) {
								$missing_attrs[] = $randomonial_field . ':' . $attribute;
							}
						}
					}
					else {
						if (property_exists($dataInbound->{$randomonial_field}, 'attributes')) {
							foreach ($dataInbound->{$randomonial_field}->attributes as $attribute) {
								$bad_attr_frags = explode('=', $attribute, 2);
								$invalid_attrs[] = $randomonial_field . ':' . $bad_attr_frags[0];
							}
						}
					}
				}
				else {
					$missing_fields[] = $randomonial_field;
				}
			}
		}

		$has_erroneous = !empty($missing_fields) || !empty($missing_attrs) || !empty($invalid_fields) || !empty($invalid_attrs);
		
		if (!$has_erroneous) {
			return $dataInbound;
		}
		else {
			return ['MISSING'=>['FIELDS'=>$missing_fields, 'ATTRIBUTES'=>$missing_attrs], 
					'INVALID'=>['FIELDS'=>$invalid_fields, 'ATTRIBUTES'=>$invalid_attrs]];
		}
	}

	private function reorder_randomonials($itemId, $direction) {
		$direction = (int) $direction;
		$itemId = (int) $itemId;

		if (is_integer($itemId) && is_integer($direction)) {
			$json_data_file = RANDOMONIAL_DATA_PATH . 'blog_id_' . get_current_blog_id() . '.json';

			if (file_exists($json_data_file)) {
				if (current_user_can('edit_posts')) {
					$testimonialDataJSON = json_decode(file_get_contents($json_data_file));
					$newId = $itemId + $direction;

					if ($newId >= 0) {
						if ($newId < count($testimonialDataJSON->entries)) {
							$tempSelected = $testimonialDataJSON->entries[$itemId];
							$testimonialDataJSON->entries[$itemId] = $testimonialDataJSON->entries[$newId];
							$testimonialDataJSON->entries[$newId] = $tempSelected;
		
							if (file_put_contents($json_data_file, json_encode($testimonialDataJSON, JSON_NUMERIC_CHECK)) > 0) {
								return json_encode(array(200, 'OK'));
							} else {
								return json_encode(array(500, 'System Write Failed'));
							}
						} else {
							return json_encode(array(400, 'Randomonial outside of upper limit!'));
						}
					} else {
						return json_encode(array(400, 'Randomonial outside of lower limit!'));
					}
				} else {
					return json_encode(array(403, 'Insufficient Permissions'));
				}
			} else {
				return json_encode(array(500, 'Internal Application Error'));
			}
		} else {
			return json_encode(array(400, 'Invalid randomonial id or movement!'));
		}
	}

	private function get_randomonial($itemId) {
		$json_data_file = RANDOMONIAL_DATA_PATH . 'blog_id_' . get_current_blog_id() . '.json';

		if (file_exists($json_data_file)) {
			$testimonialJSON = json_decode(file_get_contents($json_data_file));
			return json_encode(array(200, $testimonialJSON->entries[$itemId]));
		}
		else {
			return json_encode(array(500, 'Internal Application Error'));
		}
	}

	private function add_randomonial($fieldsSubmittedJSON) {
		$json_data_file = RANDOMONIAL_DATA_PATH . 'blog_id_' . get_current_blog_id() . '.json';

		if (file_exists($json_data_file)) {
			if (current_user_can('publish_posts')) {
				$result = $this->validate_randomonial($fieldsSubmittedJSON);

				if (is_object($result)) {
					$testimonialDataJSON = json_decode(file_get_contents($json_data_file));
					$testimonialDataJSON->entries[] = $result;
					$testimonialDataJSON->entries = array_values($testimonialDataJSON->entries);
				
					if (file_put_contents($json_data_file, json_encode($testimonialDataJSON, JSON_NUMERIC_CHECK)) > 0) {
						return json_encode(array(200, 'OK'));
					}
					else {
						return json_encode(array(500, 'System Write Failed'));
					}
				}
				else {
					return json_encode(array(400, $result));
				}
			}
			else {
				return json_encode(array(403, 'Forbidden'));
			}
		}
		else {
			return json_encode(array(500, 'Internal Application Error'));
		}
	}

	private function edit_randomonial($item_id, $updated_data) {
		$json_data_file = RANDOMONIAL_DATA_PATH . 'blog_id_' . get_current_blog_id() . '.json';

		if (file_exists($json_data_file)) {
			if (current_user_can('edit_posts')) {
				$result = $this->validate_randomonial($updated_data);

				if (is_object($result)) {
					$testimonialDataJSON = json_decode(file_get_contents($json_data_file));
					$testimonialDataJSON->entries[$item_id] = $result;
					$testimonialDataJSON->entries = array_values($testimonialDataJSON->entries);
				
					if (file_put_contents($json_data_file, json_encode($testimonialDataJSON, JSON_NUMERIC_CHECK)) > 0) {
						return json_encode(array(200, 'OK'));
					}
					else {
						return json_encode(array(500, 'System Write Failed'));
					}
				}
				else {
					return json_encode(array(400, $result));
				}
			}
			else {
				return json_encode(array(403, 'Forbidden'));
			}
		}
		else {
			return json_encode(array(500, 'Internal Application Error'));
		}
	}

	private function delete_randomonials($indexes) {
		$json_data_file = RANDOMONIAL_DATA_PATH . 'blog_id_' . get_current_blog_id() . '.json';

		if (file_exists($json_data_file)) {
			if (current_user_can('delete_others_posts')) {
				$testimonialJSON = json_decode(file_get_contents($json_data_file));
				$starting_entry_count = count($testimonialJSON->entries);
				$entries_to_delete = json_decode(html_entity_decode(stripslashes($indexes)));

				foreach ($entries_to_delete as $entry_id) {
					unset($testimonialJSON->entries[$entry_id]);
				}
				 
				$testimonialJSON->entries = array_values($testimonialJSON->entries);
				$final_entry_count = count($testimonialJSON->entries);
				
				if (file_put_contents($json_data_file, json_encode($testimonialJSON, JSON_NUMERIC_CHECK)) > 0) {
					return json_encode(array(200, ($starting_entry_count - $final_entry_count)));
				}
				else {
					return json_encode(array(500, 'System Write Failed'));
				}
			}
			else {
				return json_encode(array(403, 'Forbidden'));
			}
		}
		else {
			return json_encode(array(500, 'Internal Application Error'));
		}
	}

	private function build_randomonials_data_form() {
        // Sets $randomonial_template using NOWDOC
        require_once RANDOMONIAL_PLUGIN_PATH . 'public/partials/randomonial_page.php';
		$randomonial_template = json_decode($randomonial_template);
		
		$custom_fields = array_keys(get_object_vars($randomonial_template->fields->custom));
		$custom_fields_html = '';

		if (count($custom_fields) > 0) {
			$custom_fields_html .= '    <fieldset id="randomonials_custom_fields">' . "\n";
			$custom_fields_html .= '        <legend>Custom HTML Fields</legend>' . "\n";

			foreach ($custom_fields as $field) {
				$custom_fields_html .= '        <div class="randomonials-custom-tag-container">' . "\n";
				$custom_fields_html .= '            <div class="randomonials-tag-title">' . ucwords($field) . ' Field</div>' . "\n";
				
				$tag_object = &$randomonial_template->fields->custom->{$field};
				$tagAttributeInputs = [];

				foreach ($tag_object->attributes as $attribute) {
					$tagAttributeInputs[] = '<span class="randomonial-attribute-label">' . $attribute . '="</span><button type="button" data-field-param="' . $attribute . '">Set</button><span class="randomonial-attribute-label">"</span>';
				}

				if (count($tagAttributeInputs) > 0) {
					$tagAttributeInputs = ' ' . implode(' ', $tagAttributeInputs);
				}
				else {
					$tagAttributeInputs = '';
				}

				if (in_array($tag_object->type, self::$selfClosing)) {
					$custom_fields_html .= '            <div class="randomonial-tag-html" data-field-name="' . $field . '"><span>' . '&lt;' . $tag_object->type . ' class="' . $tag_object->class . '"</span>' . $tagAttributeInputs . '<span>&gt;</span></div>' . "\n";
				}
				else {
					$custom_fields_html .= '            <div class="randomonial-tag-html" data-field-name="' . $field . '"><span>&lt;' . $tag_object->type . ' class="' . $tag_object->class . '"</span>' . $tagAttributeInputs . '<span>&gt;</span><button type="button" data-field-param="value">Set</button><span>&lt;/' . $tag_object->type . '&gt;</span></div>' . "\n"; 
				}

				$custom_fields_html .= '        </div>' . "\n";
			}

			$custom_fields_html .= '    </fieldset>' . "\n";
		}

		// Sets $randomonial_data_form using HEREDOC
		require_once RANDOMONIAL_PLUGIN_PATH . 'admin/partials/randomonial-data-form.php';
		return $randomonial_data_form;
	}

	private function build_randomonial_html_rows($randomonial_count, $jsonData) {
		$html = '';

		foreach ($jsonData->entries as $idxPos => $randomonial) {
			$author_out = ((strlen($randomonial->author->value) > 22) ? (substr($randomonial->author->value, 0, 19) . '...') : $randomonial->author->value);
			$comment_out = ((strlen($randomonial->comment->value) > 35) ? (substr($randomonial->comment->value, 0, 32) . '...') : $randomonial->comment->value);

			if ($idxPos == 0) {
				$up_disabled = ' disabled';
			}
			else {
				$up_disabled = '';
			}

			if ($idxPos == ($randomonial_count-1)) {
				$down_disabled = ' disabled';
			}
			else {
				$down_disabled = '';
			}

			$html .= '<tr data-randomonial-id="' . $idxPos . '">' . "\n";
			$html .= '    <th><input type="checkbox" name="randomonials_selected[]" value="' . $idxPos . '"></th>' . "\n";
			$html .= '    <td>' . $author_out . '</td>' . "\n";
			$html .= '    <td>' . $comment_out . '</td>' . "\n";
			$html .= '    <td class="randomonial-admin-btn-cell"><button type="button" class="randomonial-admin-btn randomonial-admin-btn-edit"><i class="icofont-gear icofont-lg"></i></button></td>' . "\n";
			$html .= '    <td class="randomonial-admin-btn-cell"><button type="button" class="randomonial-admin-btn randomonial-admin-btn-up"' . $up_disabled . '><i class="icofont-rounded-up icofont-lg"></i></button></td>' . "\n";
			$html .= '    <td class="randomonial-admin-btn-cell"><button type="button" class="randomonial-admin-btn randomonial-admin-btn-down"' . $down_disabled . '><i class="icofont-rounded-down icofont-lg"></i></button></td>' . "\n";
			$html .= '    <td class="randomonial-admin-btn-cell"><button type="button" class="randomonial-admin-btn randomonial-admin-btn-del"><i class="icofont-close-circled icofont-lg"></i></button></td>' . "\n";
			$html .= '</tr>' . "\n";			
		}

		return $html;
	}

	public function auto_delete_data_file($old_site) {
		$json_data_file = RANDOMONIAL_DATA_PATH . 'blog_id_' . $old_site->blog_id . '.json';
		$result = unlink($json_data_file);

		if (!$result) {
			log_randomonial_error('Failed to delete randomonial data file [' . $json_data_file . ']');
		}
	}

	public function auto_create_data_file($new_site) {
		$json_data_file = RANDOMONIAL_DATA_PATH . 'blog_id_' . $new_site->blog_id . '.json';
		$result = file_put_contents($json_data_file, '{"entries":[]}');

		if (($result === 0) || ($result === false)) {
			log_randomonial_error('Failed to create randomonial data file [' . $json_data_file . ']');
		}
	}

	public function display_randomonials_controls() {
		if (!current_user_can('edit_others_pages')) {
		  wp_die(__('You do not have sufficient permissions to access this page!'));
		}

		$testimonialJSON = RANDOMONIAL_DATA_PATH . 'blog_id_' . get_current_blog_id() . '.json';

		if (file_exists($testimonialJSON)) {
			$header_button = '<button id="randomonials_add_button" type="button">Add New</button>';
			$testimonialJSON = json_decode(file_get_contents($testimonialJSON));
			$randomonial_count = count($testimonialJSON->entries);

			if ($randomonial_count > 0) {
				$randomonial_controls = $this->build_randomonial_html_rows($randomonial_count, $testimonialJSON);

				// Sets $randomonials_control_grid var using HEREDOC
				require_once RANDOMONIAL_PLUGIN_PATH . 'admin/partials/randomonials-control-grid.php';
				$dashboard_output = &$randomonials_control_grid;
			}
			else {
				$dashboard_output = '<div class="randomonials-notice">Your site doesn\'t have any randomonials yet. Use the "Add New" button link above to add your first one!</div>';
			}
        }
        else {
			$header_button = '';
            $dashboard_output = '<div class="randomonials-notice">Your site does not have a Randomonials data file! Please deactivate / reactivate the plugin to create one! Deactivating the plugin will <strong>NOT</strong> delete other data files that exist.</div>';
		}

		// Sets $randomonials_dashboard var using HEREDOC
		require_once RANDOMONIAL_PLUGIN_PATH . 'admin/partials/randomonials-dashboard.php';
		echo $randomonials_dashboard;		
		echo $this->build_randomonials_data_form();
	}

	/**
	 * Handle AJAX requests for CRUD operations.
	 *
	 * @since    1.0.0
	 */
	public function handle_ajax_req() {
		if (isset($_POST) 
			&& isset($_POST['wp_nonce'])
			&& isset($_POST['operation']) 
			&& in_array($_POST['operation'], self::$supported_ajax_operations)) 
		{
			$nonce_chk = wp_verify_nonce($_POST['wp_nonce'], $_POST['operation']);

			if (($nonce_chk == 1) xor ($nonce_chk == 2)) {
				switch ($_POST['operation']) {
					case 'get-item':
						echo $this->get_randomonial($_POST['itemId']);
						break;
					case 'add-item':
						echo $this->add_randomonial($_POST['fields']);
						break;
					case 'delete-items':
						echo $this->delete_randomonials($_POST['items']);
						break;
					case 'edit-item':
						echo $this->edit_randomonial($_POST['itemId'], $_POST['fields']);
						break;
					case 'reorder-items':
						echo $this->reorder_randomonials($_POST['itemId'], $_POST['direction']);
						break;
					default:
						echo json_encode(array(400, 'Bad Request'));
						break;
				}
			}
			else {
				echo json_encode(array(403, 'Forbidden'));
			}

			wp_die();
		}
		else {
			echo json_encode(array(400, 'Bad Request'));
			wp_die();
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$current_screen = get_current_screen();

		if (strpos($current_screen->base, 'randomonials') !== false) {
			wp_enqueue_style($this->plugin_name . '-admin', (RANDOMONIAL_PLUGIN_URL . 'admin/css/randomonials-admin.css'), array(), $this->version, 'all' );
			wp_enqueue_style($this->plugin_name . '-icofonts', (RANDOMONIAL_PLUGIN_URL . 'admin/css/icofont.min.css'), array(), $this->version, 'all' );
			wp_enqueue_style('wp-jquery-ui-dialog');
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook) {
		switch ($hook)
		{
			case 'plugins_page_randomonials-manager':
				$localize_js = ['ajax_url' => admin_url('admin-ajax.php'),
								'nonce_add_item' => wp_create_nonce('add-item'), 
								'nonce_get_item' => wp_create_nonce('get-item'),
								'nonce_edit_item' => wp_create_nonce('edit-item'),
								'nonce_delete_items' => wp_create_nonce('delete-items'),
								'nonce_reorder_items' => wp_create_nonce('reorder-items')];
				wp_enqueue_script($this->plugin_name . '-admin-controller', (RANDOMONIAL_PLUGIN_URL . 'admin/js/randomonials-admin-controller.js'), array('jquery', 'jquery-ui-dialog', 'wp-tinymce'), $this->version, false);
				wp_localize_script($this->plugin_name . '-admin-controller', 'randomonial_admin_client', $localize_js);
				break;
			default:
				break;
		}
	}

	/**
	 * Create admin menu and submenus.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menus() {
		add_submenu_page('plugins.php', 'Randomonials Manager', 'Randomonials', 'manage_options', 'randomonials-manager', array($this, 'display_randomonials_controls'));
	}
}