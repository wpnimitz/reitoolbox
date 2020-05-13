<?php
$tabs = array('Branding', 'Forms', 'City Pages', 'Help & Guidelines', 'Reports');
include_once('toolbox-config.php');


// Enqueue the script on the back end (wp-admin)
add_action( 'admin_enqueue_scripts', 'toolbox_admin_scripts_assets' );
function toolbox_admin_scripts_assets() {
	$ver = "1.4.1" . strtotime("now");
	// Add the color picker css file       
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_style('toolbox-css', CITYPRO_URL . '/plugins/css/webnotik.css?version='.$ver);
    //wp_enqueue_script('toolbox-webnotik', get_stylesheet_directory_uri() . '/plugins/js/webnotik.js?version='.$ver);
    wp_enqueue_script( 'wp-color-picker-alpha', CITYPRO_URL . '/plugins/js/wp-color-picker-alpha.min.js', array( 'wp-color-picker' ), $ver, true );
    wp_enqueue_script( 'get-city-pages-script', CITYPRO_URL . '/plugins/js/webnotik-ajax.js?ver='.$ver, array( 'jquery' ), null, true );
    wp_localize_script( 'get-city-pages-script', 'get_city_pages_data', array('ajaxurl' => admin_url( 'admin-ajax.php' )) );
}


add_action('admin_menu', 'toolbox_admin_menu_999');
function toolbox_admin_menu_999() {
	global $tabs;
    add_menu_page( __('City Page Pro', 'citypage-pro'), __('City Page Pro', 'citypage-pro'), 'manage_options', 'toolbox-general', 'show_toolbox_content_callback', 'dashicons-flag', 3);
    add_action( 'admin_init', 'toolbox_settings' );

    for ($i=0; $i < count($tabs); $i++) {
    	$toolbox_content = 'toolbox_' .toolbox_create_slug($tabs[$i], true) .'_callback';
		add_submenu_page('toolbox-general', $tabs[$i], $tabs[$i], 'manage_options', 'toolbox-'.toolbox_create_slug($tabs[$i]), $toolbox_content, $i);
    }
}
function toolbox_settings() {
	global $tabs;
	//for general settings since its not part of the loop
	register_setting( 'toolbox-general-group', 'general' );
	for ($i=0; $i < count($tabs); $i++) {
    	$settings_group = 'toolbox-' .toolbox_create_slug($tabs[$i], true) . '-group';
		register_setting( $settings_group, toolbox_create_slug($tabs[$i], true) );
    }
}


add_action( 'wp_before_admin_bar_render', 'toolbox_admin_bar_render' );
function toolbox_admin_bar_render() {
    global $wp_admin_bar;
    global $tabs;
    // we can remove a menu item, like the Comments link, just by knowing the right $id
    // $wp_admin_bar->remove_menu('comments');

    // lets add our main theme settings option
    $wp_admin_bar->add_menu(
	    array(
	        'id' => 'toolbox-general',
	        'title' => __('City Page Pro'),
	        'href' => admin_url( 'admin.php?page=toolbox-general')
	    )	    
	);
	$wp_admin_bar->add_menu(
		array(
	    	'parent' => 'toolbox-general',
	        'id' => 'general-submenu',
	        'title' => __('General'),
	        'href' => admin_url( 'admin.php?page=toolbox-general')
	    )
	);

	for ($i=0; $i < count($tabs); $i++) {
		$link =  'toolbox-' . toolbox_create_slug($tabs[$i]);
		$wp_admin_bar->add_menu(
			array(
		    	'parent' => 'toolbox-general',
		        'id' => $link . '-submenu',
		        'title' => __($tabs[$i]),
		        'href' => admin_url( 'admin.php?page=' . $link )
		    )
		);
	}
}


function toolbox_create_slug($string, $underscore = false) {
    // Replaces all spaces with hyphens.
	$string = str_replace(' ', '-', $string);
	// Removes special chars.
    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);   
    // Replaces multiple hyphens with single one.
    $string = preg_replace('/-+/', '-', $string);
    // make all characters lower case
    $string = strtolower($string);
    // for callback functions.
    if($underscore) {
		$string = str_replace('-', '_', $string);
	}    
    return $string;
}

