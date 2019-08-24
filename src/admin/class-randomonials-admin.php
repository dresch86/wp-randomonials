<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Randomonials
 * @subpackage Randomonials/public
 * @author     Daniel Resch <dresch@primesoftwarenetworks.com>
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
	private static $supported_ajax_operations = ['get-item', 'add-item', 'create-data-file', 'edit-item', 'delete-item', 'reorder-items'];

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

		$missing_fields = [];
		$invalid_fields = [];
		$dataInbound = json_decode(html_entity_decode(stripslashes($dataStringified)));
		
		foreach (array('core', 'custom') as $root_field) {
			$root_field_children = array_keys(get_object_vars($randomonial_template->fields->{$root_field}));

			foreach ($root_field_children as $root_field_child) {
				if (property_exists($dataInbound, $root_field_child)) {
					$sanitized = trim($dataInbound->{$root_field_child}->value);
					$sanitized = strip_tags($sanitized);
	
					if (strlen($sanitized) > 1) {
						$dataInbound->{$root_field_child}->value = $sanitized;
					}
					else {
						$invalid_fields[] = $root_field_child . ':value';
					}
	
					$template_attributes = &$randomonial_template->fields->{$root_field}->{$root_field_child}->attributes;
	
					if (count($template_attributes) > 0) {
						if (property_exists($dataInbound->{$root_field_child}, 'attributes')) {
							$dataInboundAttrs = [];
	
							foreach ($dataInbound->{$root_field_child}->attributes as $attribute) {
								$attribute_frags = explode('=', $attribute, 2);
	
								if (strlen($dataInboundAttrs[$attribute_frags[0]]) > 1) {
									if (in_array($attribute_frags[0], $template_attributes)) {
										$dataInboundAttrs[$attribute_frags[0]] = striptags(trim($attribute_frags[1]));
									}
									else {
										$invalid_fields[] = $root_field_child . ':' . $attribute_frags[0];
									}
								}
								else {
									$invalid_fields[] = $root_field_child . ':' . $attribute_frags[0];
								}
							}
	
							$missing_attrs = array_diff($template_attributes, array_keys($dataInboundAttrs));
	
							if (count($missing_attrs) > 0) {
								foreach ($missing_attrs as $missing_attr) {
									$missing_fields[] = $root_field_child . ':' . $missing_attr;
								}
							}
						}
						else {
							foreach ($template_attributes as $attribute) {
								$missing_fields[] = $root_field_child . ':' . $attribute;
							}
						}
					}
					else {
						if (count($dataInbound->{$root_field_child}->attributes) > 0) {
							foreach ($dataInbound->{$root_field_child}->attributes as $attribute) {
								$bad_attr_frags = explode('=', $attribute, 2);
								$invalid_fields[] = $root_field_child . ':' . $bad_attr_frags[0];
							}
						}
					}
				}
				else {
					$missing_fields[] = $root_field_child;
				}
			}
		}

		return array('SANITIZED'=>$dataInbound, 'MISSING'=>$missing_fields, 'INVALID'=>$invalid_fields);
	}

	private function get_randomonial($itemId) {
		$json_data_file = RANDOMONIAL_DATA_PATH . 'blog_id_' . get_current_blog_id() . '.json';

		if (file_exists($json_data_file)) {
			// Sets $randomonial_template using NOWDOC
			require_once RANDOMONIAL_PLUGIN_PATH . 'public/partials/randomonial_page.php';
			$randomonial_template = json_decode($randomonial_template);
			$randomonial_template_merged = array_merge(get_object_vars($randomonial_template->fields->core), get_object_vars($randomonial_template->fields->custom));;

			$testimonialJSON = json_decode(file_get_contents($json_data_file));
			$payload = ['TEMPLATE'=>$randomonial_template_merged, 'RANDOMONIAL'=>$testimonialJSON->entries[$itemId]];
			
			return json_encode(array(200, $payload));
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

				if ((count($result['MISSING']) == 0) && (count($result['INVALID']) == 0)) {
					$testimonialDataJSON = json_decode(file_get_contents($json_data_file));
					$testimonialDataJSON->entries[] = $result['SANITIZED'];
					$testimonialDataJSON->entries = array_values($testimonialDataJSON->entries);
				
					if (file_put_contents($json_data_file, json_encode($testimonialDataJSON, JSON_NUMERIC_CHECK)) > 0) {
						return json_encode(array(200, 'OK'));
					}
					else {
						return json_encode(array(500, 'System Write Failed'));
					}
				}
				else {
					return json_encode(array(400, array('MISSING'=>$result['MISSING'], 'INVALID'=>$result['INVALID'])));
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

	private function delete_randomonial($idx) {
		$json_data_file = RANDOMONIAL_DATA_PATH . 'blog_id_' . get_current_blog_id() . '.json';

		if (file_exists($json_data_file)) {
			if (current_user_can('delete_others_posts')) {
				$testimonialJSON = json_decode(file_get_contents($json_data_file));
				unset($testimonialJSON->entries[$idx]); 
				$testimonialJSON->entries = array_values($testimonialJSON->entries);
				
				if (file_put_contents($json_data_file, json_encode($testimonialJSON, JSON_NUMERIC_CHECK)) > 0) {
					return json_encode(array(200, 'OK'));
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

	private function echo_randomonial_html_rows($randomonial_count, $jsonData) {
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

			echo '            <tr data-randomonial-id="' . $idxPos . '">' . "\n";
			echo '                <th><input type="checkbox" name="randomonials_selected[]" value="' . $idxPos . '"></th>' . "\n";
			echo '                <td>' . $author_out . '</td>' . "\n";
			echo '                <td>' . $comment_out . '</td>' . "\n";
			echo '                <td class="randomonial-admin-btn-cell"><button type="button" class="randomonial-admin-btn randomonial-admin-btn-edit"><i class="icofont-gear icofont-lg"></i></button></td>' . "\n";
			echo '                <td class="randomonial-admin-btn-cell"><button type="button" class="randomonial-admin-btn"' . $up_disabled . '><i class="icofont-rounded-up icofont-lg"></i></button></td>' . "\n";
			echo '                <td class="randomonial-admin-btn-cell"><button type="button" class="randomonial-admin-btn"' . $down_disabled . '><i class="icofont-rounded-down icofont-lg"></i></button></td>' . "\n";
			echo '                <td class="randomonial-admin-btn-cell"><button type="button" class="randomonial-admin-btn randomonial-admin-btn-del" data-nonce="' . wp_create_nonce('delete-item') . '" onclick="deleteRandomonial(this);"><i class="icofont-close-circled icofont-lg"></i></button></td>' . "\n";
			echo '            </tr>' . "\n";			
		}
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

	public function display_randomonials_add_form() {
        // Sets $randomonial_template using NOWDOC
        require_once RANDOMONIAL_PLUGIN_PATH . 'public/partials/randomonial_page.php';
		$randomonial_template = json_decode($randomonial_template);
		
		echo '<div id="randomonials_dashboard">' . "\n";
		echo '    <div id="randomonials_admin_header">' . "\n";
		echo '        <h1>Adding New Randomonial...</h1>' . "\n";
		echo '    </div>'  . "\n";
		echo '    <form id="randomonials_add_form" data-nonce="' . wp_create_nonce('add-item') . '" class="randomonials-vbox" method="post">' . "\n";
		echo '        <fieldset id="randomonials_core_inputs">' . "\n";
		echo '            <legend>Core Fields</legend>' . "\n";
		echo '            <div class="randomonials-vbox">' . "\n";
		echo '                <label for="randmonial_author_ipt">Author:</label>' . "\n";
		echo '                <input type="text" id="randmonial_author_ipt" name="author" minlength="1" maxlength="100" placeholder="Author goes here..." required>' . "\n";
		echo '                <label for="randmonial_comment_ipt">Comment:</label>' . "\n";
		echo '                <textarea id="randmonial_comment_ipt" name="comment" rows="10" cols="60" placeholder="Comment goes here..." required></textarea>' . "\n";
		echo '            </div>'  . "\n";
		echo '        </fieldset>' . "\n";

		$custom_fields = array_keys(get_object_vars($randomonial_template->fields->custom));

		if (count($custom_fields) > 0) {
			echo '        <fieldset id="randomonials_custom_inputs">' . "\n";
			echo '            <legend>Custom HTML Fields</legend>' . "\n";

			foreach ($custom_fields as $field) {
				echo '            <div class="randomonials-custom-tag-container">' . "\n";
				echo '                <div class="randomonials-tag-title">' . ucwords($field) . ' Field</div>' . "\n";
				
				$tag_object = &$randomonial_template->fields->custom->{$field};
				$tagAttributeInputs = [];

				foreach ($tag_object->attributes as $attribute) {
					$tagAttributeInputs[] = '<span class="randomonial-attribute-label">' . $attribute . '="</span><button type="button" data-field-param="' . $attribute . '" onclick="set_tag_param(this);">Set</button><span class="randomonial-attribute-label">"</span>';
				}

				if (count($tagAttributeInputs) > 0) {
					$tagAttributeInputs = ' ' . implode(' ', $tagAttributeInputs);
				}
				else {
					$tagAttributeInputs = '';
				}

				if (in_array($tag_object->type, self::$selfClosing)) {
					echo '                <div class="randomonial-tag-html" data-field-name="' . $field . '"><span>' . '&lt;' . $tag_object->type . ' class="' . $tag_object->class . '"</span>' . $tagAttributeInputs . '<span>&gt;</span></div>' . "\n";
				}
				else {
					echo '                <div class="randomonial-tag-html" data-field-name="' . $field . '"><span>&lt;' . $tag_object->type . ' class="' . $tag_object->class . '"</span>' . $tagAttributeInputs . '<span>&gt;</span><button type="button" data-field-param="value" onclick="set_tag_param(this);">Set</button><span>&lt;/' . $tag_object->type . '&gt;</span></div>' . "\n"; 
				}

				echo '            </div>' . "\n";
			}

			echo '        </fieldset>' . "\n";
		}

		echo '        <div id="randomonials_add_controls" class="randomonials-hbox">' . "\n";
		echo '            <button type="submit">Submit</button>' . "\n";
		echo '            <button type="reset" onclick="reset_form();">Reset</button>' . "\n";
		echo '        </div>'  . "\n";
		echo '    </form>' . "\n";
		echo '    <div id="randomonials_submit_result">' . "\n";
		echo '    </div>' . "\n";
		echo '    <div id="randomonials_edit_tag_param">' . "\n";
		echo '        <input type=text id="randomonial_param_input" placeholder="Please enter value here...">' . "\n";
		echo '    </div>' . "\n";
		echo '</div>'  . "\n";

		// Sets $randomonial_data_form using HEREDOC
		require_once RANDOMONIAL_PLUGIN_PATH . 'admin/partials/randomonial-data-form.php';
	}

	public function display_randomonials_table() {
		if (!current_user_can('edit_others_pages')) {
		  wp_die(__('You do not have sufficient permissions to access this page!'));
		}

		$testimonialJSON = RANDOMONIAL_DATA_PATH . 'blog_id_' . get_current_blog_id() . '.json';
		
		echo '<div id="randomonials_dashboard">' . "\n";
		echo '<div id="randomonials_admin_header">' . "\n";
		echo '    <h1>Manage Randomonials</h1>' . "\n";

		if (file_exists($testimonialJSON)) {
			echo '    <a class="page-title-action aria-button-if-js" href="' . menu_page_url('randomonials-add', false) . '">Add New</a>' . "\n";
			echo '</div>' . "\n";

			$testimonialJSON = json_decode(file_get_contents($testimonialJSON));
			$randomonial_count = count($testimonialJSON->entries);

			if ($randomonial_count > 0) {
				echo '    <table id="randomonials_datagrid">' . "\n";
				echo '        <thead>' . "\n";
				echo '            <tr>' . "\n";
				echo '                <th><input type="checkbox" id="randomonials_select_all" onclick="selectAllRandomonials(this);"></th>' . "\n";
				echo '                <th>Author</th>' . "\n";
				echo '                <th>Comment</th>' . "\n";
				echo '                <th>Edit</th>' . "\n";
				echo '                <th colspan="2">Move</th>' . "\n";
				echo '                <th>Delete</th>' . "\n";
				echo '            </tr>' . "\n";
				echo '        </thead>' . "\n";
				echo '        <tbody>' . "\n";
	
				$this->echo_randomonial_html_rows($randomonial_count, $testimonialJSON);
	
				echo '        </tbody>' . "\n";
				echo '    </table>' . "\n";
			}
			else {
				echo '    <div class="randomonials-notice">Your site doesn\'t have any randomonials yet. Use the "Add New" button link above to add your first one!</div>' . "\n";
			}
        }
        else {
			echo '</div>' . "\n";
            echo '    <div class="randomonials-notice">Your site does not have a Randomonials data file! Please deactivate / reactivate the plugin to create one! Deactivating the plugin will <strong>NOT</strong> delete other data files that exist.</div>' . "\n";
		}
		
		echo '</div>' . "\n";
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
					case 'delete-item':
						echo $this->delete_randomonial($_POST['itemId']);
						break;
					case 'delete-multi-items':
						echo $this->delete_randomonial($_POST['itemIds']);
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

		switch ($hook)
		{
			case 'toplevel_page_randomonials-manager':
				$localize_js = ['ajax_url' => admin_url('admin-ajax.php'), 
								'nonce_get_item' => wp_create_nonce('get-item'),
								'nonce_edit_item' => wp_create_nonce('edit-item')];
				wp_enqueue_script($this->plugin_name . '-edit-form-builder', (RANDOMONIAL_PLUGIN_URL . 'admin/js/randomonials-edit-form-builder.js'), array('wp-tinymce'), $this->version, false);
				wp_enqueue_script($this->plugin_name . '-admin-manage', (RANDOMONIAL_PLUGIN_URL . 'admin/js/randomonials-admin-manage.js'), array('jquery', 'jquery-ui-dialog'), $this->version, false);
				wp_localize_script($this->plugin_name . '-admin-manage', 'randomonial_admin_client', $localize_js);
				break;
			case 'randomonials_page_randomonials-add':
				wp_enqueue_script($this->plugin_name . '-admin-add', (RANDOMONIAL_PLUGIN_URL . 'admin/js/randomonials-admin-add.js'), array('jquery', 'jquery-ui-dialog'), $this->version, false);
				wp_localize_script($this->plugin_name . '-admin-add', 'randomonial_admin_client', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('ajax-add')));
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
		add_menu_page('Randomonials Manager', 'Randomonials', 'edit_others_pages', 'randomonials-manager', array($this, 'display_randomonials_table'), 'dashicons-media-code', 5);
		add_submenu_page('randomonials-manager', 'Randomonials Dashboard', 'Manage', 'edit_others_pages', 'randomonials-manager', array($this, 'display_randomonials_table'));
		add_submenu_page('randomonials-manager', 'New Randomonial', 'Add New', 'publish_pages', 'randomonials-add', array($this, 'display_randomonials_add_form'));
	}
}