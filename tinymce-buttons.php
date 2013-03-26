<?php
// Copyright (c) 2013 Jon Falcon

// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:

// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.

// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.

/**
 * TinyMCE Button Interface
 *
 * This enables User(s) to create their own TinyMCE Button Class.
 *
 * @author   Jon Falcon <darkutubuki143@gmail.com>
 * @version  1.0
 */
interface TinyMCE_Button_Interface {
	function get_id();
	function get_title();
	function get_icon();
	function get_info();
	function get_placement();
	function has_html();
	function get_return_string();
}

/**
 * TinyMCE Button Class
 *
 * A class to create a simple tinymce button that returns a string
 * to the TinyMCE Editor
 *
 * @author   Jon Falcon <darkutubuki143@gmail.com>
 * @version  1.0
 */
class TinyMCE_Button implements TinyMCE_Button_Interface {
	private $id;
	private $settings;

	/**
	 * Populates the object if the button id is set
	 * @param string $id       Button ID
	 * @param array  $settings Button Settings
	 */
	function __construct($id = null, array $settings = array()) {
		if($id) {
			$this->populate($id, $settings);
		}
	}

	/**
	 * Populates this object
	 *
	 * @param  string $id       Button ID
	 * @param  array  $settings Button settings
	 */
	function populate($id = '', array $settings) {
		$this->id       = $id;

		$this->settings['title']             = isset($settings['title']) ? $settings['title'] : '';
		$this->settings['icon']              = isset($settings['icon']) ? $settings['icon'] : '';
		$this->settings['row']               = isset($settings['row']) ? $settings['row'] : 3;
		$this->settings['popup']             = isset($settings['popup']) ? $settings['popup'] : false;
		$this->settings['return']            = isset($settings['return']) ? $settings['return'] : '';
		$this->settings['callback']          = isset($settings['callback']) ? $settings['callback'] : '';
		// plugin info
		$this->settings['info']['longname']  = isset($settings['info']['longname']) ? $settings['info']['longname'] : '';
		$this->settings['info']['author']    = isset($settings['info']['author']) ? $settings['info']['author'] : '';
		$this->settings['info']['authorurl'] = isset($settings['info']['authorurl']) ? $settings['info']['authorurl'] : '';
		$this->settings['info']['version']   = isset($settings['info']['version']) ? $settings['info']['version'] : '';
	}

	/**
	 * Returns the button ID
	 * @return string Button ID
	 */
	function get_id() {
		return $this->id;
	}

	/**
	 * Returns all the settings
	 * @return array Button Settings
	 */
	function get_settings() {
		return $this->settings;
	}

	/**
	 * Returns the button title
	 * @return string Button Title
	 */
	function get_title() {
		return $this->settings['title'];
	}

	/**
	 * Returns the button icon
	 * @return string Button Icon URL
	 */
	function get_icon() {
		return $this->settings['icon'];
	}

	/**
	 * Returns the information
	 * @return array       Button Information
	 */
	function get_info() {
		return $this->settings['info'];
	}

	/**
	 * Returns the button row placement
	 * @return integer Row number
	 */
	function get_placement() {
		return intval($this->settings['row']);
	}

	/**
	 * Has html popup content
	 *
	 * Always return true since we are only making a simple button
	 * 
	 * @return boolean 
	 */
	function has_html() {
		return $this->settings['popup'];
	}

	/**
	 * Returns the string to be added on the TinyMCE Editor
	 * @return string String added to the TinyMCE Editor
	 */
	function get_return_string() {
		if(function_exists($this->settings['callback'])) {
			return call_user_func($this->settings['callback']);
		}
		return $this->settings['return'];
	}
}

/**
 * TinyMCE Buttons Class
 *
 * A singleton class that handles all the required WP Hooks and Callbacks
 * to generate the buttons.
 */
class TinyMCE_Buttons {
	private $buttons = array();
	static $self;

	/**
	 * Constructor
	 *
	 * Restricts instantiation of this class
	 * @access  private
	 */
	private function __construct() {}

	/**
	 * Returns the last instance of this class or create a new instance
	 * if this class hans't been instantiated yet
	 * @return Object TinyMCE_Buttons Class
	 */
	static public function get_instance() {
		if(!isset(self::$self)) {
			self::$self = new self();
		}
		return self::$self;
	}