function get_toolbox_option($name, $group) {
	global $default;
	$toolbox = get_option($group);

	if(isset($toolbox[$name])) {
		return $toolbox[$name];
	} else {
		if(isset($default[$group][$name])) {
			return $default[$group][$name];
		} else {
			return '';
		}
	}
}

add_action( 'wp_ajax_rename_city_pages', 'rename_city_pages_callback' );
function rename_city_pages_callback() {

	$given_url = $_REQUEST["given_url"];
	$slug = trim(parse_url($given_url, PHP_URL_PATH), '/');
	
	$page = get_page_by_path( $slug );
	$mypost_id = $page->ID;
	$new_title = $_REQUEST["given_title"];

	if($mypost_id > 0) {
		// Let's Update the Post
		$my_post = array(
			'ID'           => $mypost_id,
			'post_title'   => 'We Buy Houses in ' . $new_title,
			'post_name'	   => str_replace(" ", "-", strtolower('We Buy Houses ' . $new_title)) 
		);

		// Update the post into the database
		wp_update_post( $my_post );
		update_post_meta($mypost_id, 'city_keyword', $new_title);

		$success["post_title"] = 'We Buy Houses in ' . $new_title;
		$success["post_name"] = get_the_permalink($mypost_id);
		wp_send_json_success( $success );	

	} else {
		$error["given_title"] = "Title: " . $_REQUEST["given_title"];
		$error["given_url"] = "URL: " . $_REQUEST["given_url"];		
		$error["mypost_id"] = "ID: " . $page->ID;
		wp_send_json_error( $error ); 
	}
}

add_action( 'wp_ajax_clone_city_page', 'clone_city_page_callback' );
function clone_city_page_callback() {

	$given_url = $_REQUEST["given_url"];
	$slug = trim(parse_url($given_url, PHP_URL_PATH), '/');
	
	$page = get_page_by_path( $slug );
	$mypost_id = $page->ID;
	$new_title = $_REQUEST["given_title"];

	if($mypost_id > 0) {
		// Create post object
		$mypost = array(
		  'post_title'    => get_the_title($mypost_id),
		  'post_content'  => get_post_field('post_content', $mypost_id),
		  'post_type'     => 'page',
		  'post_status'   => 'publish',
		  'post_author'   => get_current_user_id()
		);
		 
		// Insert the post into the database
		$new_post_id = wp_insert_post( $mypost );

		// Copy post metadata
		$data = get_post_custom($mypost_id);
	    foreach ( $data as $key => $values) {
	      foreach ($values as $value) {
	        add_post_meta( $new_post_id, $key, $value );
	      }
	    }

	    update_post_meta($new_post_id, 'city_map', '');

		$success["post_title"] = $new_title;
		$success["post_name"] = get_the_permalink($new_post_id);
		wp_send_json_success( $success );	

	} else {
		$error["given_title"] = "Title: " . $_REQUEST["given_title"];
		$error["given_url"] = "URL: " . $_REQUEST["given_url"];		
		$error["mypost_id"] = "ID: " . $page->ID;
		wp_send_json_error( $error ); 
	}
}

//actual ajax
add_action( 'wp_ajax_get_city_pages', 'get_city_pages_callback' );
function get_city_pages_callback() {
    $json = array();


    $query_args = array( 
    	's' => 'we buy houses'
    );
	$query = new WP_Query( $query_args ); 

	$record = 0;
	foreach ($query->posts as $post) {
		$slug = $post->post_name;
		$title = $post->post_title;
	    
	    
	    if( strpos($title, 'We Buy Houses') !== false ) {
	    	$finalize_title = explode("We Buy Houses ", $title);	
	    	$json[$record]["PageName"] = $finalize_title[1];
	    	$json[$record]["PageURL"] = get_the_permalink( $post->ID );
	    	$record++; 
	    }
	}
    wp_send_json_success( $json );
} 

