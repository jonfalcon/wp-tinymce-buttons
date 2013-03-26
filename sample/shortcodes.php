<?php
class TinyMCE_Shortcodes_Button implements TinyMCE_Button_Interface {
	private $shortcodes = array();

	public function add($name, $return) {
		$this->shortcodes[$name] = $return;
		return $this;
	}

	public function get_shortcodes() {
		return $this->shortcodes;
	}

	public function install() {
		TinyMCE_Buttons::get_instance()->add_button($this);
		foreach($this->get_shortcodes() as $shortcode => $return_string) {
			$settings['title']  = ucwords(str_replace(array("_", "-"), " ", $shortcode));
			$settings['return'] = $return_string;

			add_tinymce_button($this->get_id(), $settings);
		}
		return $this;
	}

	function get_id() {
		return "shortcode";
	}

	function get_title() {
		return "Shortcodes List";
	}

	function get_icon() {
		return TEMPLATE_IMG_URL . 'shortcodes.png'; 
	}
	function get_info() {
		return array(
				"longname"  => "This are my list of shortcodes",
				"author"    => "Jon Falcon",
				"authorurl" => "",
				"version"   => "1.0"
			);
	}

	function get_placement() {
		return 3;
	}

	function has_html() {
		return true;
	}

	function get_return_string() {
		ob_start();
		?>
			<p><strong>Select a shortcode and click the add button</strong></p>
			<select name="shortcode">
				<?php foreach($this->get_shortcodes() as $shortcode => $return_string): ?>
					<option value="<?php echo $return_string; ?>"><?php echo $shortcode; ?></option>
				<?php endforeach; ?>
			</select>
			<input type="submit" name="submit" id="send_shortcode" value="Submit" />
			<script type="text/javascript">
				(function($){
					$(document).ready(function(){
						$('#send_shortcode').click(function() {
							var shortcode = $('select[name=shortcode]').val();
							window.send_to_editor(shortcode);
							return false;
						});
					});
				})(jQuery);
			</script>
		<?php
		return ob_get_clean();
	}
}

$shortcode_button = new TinyMCE_Shortcodes_Button();
$shortcode_button->add('Clear Floatings', '[clear]')
	->add('Headings style 1', '[heading style=1]My Heading[/heading]')
	->add('Headings style 2', '[heading style=2]My Heading[/heading]')
	->add('Headings style 3', '[heading style=3]My Heading[/heading]')
	->add('Checklist style 1','[checklist style=1]<ul><li>List 1</li><li>List 2</li></ul>[/checklist]')
	->add('Checklist style 2','[checklist style=2]<ul><li>List 1</li><li>List 2</li></ul>[/checklist]')
	->add('Checklist style 3','[checklist style=3]<ul><li>List 1</li><li>List 2</li></ul>[/checklist]')
	->install();