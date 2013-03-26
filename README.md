<h2>Wordpress TinyMCE Buttons</h2>

<p>Create TinyMCE buttons and button Groups faster with this library. Simple use the single function <code>add_tinymce_button($id = "button_id", $settings = array());</code> or create your own class.</p>

<h3>Installation</h3>

<ol>
	<li>Include the class in your <code>functions.php</code> (for theme development) or in your loader (for plugin development).</li>
	<li>Add the following code: <code><?php add_tinymce_button($id = "button_id", $settings = array()); ?></code></li>
	<li>To create button groups, simply add another tinymce button with the same <code>$id</code> of the parent.</li>
</ol>

<h3>Settings</h3>

<ul>
	<li><code>title</code> - Button Title</li>
	<li><code>icon</code> - Button Icon URL</li>
	<li><code>row</code> - Button row placement. (Up to the 4th row)</li>
	<li><code>popup</code> - Set this to true if you want to display some settings inside a popup.</li>
	<li><code>return</code> - This is the string returned to the editor when the button is clicked. If popup is set to true, this will return the content of the popup.</li>
	<li><code>callback</code> - If you want a function to handle the return string.</li>
</ul>

<h3>Creating a TinyMCE Button Class</h3>

<p>You can create your own button class if you want to have full control of your button. Here's how you do it</p>

<pre>
	<code>
			class TinyMCE_Sample_Button implements TinyMCE_Button_Interface {
				// return id
				function get_id() {
					return "my_button_id";
				}

				// return title
				function get_title() {
					return "Sample Button";
				}

				// return icon url
				function get_icon() {
					return '/path/to/icon.ext';
				}

				// return plugin info
				function get_info() {
					return array(
							"longname"  => "This is a sample Button",
							"author"    => "Jon Falcon",
							"authorurl" => "",
							"version"   => "1.0"
						);
				}

				// return placement
				function get_placement() {
					return 3;
				}

				// if this button has popup
				function has_html() {
					return true;
				}

				// popup content
				function get_return_string() {
					ob_start();
					echo "your html here";
					return ob_get_clean();
				}
			}
			// add your button to the buttons list
			TinyMCE_Buttons::get_instance()->add_button(new TinyMCE_Sample_Button());
	</code>
</pre>

<h3>TIPS</h3>

<p>If you plan to create a popup, you have to add a javascript to return the string back to the editor. Simple use the function <code>window.send_to_editor("my return string here");</code></p>