function city_pages_field($name, $action = false, $count = 0, $class = "") {
	$label = $name;
	$name = toolbox_create_slug($name, true);
	$city_pages = get_option('city_pages');

	$city_names = @$city_pages["names"];
	$city_urls = @$city_pages["urls"];

	$value1 = '';
	if(!empty($city_names[$count])) {
		$value1 = $city_names[$count];
	}
	$value2 = '';
	if(!empty($city_urls[$count])) {
		$value2 = $city_urls[$count];
	}

	$city_action = '';
	$url_action = '';
	if($action) {
		$city_action = '<div class="actions inline-action">
			<a title="Rename City Page" class="rename-cp" href="#">Rename</a>
			<a title="Delete this Data" class="delete-cp" href="#">Delete</a>
			<a title="Clone this City Page" class="clone-cp" href="#">Clone</a>
		</div>';
		$url_action = '<div class="actions inline-action">
			<a title="Google Index Verify" class="verify-cp" href="#">Verify</a>
			<a title="View City Page" class="visit-cp" href="#">View</a>
		</div>';
	}



	$ret = '<div class="form-group keyword '.$class.'">
    	<div class="form-label">
    		<label for="'.$name.'">'.$label.'</label> 
    	</div>
    	<div class="form-field">
    		<div class="col-2 k-main">
    			<div class="input-group">
	    			<input type="text" name="city_pages[names][]" id="'.$name.'" value="'.$value1.'" placeholder="Enter City Name">
	    			'.$city_action.'
	    		</div>
	    		<!-- <p class="hint">Enter focus city or state here.</p> -->
	    	</div><div class="col-2 k-value">
	    		<div class="input-group">
	    			<input type="url" name="city_pages[urls][]" id="'.$name.'_url" value="'.$value2.'" placeholder="Enter URL">
	    			'.$url_action.'
	    		</div>
    			<!-- <p class="hint">Enter page URL. Very usefull for automatic linking.</p> -->
	    	</div>
    	</div>
    </div>';

    return $ret;
}

function toolbox_fields($type = 'text', $name, $group = false, $help = false, $options = false, $class = false, $others = false) {
	$label = $name;
	$name = toolbox_create_slug($name, true);
	$ret = '<div class="form-group '.$name.'">';
	$ret .= '<div class="form-label">';
	$ret .= '<label for="'.$name.'">'.$label.'</label>';
	$ret .= '</div>';
	if($help) {
		$help_ret = '';
		foreach ($help as $key => $print) {
			$help_ret .= '<p class="'.$key.'">'.$print.'</p>';
		}
	}
	if($group) {
		$final_name = $group.'['.$name.']';
	}
	if(!$class) {
		$class = '';
	}

	if($others) {
		$data = '';
		foreach ($others as $other => $val) {
			$data .= 'data-' . $other . '="'.$val.'"';
		}
	}

	$value = get_toolbox_option($name, $group);
	$ret .= '<div class="form-field input-group form-'.$class.'">';
	switch ($type) {
		case 'text':
		case 'number':
			$ret .= '<input class="'.$class.'" type="'.$type.'" name="'.$final_name.'" id="'.$name.'" value="'.$value.'" '.(isset($data) ? $data : '').' placeholder="Enter '.$label.'">';
			break;
		case 'select':
			$ret .= '<select class="'.$class.'" name="'.$final_name.'" id="'.$name.'" '.(isset($data) ? $data : '').'>';
			if($options) {
				for ($i=0; $i < count($options); $i++) {
					$option_value = toolbox_create_slug($options[$i], true);
					$is_selected = ($value == $option_value) ? 'selected' : '';
					$ret .= '<option value="'.$option_value.'" '.$is_selected.'>'.$options[$i].'</option>';
				}
			}
			$ret .= '</select>';
			break;
		case 'textarea':
			$ret .= '<textarea name="'.$final_name.'" id="'.$name.'">'.$value.'</textarea>';
			break;
		default:
			# code...
			break;
	}
	$ret .= isset($help_ret) ? $help_ret : '';
	$ret .= '</div>'; //close form-field
	$ret .= '</div>'; //close form-group

	return $ret;
}

add_action( 'et_after_main_content', 'webnotik_global_footer' );
function webnotik_global_footer() {
	if(is_single()) {
		$divi_global = get_option('divi_global');

		echo do_shortcode('<div class="divi-global">[et_pb_section global_module="'.$divi_global["blog_post_after_content"].'"][/et_pb_section]</div>');
	}
    
}


