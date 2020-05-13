<?php
/*
   Plugin Name: City Page Pro
   Plugin URI: https://topresultsconsulting.com
   Version: 1.5.7
   Author: Nimitz Batioco
   Author URI: https://wpnimitz.com
   License: GPL2
*/

 /*
	I still need to clean this up but I don't have time.
 */

// include_once("plugins/webnotik.php");
include_once("plugins/toolbox.php"); 
include_once("plugins/shortcode.php");
include_once("plugins/metabox.php");
include_once("includes/comparison.php");
//include_once("includes/crm-realeflow.php");

define( 'CITYPRO_PATH', plugin_dir_path( __FILE__ ) );
define( 'CITYPRO_URL', plugin_dir_url( __FILE__ ) );

add_action( 'wp_enqueue_scripts', 'custom_assets' );
function custom_assets() {
	$ver = "1.0.1" . strtotime("now");
	wp_enqueue_style( 'app-style', CITYPRO_URL . '/assets/css/app-style.css', '', $ver ); // already remove the uncessary css

	$branding = get_option('branding');
	$allow = $branding["allow_use_of_branding"];
	if($allow == "yes") {		
		wp_enqueue_style( 'rei-style', CITYPRO_URL . '/assets/css/rei-style.css', '', $ver ); // already remove the uncessary css
	}
    
}

add_filter( 'body_class', 'webnotik_body_class' );
function webnotik_body_class( $classes ) {
	if(is_page()) {
		$classes[] = 'webnotik-pages';
	} elseif(is_single()) {
		$classes[] = 'webnotik-post';
	} else {
		$classes[] = 'webnotik-otherpage';
	}

	$classes[] = 'round_corners';

    return $classes;
}

//create a new stylesheet each time the child theme is updated
add_action('after_setup_theme', 'create_rei_style');
function create_rei_style() {
	$filename = CITYPRO_PATH . '/assets/css/rei-style.css';
	if (!file_exists($filename)) {
	    //partial code from generate_new_rei_style() just remove the json success
		include_once( CITYPRO_PATH . '/includes/style.php' );
		$myCSS = fopen($filename, "w") or die("Unable to open file!");	
		fwrite($myCSS, $css);
		fclose($myCSS);
	}
}

//an option for the admin to create a new push updated for the style of the child theme.
add_action( 'wp_ajax_generate_new_rei_style', 'generate_new_rei_style' );
function generate_new_rei_style() {
	include_once( CITYPRO_PATH . '/includes/style.php');
	$file = CITYPRO_PATH . '/assets/css/rei-style.css';
	$myCSS = fopen($file, "w") or die("Unable to open file!");	
	$success = "Style successfully updated.";
	fwrite($myCSS, $css);
	fclose($myCSS);
	wp_send_json_success($success);
}


add_filter( 'gform_submit_button', 'form_submit_button', 10, 2 );
function form_submit_button( $button, $form ) {
	$branding = get_option('branding');
	$id = 'gform_submit_button_' . $form['id'];
	$text = $form["button"]["text"];

	if(!empty($branding["form_button_background"])) {
		return '<button type="submit" class="button gform_button" id="'.$id.'"><span>'.$text.'</span></button>';
	} else {
	    return '<div class="et_pb_button_module_wrapper et_pb_button_0_wrapper et_pb_button_alignment_center et_pb_module">
					<button class="et_pb_button et_pb_button_0 et_pb_bg_layout_light" id="'.$id.'">'.$text.'</button>
				</div>';
	}
}

//