	/**
	 * Registers the TinyMCE Button or override the existing one
	 * @param TinyMCE_Button_Interface $button TinyMCE Button
	 */
	function add_button(TinyMCE_Button_Interface $button) {
		$button_id                   = $button->get_id();
		$this->buttons[$button_id][] = $button;
	}

	/**
	 * Add the needed WP Hooks and Callbacks
	 */
	function install() {
		/*** register buttons ***/
		add_action('init', array($this, 'initialize_buttons'));
		add_filter('mce_buttons', array($this, 'buttons_on_row_1'));
		add_filter('mce_buttons_2', array($this, 'buttons_on_row_2'));
		add_filter('mce_buttons_3', array($this, 'buttons_on_row_3'));
		add_filter('mce_buttons_4', array($this, 'buttons_on_row_4'));

		/*** AJAX REQUEST ***/
		add_action('wp_ajax_tinymce_view_html', array($this, 'view_html'));
		add_action('wp_ajax_tinymce_button_script', array($this, 'editor_script'));
	}

	/**
	 * Register the buttons
	 * @return [type] [description]
	 */
	function initialize_buttons() {
		if($this->_validate_user()) {
			add_filter('mce_external_plugins', array($this, 'register_script'));
		}
	}

	/**
	 * Register the buttons on row 1
	 * @param  Array $buttons Buttons List
	 * @return Array          Buttons placement
	 */
	function buttons_on_row_1($buttons) {
		return $this->_display_buttons($buttons, 1);
	}

	/**
	 * Register the buttons on row 2
	 * @param  Array $buttons Buttons List
	 * @return Array          Buttons placement
	 */
	function buttons_on_row_2($buttons) {
		return $this->_display_buttons($buttons, 2);
	}

	/**
	 * Register the buttons on row 3
	 * @param  Array $buttons Buttons List
	 * @return Array          Buttons placement
	 */
	function buttons_on_row_3($buttons) {
		return $this->_display_buttons($buttons, 3);
	}

	/**
	 * Register the buttons on row 4
	 * @param  Array $buttons Buttons List
	 * @return Array          Buttons placement
	 */
	function buttons_on_row_4($buttons) {
		return $this->_display_buttons($buttons, 4);
	}

	/**
	 * Register the Button script
	 * @param  array $plugins List of TinyMCE Plugins
	 * @return array          New List of TinyMCE Plugins
	 */
	function register_script($plugins) {
		foreach($this->buttons as $button_group) {
			$button_id           = $button_group[0]->get_id();
			$plugins[$button_id] = admin_url('admin-ajax.php?action=tinymce_button_script&button=' . $button_id);
		}
		return $plugins;
	}

	/**
	 * View HTML
	 */
	function view_html() {
		$plugin = isset($_GET['plugin']) ? $_GET['plugin'] : '';
		$index  = isset($_GET['index']) ? $_GET['index'] : '';
		$button = $this->get_button($plugin, $index);

		if($button){
			$button->get_return_string();
		}
		exit(0);
	}