function toolbox_content($body, $tab = 'general') {
	global $tabs;
	$tab_group_name = 'toolbox-' .toolbox_create_slug($tab, true) . '-group';
	?>
	<div class="webnotik-re-wrapper">
		<div class="message"></div>
		<div class="panel">
			<div class="panel-header">
				<h1>Welcome to City Page Pro Settings</h1>
				<p>Speeding the process of templated development website phase</p>
				
			</div>
			<div class="panel-navigation">
				<div class="panel-nav">
					<a class="forms-group <?php echo ($tab == 'general' ? 'active' : '') ?>" href="admin.php?page=toolbox-general">General</a>
					<?php 
					for ($i=0; $i < count($tabs); $i++) {
				    	$toolbox_content = 'toolbox_' .toolbox_create_slug($tabs[$i], true) .'_callback';
						echo '<a class="forms-group ' . ($tab == toolbox_create_slug($tabs[$i]) ? 'active' : 'inactive') . '" href="admin.php?page=toolbox-'.toolbox_create_slug($tabs[$i]).'">'.$tabs[$i].'</a>';
				    }
					?>
					<a href="#" class="icon">&#9776;</a>			
				</div>
			</div>
			<?php settings_errors(); ?>			
			<div class="panel-body">
				<form method="post" action="options.php">
				<?php settings_fields( $tab_group_name ); ?>
				<?php do_settings_sections( $tab_group_name ); ?>
				<?php echo $body; ?>
				</form>
			</div>
		</div>
	</div>
<?php 
} //close toolbox_content

function show_toolbox_content_callback() {

	$ret = '<p>Welcome to general settings. Output any shortcode in any of your wordpress page and we will instantly convert any data to seo rich snippets.</p>';	
	

	$ret .= toolbox_fields('text', 'Business Name', 'general', array('help' => '[webnotik business="name"]'));
	$ret .= toolbox_fields('text', 'Business Phone Number', 'general', array('help' => '[webnotik business="phone_number"]'));
	$ret .= toolbox_fields('text', 'Business Email Address', 'general', array('help' => '[webnotik business="email_address"]'));
	$ret .= toolbox_fields('text', 'Business Address Line 1', 'general', array('help' => '[webnotik business="address_line_1"]'));
	$ret .= toolbox_fields('text', 'Business Address Line 2', 'general', array('help' => '[webnotik business="address_line_2"]'));
	$ret .= toolbox_fields('text', 'Business Logo URL', 'general', array('help' => '[webnotik business="logo_url"]'));
	$ret .= toolbox_fields('textarea', 'Business Map', 'general', array('help' => '[webnotik business="business_map"]', 'hint' => 'Please use a google map embed code. Make sure to change the width to 100% and height to 300.'));
	$ret .= '<h3>Footer Credits</h3>';
	$ret .= '<p class="hint">[footer_credits]</p>';
	$ret .= toolbox_fields('text', 'Start Year', 'general', array('hint' => 'You can leave this blank. Start year may be needed for some business. It will look like this 2018-' . date("Y")));
	$ret .= toolbox_fields('text', 'Credit Company Name', 'general', array('hint' => 'Defaults: Top Results Consuling; when leave empty'));
	$ret .= toolbox_fields('text', 'Credit Company URL', 'general', array('hint' => 'Defaults: https://topresultsconsulting.com; when leave empty'));

	$ret .= '<h3>Other</h3>';
	$ret .= toolbox_fields('text', 'Business Owner Name', 'general', array('hint' => 'Use for reporting'));
	$ret .= toolbox_fields('text', 'Notification Email', 'general', array('hint' => 'Use for reporting'));
	$ret .= toolbox_fields('text', 'Privacy URL', 'general',  array('help' => '[legal_pages for="privacy"]'));
	$ret .= toolbox_fields('text', 'Terms of Use URL', 'general', array('help' => '[legal_pages for="terms"]'));

	$ret .= get_submit_button();

	$ret .= '<div>
			<p>Other shortcode available</p>
			<p>[webnotik business="address"] -> combine address line 1 and 2 in once complete address. Perfect for the footer.</p>
			<p>[webnotik business="weburl"] -> display complete url of the website. Perfect for legal pages.</p>
			<p>[webnotik business=phone_number text=link] - support for Phone Linking</p>
			<p>[webnotik business=email_address text=link] - support for Email Linking</p>
			<p>[footer_credits]</p>
	
	</div>';
	echo toolbox_content($ret, 'general');
}

