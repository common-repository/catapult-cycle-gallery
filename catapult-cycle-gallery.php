<?php
/*
Plugin Name: Catapult cycle gallery
Plugin URI: http://catapultdesign.co.uk/plugin/cycle-gallery
Description: Simple plug-in to create animated image gallery using Malsup's Cycle jquery plugin. Creates image upload fields on selected page or post types that allow you to upload and manage your images from the post itself rather than from the plug-in settings. You can insert the gallery using a shortcode.
Author: Catapult
Version: 1.3
Author URI: http://catapultdesign.co.uk/
License: GPL2
*/

$options = get_option('catapult_cycle_options');
$catapult_post_type = $options['cycle_post_type'];$wp_content_url = get_option( 'siteurl' ) . '/wp-content';$wp_plugin_url = plugins_url() . '/catapult-cycle-gallery';
// Add jquery and plug-in scripts to selected post type
function catapult_cycle_method() {
	global $catapult_post_type;
	if ( get_post_type (  ) == $catapult_post_type ) {
		global $wp_content_url, $wp_plugin_url;
		//wp_deregister_script('jquery');
		//wp_register_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js', false, '1.7.2');
		wp_enqueue_script('jquery');
		wp_enqueue_script ( 'easing', $wp_plugin_url . '/jquery.easing.1.3.js' );		wp_enqueue_script ( 'cycle', $wp_plugin_url . '/jquery.cycle.all.js' );
		
		wp_register_style( 'catapult-cycle-style', $wp_plugin_url . '/catapult-cycle-style.css' );
		wp_enqueue_style('catapult-cycle-style');
	}
}
add_action('wp_enqueue_scripts', 'catapult_cycle_method');

// Add scripts to admin pages
function catapult_cycle_init() {
	global $catapult_post_type;
	$page = get_post ( $_GET['post'] );
	$mypage = $page->post_title;

	if ( get_post_type ( $_GET['post'] ) == $catapult_post_type ) {
		add_action('admin_print_scripts', 'cat_cycle_admin_scripts');
		add_action('admin_print_styles', 'cat_cycle_admin_styles');
	}
}
add_action ('init', 'catapult_cycle_init' );

// Create meta boxes for selected post types to upload images
function add_catapult_cycle_meta_boxes() {
	global $catapult_post_type;
	add_meta_box('cycle', 'Image upload', 'show_catapult_cycle_meta_box', $catapult_post_type, 'normal', 'default');	
}
add_action('add_meta_boxes', 'add_catapult_cycle_meta_boxes');

function show_catapult_cycle_meta_box($post) {
	global $catapult_post_type;
	$page = get_post ( $_GET['post'] ); 
	$mypage = $page->post_title;	
	//if ( get_post_type ( $_GET['post'] ) == $catapult_post_type ) {
		echo '<input type="hidden" name="catapult_cycle_meta_box_nonce" value="'. wp_create_nonce( $catapult_post_type ). '" />'; ?>
		<table class="form-table" id="catapultGalleryTable">
			<?php $imgcount = 2; ?>
			<tr height="180" class="table-row" id="row1">  
					<td><p><label for="upload_image" style="font-weight:bold;">Image 1</label><br />
						<input id="upload_image_1" type="text" size="80" name="upload_image_1" value="<?php echo get_post_meta($post->ID, 'upload_image_1', true); ?>" /><br />
						<input class="btn_upload_image" id="upload_image_button_1" type="button" value="Upload Image" /></p>
						<label for="title_image" style="font-weight:bold;">Title image 1</label><br />
						<input id="upload_title_1" type="text" size="40" name="upload_title_1" value="<?php echo get_post_meta($post->ID, 'upload_title_1', true); ?>" />
					</td>
				</tr>
			<?php while ( get_post_meta($post->ID, 'upload_image_' . $imgcount, true) ) { ?>
				<tr height="180" class="table-row" id="row<?php echo $imgcount; ?>">  
					<td><p><label for="upload_image" style="font-weight:bold;">Image <?php echo $imgcount; ?></label><br />
						<input id="upload_image_<?php echo $imgcount; ?>" type="text" size="80" name="upload_image_<?php echo $imgcount; ?>" value="<?php echo get_post_meta($post->ID, 'upload_image_' . $imgcount, true); ?>" /><br />
						<input class="btn_upload_image" id="upload_image_button_<?php echo $imgcount; ?>" type="button" value="Upload Image" /></p>
						<label for="title_image" style="font-weight:bold;">Title image <?php echo $imgcount; ?></label><br />
						<input id="upload_title_<?php echo $imgcount; ?>" type="text" size="40" name="upload_title_<?php echo $imgcount; ?>" value="<?php echo get_post_meta($post->ID, 'upload_title_' . $imgcount, true); ?>" />
					</td>
				</tr>
			<?php $imgcount++;
			} ?>
		</table>
		<p style="float:right;"><input class="add-button" id="add_1" type="button" value="Add row" /></p>
	<?php  
	//} else {
	//	echo 'Image upload is not available on this screen';
	//}
}