	/**
	 * TinyMCE Editor Scripts
	 */
	function editor_script() {
		header("Content-type: text/javascript");
		?>
			(function(){
				<?php foreach($this->buttons as $button_group): $parent = $button_group[0]; ?>
					tinymce.create( "tinymce.plugins.<?php echo $parent->get_id(); ?>", {
						getInfo		  : function () {
											return <?php echo $this->_info_to_object($parent->get_info()); ?>;
										},
						<?php if(count($button_group) == 1): ?>
						init		  : function(ed, url){
											ed.addButton( "<?php echo $parent->get_id(); ?>", {
												title	: "<?php echo $parent->get_title(); ?>",
												image	: "<?php echo $parent->get_icon(); ?>",
												onclick	: function(){
													<?php if($parent->has_html()): ?>
														tb_show('<?php echo $parent->get_title(); ?>', '<?php echo admin_url('admin-ajax.php?action=tinymce_view_html&plugin=' . $parent->get_id() . '&index=0'); ?>');
														console.log('<?php echo admin_url('admin-ajax.php?action=tinymce_view_html&plugin=' . $parent->get_id() . '&index=0'); ?>');
													<?php else: ?>
														ed.execCommand( "mceInsertContent", false, '<?php echo $parent->get_return_string(); ?>' );
													<?php endif; ?>
												}
											} );
										},
						<?php endif; ?>
						createControl : function(n, cm) {
											<?php if(count($button_group) > 1): ?>
												if(n == '<?php echo $parent->get_id(); ?>') {
													var c = cm.createSplitButton('<?php echo $parent->get_id(); ?>', {
									                    title : '<?php echo $parent->get_title(); ?>',
									                    image	: "<?php echo $parent->get_icon(); ?>",
									                    onclick : function() {
										                    <?php if($parent->has_html()): ?>
										                    	tb_show('<?php echo $parent->get_title(); ?>', '<?php echo admin_url('admin-ajax.php?action=tinymce_view_html&plugin=' . $parent->get_id() . '&index=0'); ?>');
															<?php else: ?>
									                    		tinyMCE.activeEditor.execCommand('mceInsertContent', false, '<?php echo $parent->get_return_string(); ?>');
									                    	<?php endif; ?>
									                    }
									                });
								                    c.onRenderMenu.add(function(c, m) {
														<?php foreach($button_group as $count => $button): ?>
															m.add({title : '<?php echo $button->get_title(); ?>', onclick : function() {
																<?php if($button->has_html()): ?>
																	tb_show('<?php echo $button->get_title(); ?>', '<?php echo admin_url('admin-ajax.php?action=tinymce_view_html&plugin=' . $button->get_id() . '&index=0'); ?>');
																<?php else: ?>
										                        	tinyMCE.activeEditor.execCommand('mceInsertContent', false, '<?php echo $button->get_return_string(); ?>');
											                    <?php endif; ?>
										                    }});
														<?php endforeach; ?>
									 				});
									 				return c;
												}
											<?php endif; ?>
											return null;
										}
					} );
					tinymce.PluginManager.add( "<?php echo $parent->get_id(); ?>", tinymce.plugins.<?php echo $parent->get_id(); ?> );
				<?php endforeach; ?>
			})();
		<?php
		exit(0);
	}

	/**
	 * Checks if the button exists
	 * @param  string  $id Button ID
	 * @return boolean     True if it exists, otherwise false
	 */
	function has_button($id) {
		foreach($this->buttons as $button_group) {
			foreach($button_group as $button) {
				if($button->get_id() == $id) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Return the button from the buttons list
	 * @param  string $id     Button ID
	 * @param  integer $index Button position in the array
	 * @return Object         Button Object
	 */
	function get_button($id, $index) {
		if(isset($this->buttons[$id][$index])) {
			return $this->buttons[$id][$index];
		}

		return false;
	}

	/**
	 * Encodes button information to json string
	 * @param  array $info Plugin Info
	 * @return string      Encoded plugin info
	 */
	private function _info_to_object($info) {
		$arr   = array("longname", "author", "authorurl", "version");
		$infos = array();
		foreach($arr as $in) {
			if(isset($info[$in])) {
				$infos[$in] = $info[$in];
			} else {
				$infos[$in] = '';
			}
		}

		return json_encode($infos);
	}

	/**
	 * Register buttons to specific row
	 * @param  array $buttons Buttons list
	 * @param  integer $row   Row placement
	 * @return array          Buttons List
	 */
	private function _display_buttons($buttons, $row) {
		foreach($this->buttons as $button_group) {
			if($button_group[0]->get_placement() == $row) {
				array_unshift($buttons, $button_group[0]->get_id());
			}
		}

		return $buttons;
	}

	/**
	 * Validates user and if there is any registered button
	 * @return Boolean True if user can edit and there is at least 1 button registered
	 */
	private function _validate_user() {
		return (current_user_can("edit_posts") && current_user_can("edit_pages") && !empty($this->buttons));
	}
}

/**
 * Add a new button on the TinyMCE Editor
 * @param string $id       Button Id
 * @param array  $settings Settings Array
 */
function add_tinymce_button($id, $settings) {
	TinyMCE_Buttons::get_instance()->add_button(new TinyMCE_Button($id, $settings));
}