function toolbox_branding_callback() {
	$ret .= toolbox_fields('select', 'Allow Use of Branding?', 'branding', array('hint' => 'The branding below will be used only if this field is set to Yes.'), array("No","Yes"));
	$ret = '<p>This section is deprecated. Welcome to your branding settings. Please use this page to easily change for this template.</p>';

	$ret .= toolbox_fields('select', 'Round Corners?', 'branding', false, array("No","Yes"));
	$ret .= toolbox_fields('text', 'Round Corners PX', 'branding', array('hint' => 'add <strong>rounded_corners</strong> to module or row class.'));
	$ret .= toolbox_fields('text', 'Main Branding Color', 'branding', array('hint' => 'Use for the city keyword color in hero section'), false, 'wda_color_picker', array('alpha' => 'true'));
	$ret .= toolbox_fields('text', 'Secondary Branding Color', 'branding', false, false, 'wda_color_picker', array('alpha' => 'true'));
	$ret .= toolbox_fields('text', 'Menu CTA Color', 'branding', array('hint' => 'you must add <strong>cta</strong> class to the menu'), false, 'wda_color_picker', array('alpha' => 'true'));

	$ret .= '<h3>Hero Section</h3>';
	$ret .= toolbox_fields('text', 'Hero Background Image', 'branding', array('hint' => 'add hero-background class to the hero section to use the this function.'));
	$ret .= toolbox_fields('text', 'Hero Background Overlay Color', 'branding', false, false, 'wda_color_picker');

	$ret .= '<h3>Form Design</h3>';
	$ret .= '<p>Make sure to add <strong>form-hero-header</strong> class to any module that you have a form.</p>';
	$ret .= toolbox_fields('text', 'Form Header Background', 'branding', false, false, 'wda_color_picker');
	$ret .= toolbox_fields('select', 'Remove Header Bottom Padding?', 'branding', false, array("No","Yes"));
	$ret .= toolbox_fields('select', 'Form Fields Size', 'branding', false, array("Small","Regular"));
	$ret .= toolbox_fields('text', 'Form Body Background', 'branding', false, false, 'wda_color_picker');
	$ret .= toolbox_fields('text', 'Form Button Background', 'branding', false, false, 'wda_color_picker');
	$ret .= toolbox_fields('text', 'Form Button Background Hover', 'branding', false, false, 'wda_color_picker');
	$ret .= toolbox_fields('select', 'Allow Trust Badge?', 'branding', false, array("No","Yes"));
 

	//#TODO
	/*
		Need to remove this one since special pages have their own settings and mostly matters with the design on the homepage
		Also, their is a new function of Divi (Theme Builder) that defeats the purpose of this functionality.
	*/
	// $ret .= '<h3>Special Pages</h3>';
	// $ret .= '<p>Perfect for Thank You and 404 Pages. Make sure to add <strong>special-page</strong> class to the section class settings.</p>';
	// $ret .= toolbox_fields('text', 'Special Page Background Color', 'branding', false, false, 'wda_color_picker');
	// $ret .= toolbox_fields('text', 'Special Page Button Background Color', 'branding', false, false, 'wda_color_picker');
	// $ret .= toolbox_fields('text', 'Special Page Button Hover Background Color', 'branding', false, false, 'wda_color_picker');

	$ret .= '<p>Don\'t forget to click update styles button to create a new version of the css.</p><br>';
	$ret .= '<div class="options">';	
	$ret .= get_submit_button();
	$ret .= '<p class="submit"><a href="#" id="save-styles" class="button button-primary button-large" >Update Styles</a></p></div>';

	echo toolbox_content($ret, 'branding');

	$branding = get_option('branding');

	echo '<pre>';
	print_r($branding);
	echo '</pre>';
}