// Save the upload information
function save_catapult_cycle_meta_box($post_id) {
	global $catapult_post_type;
    // check nonce
    if (!isset($_POST['catapult_cycle_meta_box_nonce']) || !wp_verify_nonce($_POST['catapult_cycle_meta_box_nonce'], $catapult_post_type )) {
        return $post_id;
    }
 
    // check capabilities
    if ( $catapult_post_type == $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
    } elseif (!current_user_can('edit_page', $post_id)) {
        return $post_id;
    }
 
    // exit on autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }
	
	$imgcount = 1;
	while ( isset($_POST['upload_image_' . $imgcount] ) ) {
		update_post_meta($post_id, 'upload_image_' . $imgcount, $_POST['upload_image_' . $imgcount]);
		update_post_meta($post_id, 'upload_title_' . $imgcount, $_POST['upload_title_' . $imgcount]);
		$imgcount++;
	}
}
add_action('save_post', 'save_catapult_cycle_meta_box');

//Based on a script from here: http://www.webmaster-source.com/2010/01/08/using-the-wordpress-uploader-in-your-plugin-or-theme/#comment-11048
function cat_cycle_admin_scripts() {
	global $wp_content_url, $wp_plugin_url;
	$jsurl = $wp_plugin_url . '/upload-script.js';
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
	wp_register_script('upload-script', $jsurl , array('jquery','media-upload','thickbox'));
	wp_enqueue_script('upload-script');
}

function cat_cycle_admin_styles() {
	wp_enqueue_style('thickbox');
}

/********************************
ADMIN BITS
********************************/
add_action('admin_menu', 'catapult_cycle_plugin_menu');

function catapult_cycle_plugin_menu() {
	add_options_page('Cycle Gallery', 'Catapult Cycle Gallery', 'manage_options', 'catapult_cycle', 'catapult_cycle_options_page');
}

function catapult_cycle_options_page() { ?>
	<div class="wrap">
    <h2>Cycle Gallery</h2>
	<form action="options.php" method="post">
		<?php settings_fields('catapult_cycle_options'); ?>
        <?php do_settings_sections('catapult_cycle'); ?>
        <input name="Submit" type="submit" style="margin-top:30px;" value="<?php esc_attr_e('Save Changes'); ?>" />
    </form>
	</div>
<?php }

add_action('admin_init', 'catapult_cycle_admin_init');
function catapult_cycle_admin_init(){
	register_setting( 'catapult_cycle_options', 'catapult_cycle_options', 'catapult_cycle_options_validate' );
	add_settings_section('catapult_cycle_main', 'Main Settings', 'catapult_cycle_section_text', 'catapult_cycle');
	add_settings_field('catapult_cycle_post_type', 'Post type', 'catapult_cycle_setting_string', 'catapult_cycle', 'catapult_cycle_main');
	add_settings_field('catapult_cycle_image_width', 'Gallery width (pixels)', 'catapult_cycle_setting_image_width', 'catapult_cycle', 'catapult_cycle_main');
	add_settings_field('catapult_cycle_image_height', 'Gallery height (pixels)', 'catapult_cycle_setting_image_height', 'catapult_cycle', 'catapult_cycle_main');
	add_settings_field('catapult_cycle_timeout', 'Timeout (ms)', 'catapult_cycle_setting_timeout', 'catapult_cycle', 'catapult_cycle_main');
	add_settings_field('catapult_cycle_speed', 'Speed (ms)', 'catapult_cycle_setting_speed', 'catapult_cycle', 'catapult_cycle_main');
	add_settings_field('catapult_cycle_transition', 'Transition', 'catapult_cycle_setting_transition', 'catapult_cycle', 'catapult_cycle_main');
	add_settings_field('catapult_cycle_pager', 'Pager', 'catapult_cycle_setting_pager', 'catapult_cycle', 'catapult_cycle_main');
}