function toolbox_forms_callback() {
	$forms = array("Seller Form", "Buyer Form", "Private Lending Form", "Contractor Form", "Realtors Form", "Wholesale Form", "Contact Form", "Extra Form");

	$ret = toolbox_fields('textarea', 'Seller Form', 'forms', array('help' => '[webnotik_form type="seller_form"]', 'hint' => "In some instances, you may use lead source, this will help us gain more advantage for PPC landing pages. For reference, please check our Help & Guidelines section <a href='#'>here.</a>"));
	$ret .= toolbox_fields('textarea', 'Buyer Form', 'forms', array('help' => '[webnotik_form type="buyer_form"]'));
	$ret .= toolbox_fields('textarea', 'Private Lending Form', 'forms', array('help' => '[webnotik_form type="private_lending_form"]'));
	$ret .= toolbox_fields('textarea', 'Contractor Form', 'forms', array('help' => '[webnotik_form type="contractor_form"]'));
	$ret .= toolbox_fields('textarea', 'Realtors Form', 'forms', array('help' => '[webnotik_form type="realtors_form"]'));
	$ret .= toolbox_fields('textarea', 'Wholesale Form', 'forms', array('help' => '[webnotik_form type="wholesale_form"]'));
	$ret .= toolbox_fields('textarea', 'Contact Form', 'forms', array('help' => '[webnotik_form type="contact_form"]'));
	$ret .= toolbox_fields('textarea', 'Extra Form', 'forms', array('help' => '[webnotik_form type="extra_form"]'));
	$ret .= toolbox_fields('textarea', 'Extra Form 2', 'forms', array('help' => '[webnotik_form type="extra_form_2"]'));
	$ret .= toolbox_fields('textarea', 'Extra Form 3', 'forms', array('help' => '[webnotik_form type="extra_form_3"]'));
	$ret .= toolbox_fields('textarea', 'Extra Form 4', 'forms', array('help' => '[webnotik_form type="extra_form_4"]'));
	$ret .= toolbox_fields('textarea', 'Extra Form 5', 'forms', array('help' => '[webnotik_form type="extra_form_5"]'));

	$ret .= get_submit_button();	

	echo toolbox_content($ret, 'forms');
}

function toolbox_city_pages_callback() {
	$city_pages = get_option('city_pages');

	$ret =  '<h2>Welcome to REI Toolbox City Pages Data Builder</h2>';	
	$ret .=  '<p>This is only a data collection for all city pages for our project. To help you ease up with the development, 
	we have functions to help you clone any page and or rename here without going to the pages.</p>';
	
	$ret .= toolbox_fields('text', 'Before Title', 'city_pages');
	$ret .= toolbox_fields('text', 'After Title', 'city_pages');
	$ret .= '<hr>';
	$ret .= city_pages_field('Main Target');
	$ret .= city_pages_field('City #<span>1</span>', true, 1, 'main-sub-keyword');
	$ret .=  '<div class="extra-keywords" id="sortable">';
	$city_count = 2;
	if(is_array($city_pages)) {
		for ($i=2; $i < count($city_pages["names"]); $i++) { 
			$ret .= city_pages_field('City #<span>' . $city_count . '</span>', true, $city_count);
			$city_count++;
		}
	}
	$ret .=  '</div>';

	$ret .=  '<div class="options">';
    $ret .= get_submit_button();
	$ret .=  '<p class="submit"><a href="#" id="submit" class="button button-primary button-large add-sub-keyword">Add new record</a></p>
	    <p class="submit"></p>
	</div>';
	//<a href="#" id="get-cp" class="button button-primary button-large" >List City Pages</a>


	echo toolbox_content($ret, 'city-pages');
}

function toolbox_crm_shortcode_callback() {


	$ret = '<h3>You can create a shortcode for your Realeflow CRM here.</h3>';
	$ret = '<h4>This function is deprecated.</h4>';
	$ret .= toolbox_fields('text', 'Account ID', 'crm-shortcode');
	$ret .= toolbox_fields('text', 'Assign Autoresponder', 'crm-shortcode');
	$ret .= toolbox_fields('text', 'Redirect URL', 'crm-shortcode');
	$ret .= toolbox_fields('select', 'Contact Type', 'crm-shortcode', false, array("Seller","Buyer"));
	$ret .= toolbox_fields('text', 'Button Text', 'crm-shortcode');


	$ret .= get_submit_button();

	echo toolbox_content($ret, 'crm-shortcode');
}