function catapult_cycle_section_text() { ?>
	<p>Simple plug-in to create animated image gallery using Malsup's Cycle jquery plugin. Creates image upload fields on selected page or post types that allow you to upload and manage your images from the post itself rather than from the plug-in settings. You can insert the gallery using a shortcode.</p>
	<p>Use this screen to select the post type where you will add Cycle galleries and to define some basic animation settings.</p>
	<p>To insert the gallery, use the shortcode <code>[catapult_cycle /]</code>.</p>
<?php }

function catapult_cycle_setting_string() {
	$postarrays = array( 'page', 'post' );
	$post_types=get_post_types( array ( 'public' => true, '_builtin' => false ), 'names' ); 
	foreach ($post_types as $post_type ) {
		$postarrays[] = $post_type;
	}
	$options = get_option('catapult_cycle_options');
	$current_post_type = $options['cycle_post_type'];
	
	echo "<select id='catapult_cycle_post_type' name='catapult_cycle_options[cycle_post_type]'>";
	foreach ($postarrays as $postarray ) {
		if ( $current_post_type == $postarray ) {
			echo "<option selected='selected'>" . $postarray . "</option>";
		} else {
			echo "<option>" . $postarray . "</option>";
		}
	}
	echo "</select>";
}

function catapult_cycle_setting_image_width() {
	$options = get_option('catapult_cycle_options');
	echo "<input id='catapult_cycle_setting_image_width' name='catapult_cycle_options[catapult_cycle_setting_image_width]' size='5' type='text' value='{$options['catapult_cycle_setting_image_width']}' />";
}

function catapult_cycle_setting_image_height() {
	$options = get_option('catapult_cycle_options');
	echo "<input id='catapult_cycle_setting_image_height' name='catapult_cycle_options[catapult_cycle_setting_image_height]' size='5' type='text' value='{$options['catapult_cycle_setting_image_height']}' />";
}

function catapult_cycle_setting_timeout() {
	$options = get_option('catapult_cycle_options');
	echo "<input id='catapult_cycle_setting_timeout' name='catapult_cycle_options[catapult_cycle_setting_timeout]' size='5' type='text' value='{$options['catapult_cycle_setting_timeout']}' />";
}

function catapult_cycle_setting_speed() {
	$options = get_option('catapult_cycle_options');
	echo "<input id='catapult_cycle_setting_speed' name='catapult_cycle_options[catapult_cycle_setting_speed]' size='5' type='text' value='{$options['catapult_cycle_setting_speed']}' />";
}

function catapult_cycle_setting_transition() {
	$postarrays = array( 'scrollHorz', 'fade' );
	$options = get_option('catapult_cycle_options');
	$current_transition = $options['catapult_cycle_transition'];
	
	echo "<select id='catapult_cycle_transition' name='catapult_cycle_options[catapult_cycle_transition]'>";
	foreach ($postarrays as $postarray ) {
		if ( $current_transition == $postarray ) {
			echo "<option selected='selected'>" . $postarray . "</option>";
		} else {
			echo "<option>" . $postarray . "</option>";
		}
	}
	echo "</select>";
}

function catapult_cycle_setting_pager() {
	$pagerarrays = array( 'none', 'before', 'after', 'arrows' );
	$options = get_option('catapult_cycle_options');
	$current_pager = $options['catapult_cycle_pager'];
	
	echo "<select id='catapult_cycle_pager' name='catapult_cycle_options[catapult_cycle_pager]'>";
	foreach ($pagerarrays as $pagerarray ) {
		if ( $current_pager == $pagerarray ) {
			echo "<option selected='selected'>" . $pagerarray . "</option>";
		} else {
			echo "<option>" . $pagerarray . "</option>";
		}
	}
	echo "</select>";
}