function toolbox_help_guidelines_callback() {
	include("parsedown.php");
	$file_path = CITYPRO_PATH . '/README.md';
	$contents = file_get_contents( $file_path );
	$Parsedown = new Parsedown();
	$ret =  $Parsedown->text($contents);

	echo toolbox_content($ret, 'help-guidelines');
}

function toolbox_reports_callback() {
	$ret = '<h2>Reports for '.get_bloginfo('name').'</h2>';
	$priority_pages = array("home", "we buy", "buyer", "agent", "investor", "contractor");
	/*
	Generating Reports for all pages - Simplified Version
	*/
	$page_arg = array( 'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => '-1');
	$pages = get_posts($page_arg);

	$ret .= '<h3>All Pages</h3>';
	$ret .= '<p style="margin-top: -15px">The seo columns checks for SEO description, SEO image, 
	as well as verify the SEO title and description for each priority page 
	for this project/website. Currently, the setup for priority page is with the following 
	page titles: "home, we buy" and most importantly, any page that uses the city name meta field. </p>';

	
	$ret .= '<table class="widefat fixed reports" cellspacing="0">';

	$ret .= '<thead>';
	$ret .= '<td>Name</td>';
	$ret .= '<td>SEO</td>';
	$ret .= '<td>Action</td>';
	$ret .= '</thead>'; //end of TR

	$counter = 0;
	foreach ($pages as $page) {
		$post_id = $page->ID;
		$post_title = $page->post_title;
		$seo_title = get_post_meta($post_id, '_yoast_wpseo_opengraph-title', true);
		$seo_description = get_post_meta($post_id, '_yoast_wpseo_opengraph-description', true);
		$seo_image = get_post_meta($post_id, '_yoast_wpseo_opengraph-image', true);
		$city_name = get_post_meta($post_id, 'city_name', true);


		$seo_label = '';
		if(empty($seo_description)) {
			$seo_label .= priority_checker($priority_pages,$post_title) ? 'Add a SEO Description <br>' : '';
		}
		if(empty($seo_image)) {
			$seo_label .= priority_checker($priority_pages,$post_title) ? 'Please attach graph image <br>' : '';
		}
		if(!empty($city_name)) {
			$seo_label .= string_verify($seo_title, $city_name) ? '' : 'Missing <strong>'.$city_name.'</strong> on your SEO title <br>';
			$seo_label .= string_verify($seo_description, $city_name) ? '' : 'Missing <strong>'.$city_name.'</strong> on your SEO description <br>';
		}

		if(!empty($seo_label)) {
			$ret .= '<tr>';
			$ret .= '<td>'. $post_title .'</td>';
			$ret .= '<td>'. $seo_label .'</td>';
			$ret .= '<td class="actions"><a href="'. get_the_permalink($post_id) .'" target="_blank">View</a></td>';
			$ret .= '</tr>'; //end of TR
		}

		
		$counter++;
	}
	

	$ret .= '</table>'; //end of TABLE
	$ret .= '<p>Page Total: '. $counter.'</p>';


	$ret .= '<h3>Forms Integration Guide</h3>';	
	$ret .= '<table class="widefat fixed reports forms" cellspacing="0">';

	$ret .= '<thead>';
	$ret .= '<td>Name</td>';
	$ret .= '<td>Description</td>';
	$ret .= '<td>Action</td>';
	$ret .= '</thead>'; //end of TR


	$expected_forms = array('seller', 'buyer', 'agent', 'investor', 'contact');
	$expected_fields = array(
		'seller' => array("Name", "Phone Number", "Email Address", "Form URL", "Lead Source"),
		'buyer' => array("Name", "Phone Number", "Email Address", "Form URL", "Lead Source"),
		'agent' => array("Name", "Phone Number", "Email Address", "Form URL", "Lead Source"),
		'investor' => array("Name", "Phone Number", "Email Address", "Form URL", "Lead Source"),
		'contact' => array("Name", "Phone Number", "Email Address", "Message", "Form URL", "Lead Source")
	);

	$forms = GFAPI::get_forms();
    foreach ( $forms as $form) {

        $ret .= '<tr>';
		
		
		$match = '';
		$client_label = '';
		foreach ($expected_forms as $form_name) {
			if(strpos(strtolower($form["title"]), $form_name) !== false){
				$match = $form_name;
				break;
			}
		}


		$field_label = '';
		$test_label = '';
		if(!empty($match)){
			// displays the types of every field in the form
			$temp_fields = $expected_fields[$match];			
			foreach ( $form['fields'] as $field ) {
				$unset_label = $field->label;
				if(($key = array_search($unset_label, $temp_fields)) !== false ) {
					unset($temp_fields[$key]);
					$test_label .= $field->label . ' / ';
				}
			}
			if(is_array($temp_fields)) {
				$lastfield = end($temp_fields);
				foreach ($temp_fields as $missing_field) {
					$field_label .= $missing_field;

					if($missing_field != $lastfield) {
						$field_label .= ', ';
					}
				}
			}
			// foreach ($temp_fields as $tf) {
			// 	$test_label .= $tf;
			// }
		}

		$client_label .= (!empty($field_label)) ? '<h4>Missing Fields</h4>' . $field_label : '';

		$general = get_option("general");
		if(!empty($general["business_owner_name"])) {
			$client = false;
			$notification_email = $general["notification_email"];
			foreach ($form["notifications"] as $notification) {
				$client	= ($notification["to"] == $notification_email) ? true : false;
				if($client) { break; }
			}
			if(!$client) {
				$client_label .= '<h4>No notification assigned for this client. </h4>';
				$client_label .= '<strong>Notification Name</strong> "Notification for '.$general["business_owner_name"].'"<br>';
				$client_label .= '<strong>Notification Send to Email Address</strong> "'.$notification_email.'"<br>';
			}
		} else {
			$client_label .= '<h4>Notication Needed!</h4>';
			$client_label .= 'Sorry, you need to add both business owner name and notitication email in City Page Pro General Settings';
		}

		$ret .= '<td>'.$form['title']. (!empty($match) ? ' <span class="hot"> hot </span>' : '') .'</td>';
		$ret .= '<td>'.$client_label.'</td>';

		$ret .= '<td class="actions">';
		$ret .= ' <a href="admin.php?page=gf_edit_forms&id='.$form['id'].'">Edit</a> ';
		$ret .= ' <a href="admin.php?page=gf_edit_forms&view=settings&id='.$form['id'].'">Settings</a> ';
		$ret .= ' <a href="admin.php?page=gf_edit_forms&view=settings&subview=notification&id='.$form['id'].'">Notifications</a> ';
		$ret .= '</td>';
		$ret .= '</tr>'; //end of TR
    }

	$ret .= '</table>'; //end of TABLE
	echo toolbox_content($ret, 'reports');


	$to = 'nimitz@webnotik.com';
	$subject = 'The subject';
	$headers = array('Content-Type: text/html; charset=UTF-8', 'From: Alex &lt;alex@brightstarinvestors.com');
	 
	wp_mail( $to, $subject, $ret, $headers );


}

 
function toolbox_old_information_callback() {
	$ret = 'Something awesome is coming here.';
	echo toolbox_content($ret, 'old-information');
}


function priority_checker($items, $active) {
	foreach ($items as $item) {
		if(strpos(strtolower($active), $item) !== false) {
			//we need to make sure that its not on the thank you page
			if(strpos(strtolower($active), 'thank') !== false) {
				//we continue search
			} else {
				return true;
				break;
			}
		}
	}
	return false;
}

function string_verify($words, $needle) {
	// Test if string contains the word 
	if(strpos($words, $needle) !== false){
	    return true;
	} else{
	    return false;
	}
}


//Display admin notices 
function city_page_pro_admin_notice() {
	$general = array("business_name", "business_phone_number", "business_email_address", "business_map", "business_logo_url", "business_owner_name", "notification_email");
	$general_data = get_option('general');


	$ret = '';
	foreach ($general as $key) {
		$value = $general_data[$key];
		$notice = "warning";
		if( empty($value) ) {
			$label = str_replace("_", " ", $key);

			$ret .= '<div class="notice notice-'.$notice.'">';
			$ret .= '<p>Sorry, you don\'t have any informatin for <b>'. ucfirst($label) .'</b></p>';
			$ret .= '</div>';
		}
		
	}
         
	echo $ret;
}
add_action( 'admin_notices', 'city_page_pro_admin_notice' );