function catapult_cycle_options_validate($input) {
	$options = get_option('catapult_cycle_options');
	$options['cycle_post_type'] = trim($input['cycle_post_type']);
	$options['catapult_cycle_transition'] = trim($input['catapult_cycle_transition']);
	$options['catapult_cycle_pager'] = trim($input['catapult_cycle_pager']);
	$options['catapult_cycle_setting_image_width'] = trim($input['catapult_cycle_setting_image_width']);
	$options['catapult_cycle_setting_image_height'] = trim($input['catapult_cycle_setting_image_height']);
	$options['catapult_cycle_setting_timeout'] = trim($input['catapult_cycle_setting_timeout']);
	$options['catapult_cycle_setting_speed'] = trim($input['catapult_cycle_setting_speed']);
	if(!preg_match('/^[a-z0-9]{32}$/i', $options['cycle_post_type'])) {
		//$options['text_string'] = '';
	}
	if(!preg_match('/^[a-z0-9]{32}$/i', $options['catapult_cycle_transition'])) {
		//$options['text_string'] = '';
	}
	if(!preg_match('/^[a-z0-9]{32}$/i', $options['catapult_cycle_pager'])) {
		//$options['text_string'] = '';
	}
	return $options;
}

// Create the shortcode and response text
function catapult_cycle_shortcode ( $atts ) {
	global $post, $wp_plugin_url;
	$options = get_option('catapult_cycle_options');
	$current_transition = $options['catapult_cycle_transition'];
	$speed = $options['catapult_cycle_setting_speed'];
	$timeout = $options['catapult_cycle_setting_timeout'];
	$pager = $options['catapult_cycle_pager'];		$height = $options['catapult_cycle_setting_image_height'];		$width = $options['catapult_cycle_setting_image_width'];		extract ( shortcode_atts ( array (		'width' => $width,		'height' => $height,		'transition' => $current_transition,		'timeout' => $timeout,		'pager' => $pager,		'speed' => $speed	), $atts ) );
	$response = '';	$response .= '<div id="catapult-navigation"><div id="listing-banner">';	$i = 1;	while ( get_post_meta ( $post->ID, 'upload_image_' . $i, true ) ) {		if ( get_post_meta($post->ID, 'upload_image_' . $i, true) ) {			$file = get_post_meta( $post->ID, 'upload_image_' . $i, true );						$response .= '<img style="width:' . $width . 'px;height:' . $height . 'px;" src="' . $file .'" />';		}	$i++;    }    $response .= '</div>';		if ( $pager == 'arrows' ) {		$response .= '<div id="cat-prev-arrow"></div><div id="cat-next-arrow"></div>';	}	if ( get_post_meta( $post->ID, 'upload_title_1', true ) ) { 		$response .= '<div id="catapult-caption-slides">';		for ( $j=1;$j<$i;$j++ ) {			$response .= '<div class="catapult-caption">' . get_post_meta( $post->ID, 'upload_title_' . $j, true ) . '</div>';		}		$response .= '</div>';	}	$response .= '</div><!-- catapult-navigation -->';		/* Added captions	//if ( get_post_meta( $post->ID, 'upload_title_1', true ) ) {		//for ( $j=1;$j<$i;$j++ ) {			//$caption = get_post_meta( $post->ID, 'upload_title_' . $j, true );			//$response .= 'aaa';		//}	 } */				$response .= '<script type="text/javascript">jQuery(document).ready(function() {';		if ( $pager == 'before' ) {			$response .= 'jQuery("#listing-banner").before(\'<div id="cat-cycle-nav">\').cycle({';		} else if ( $pager == 'after' ) {			$response .= 'jQuery("#listing-banner").after(\'<div id="cat-cycle-nav">\').cycle({';		} else {			$response .= 'jQuery("#listing-banner").cycle({';		}		if ( $pager == 'arrows' ) {			$response .= 'next: "#cat-next-arrow",						prev: "#cat-prev-arrow",';		} else if ( $pager == 'before' || $pager == 'after' ) {			$response .= 'pager: "#cat-cycle-nav",';		}		$response .= 'fx: "' . $current_transition . '",				speed: ' . $speed . ',				timeout: ' . $timeout . ',				pause: 1			});		});';				if ( get_post_meta( $post->ID, 'upload_title_1', true ) ) { 			$response .= 'jQuery("#catapult-caption-slides").cycle({';			$response .= 'next: "#cat-next-arrow",						prev: "#cat-prev-arrow",';			$response .= 'fx: "' . $current_transition . '",					speed: ' . $speed . ',					timeout: ' . $timeout . ',					pause: 1				});';		}	$response .= '</script>';		return $response;
}
add_shortcode ( 'catapult_cycle', 'catapult_cycle_shortcode' );