<?php
$pages = array('Branding', 'Forms', 'City Pages', 'Divi Global', 'Help & Guidelines', 'Report');
include_once('toolbox-config.php');


// Enqueue the script on the back end (wp-admin)
add_action( 'admin_enqueue_scripts', 'toolbox_admin_scripts_assets' );
function toolbox_admin_scripts_assets() {
	$ver = "1.4.1" . strtotime("now");
	// Add the color picker css file       
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_style('toolbox-css', get_stylesheet_directory_uri() . '/plugins/css/webnotik.css?version='.$ver);
    //wp_enqueue_script('toolbox-webnotik', get_stylesheet_directory_uri() . '/plugins/js/webnotik.js?version='.$ver);
    wp_enqueue_script( 'wp-color-picker-alpha', get_stylesheet_directory_uri() . '/plugins/js/wp-color-picker-alpha.min.js', array( 'wp-color-picker' ), $ver, true );
    wp_enqueue_script( 'get-city-pages-script', get_stylesheet_directory_uri() . '/plugins/js/webnotik-ajax.js?ver='.$ver, array( 'jquery' ), null, true );
    wp_localize_script( 'get-city-pages-script', 'get_city_pages_data', array('ajaxurl' => admin_url( 'admin-ajax.php' )) );
}


add_action('admin_menu', 'toolbox_admin_menu_999');
function toolbox_admin_menu_999() {
	global $pages;
    add_menu_page( __('Toolbox Pro', 'toolbox-pro'), __('Toolbox PRO', 'toolbox-pro'), 'manage_options', 'toolbox-general', 'show_toolbox_content_callback', 'dashicons-flag', 3);
    add_action( 'admin_init', 'toolbox_settings' );

    for ($i=0; $i < count($pages); $i++) {
    	$toolbox_content = 'toolbox_' .toolbox_create_slug($pages[$i], true) .'_callback';
		add_submenu_page('toolbox-general', $pages[$i], $pages[$i], 'manage_options', 'toolbox-'.toolbox_create_slug($pages[$i]), $toolbox_content, $i);
    }
}
function toolbox_settings() {
	global $pages;
	//for general settings since its not part of the loop
	register_setting( 'toolbox-general-group', 'general' );
	for ($i=0; $i < count($pages); $i++) {
    	$settings_group = 'toolbox-' .toolbox_create_slug($pages[$i], true) . '-group';
		register_setting( $settings_group, toolbox_create_slug($pages[$i], true) );
    }
}


add_action( 'wp_before_admin_bar_render', 'toolbox_admin_bar_render' );
function toolbox_admin_bar_render() {
    global $wp_admin_bar;
    global $pages;
    // we can remove a menu item, like the Comments link, just by knowing the right $id
    // $wp_admin_bar->remove_menu('comments');

    // lets add our main theme settings option
    $wp_admin_bar->add_menu(
	    array(
	        'id' => 'toolbox-general',
	        'title' => __('Toolbox'),
	        'href' => admin_url( 'admin.php?page=toolbox-general')
	    )	    
	); 

 //  for ($i=0; $i < count($pages); $i++) {
 //  	$link =  'toolbox-' . toolbox_create_slug($pages[$i]);
 //  	$wp_admin_bar->add_menu(
	// 	array(
	//     	'parent' => 'toolbox-general',
	//         'id' => $link . '-submenu',
	//         'title' => __($pages[$i]),
	//         'href' => admin_url( 'admin.php?page=' . $link )
	//     )
	// );
 //  }
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
			'post_name'	   => str_replace(" ", "-", strtolower('We Buy Houses in ' . $new_title)) 
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
			<a title="Rename City Page" class="rename-cp" href="#">RP</a>
			<a title="Delete this City Page Data" class="delete-cp" href="#">DD</a>
			<a title="Clone this City Page Data" class="clone-cp" href="#">CD</a>
		</div>';
		$url_action = '<div class="actions inline-action">
			<a title="Verify URL" class="verify-cp" href="#">Verify</a>
		</div>';
	}



	$ret = '<div class="form-group keyword '.$class.'">
    	<div class="form-label">
    		<label for="'.$name.'">'.$label.'</label> 
    	</div>
    	<div class="form-field">
    		<div class="col-2 k-main">
    			<div class="input-group">
	    			<input type="text" name="city_pages[names][]" id="'.$name.'" value="'.$value1.'">
	    			'.$city_action.'
	    		</div>
	    		<p class="hint">Enter focus city or state here.</p>
	    	</div><div class="col-2 k-value">
	    		<div class="input-group">
	    			<input type="url" name="city_pages[urls][]" id="'.$name.'" value="'.$value2.'">
	    			'.$url_action.'
	    		</div>
    			<p class="hint">Enter page URL. Very usefull for automatic linking.</p>
	    	</div>
    	</div>
    </div>';

    return $ret;
}

function toolbox_fields($type = 'text', $name, $group = false, $help = false, $options = false, $class = false, $others = false) {
	$label = $name;
	$name = toolbox_create_slug($name, true);
	$ret = '<div class="form-group">';
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
	$ret .= '<div class="form-field">';
	switch ($type) {
		case 'text':
		case 'number':
			$ret .= '<input class="'.$class.'" type="'.$type.'" name="'.$final_name.'" id="'.$name.'" value="'.$value.'" '.(isset($data) ? $data : '').'>';
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


function toolbox_content($body, $tab = 'general') {
	global $pages;
	$tab_group_name = 'toolbox-' .toolbox_create_slug($tab, true) . '-group';
	?>
	<div class="webnotik-re-wrapper">
		<div class="message"></div>
		<div class="panel">
			<div class="panel-header">
				<h1>Welcome to REI Toolbox Settings</h1>
				<p>Speeding up the process of website development for Real Estate Investor clients.</p>
				
			</div>
			<div class="panel-navigation">
				<div class="panel-nav">
					<a class="forms-group <?php echo ($tab == 'general' ? 'active' : '') ?>" href="admin.php?page=toolbox-general">General</a>
					<?php 
					for ($i=0; $i < count($pages); $i++) {
				    	$toolbox_content = 'toolbox_' .toolbox_create_slug($pages[$i], true) .'_callback';
						echo '<a class="forms-group ' . ($tab == toolbox_create_slug($pages[$i]) ? 'active' : 'inactive') . '" href="admin.php?page=toolbox-'.toolbox_create_slug($pages[$i]).'">'.$pages[$i].'</a>';
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

	$ret = '<p>Welcome to general settings of Wide Open Homes LLC. Output any shortcode in any of your wordpress page and we will instantly convert any data to seo rich snippets.</p>';	
	
	$ret .= toolbox_fields('text', 'Business Name', 'general', array('help' => '[webnotik business="name"]'));
	$ret .= toolbox_fields('text', 'Business Phone Number', 'general', array('help' => '[webnotik business="phone_number"]'));
	$ret .= toolbox_fields('text', 'Business Email Address', 'general', array('help' => '[webnotik business="email_address"]'));
	$ret .= toolbox_fields('text', 'Business Address Line 1', 'general', array('help' => '[webnotik business="address_line_1"]'));
	$ret .= toolbox_fields('text', 'Business Address Line 2', 'general', array('help' => '[webnotik business="address_line_2"]'));
	$ret .= toolbox_fields('text', 'Business Logo URL', 'general', array('help' => '[webnotik business="logo_url"]'));
	$ret .= toolbox_fields('text', 'Privacy URL', 'general',  array('help' => '[legal_pages for="privacy"]'));
	$ret .= toolbox_fields('text', 'Terms of Use URL', 'general', array('help' => '[legal_pages for="terms"]'));

	$ret .= get_submit_button();
	echo toolbox_content($ret, 'general');
}

function toolbox_branding_callback() {
	$ret = '<p>Welcome to your branding settings. Please use this page to easily change for this template.</p>';	
	
	$ret .= toolbox_fields('select', 'Round Corners?', 'branding', false, array("No","Yes"));
	$ret .= toolbox_fields('text', 'Round Corners PX', 'branding', array('help' => 'add <strong>rounded_corners</strong> to module or row class.'));
	$ret .= toolbox_fields('text', 'Main Branding Color', 'branding', false, false, 'wda_color_picker');
	$ret .= toolbox_fields('text', 'Secondary Branding Color', 'branding', false, false, 'wda_color_picker');

	$ret .= '<h3>Hero Section</h3>';
	$ret .= toolbox_fields('text', 'Hero Background Image', 'branding');
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

	$ret .= '<h3>Special Pages</h3>';
	$ret .= '<p>Perfect for Thank You and 404 Pages. Make sure to add <strong>special-page</strong> class to the section class settings.</p>';
	$ret .= toolbox_fields('text', 'Special Page Background Color', 'branding', false, false, 'wda_color_picker');
	$ret .= toolbox_fields('text', 'Special Page Button Background Color', 'branding', false, false, 'wda_color_picker');
	$ret .= toolbox_fields('text', 'Special Page Button Hover Background Color', 'branding', false, false, 'wda_color_picker');

	$ret .= '<div class="options">';
	$ret .= get_submit_button();

	$ret .= '<p class="submit"><a href="#" id="save-styles" class="button button-primary" >Update Styles</a></p></div>';

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

	$ret .= get_submit_button();	

	echo toolbox_content($ret, 'forms');
}

function toolbox_city_pages_callback() {
	$city_pages = get_option('city_pages');

	$ret =  '<h2>Welcome to REI Toolbox City Pages Data Builder</h2>';	
	$ret .=  '<p>In this section, you can rename, delete and even clone your currrent city page landing page. Just make sure you provide the correct name and correct url to make it work properly.</p>';
	
	$ret .= city_pages_field('Main State');
	$ret .= city_pages_field('City #<span>1</span>', true, 1, 'main-sub-keyword');
	$ret .=  '<div class="extra-keywords" id="sortable">';
	$city_count = 2;
	for ($i=2; $i < count($city_pages["names"]); $i++) { 
		$ret .= city_pages_field('City #<span>' . $city_count . '</span>', true, $city_count);
		$city_count++;
	}
	$ret .=  '</div>';

	$ret .=  '<div class="options">';
    $ret .= get_submit_button();
	$ret .=  '<p class="submit"><a href="#" id="submit" class="button button-primary add-sub-keyword">Add new city page</a></p>
	    <p class="submit"><a href="#" id="get-cp" class="button button-primary" >List City Pages</a></p>
	</div>';


	echo toolbox_content($ret, 'city-pages');
}

function toolbox_divi_global_callback() {

	$ret = '<p>Here\'s the most important part. Very useful for header and footer sections.</p>';

	$ret .= toolbox_fields('text', 'Blog Post - Before Content', 'divi_global', array('help' => 'ADD any divi global layouts ID to the field above. IDs must be separated with commas.'));
	$ret .= toolbox_fields('text', 'Blog Post - After Content', 'divi_global', array('help' => 'ADD any divi global layouts ID to the field above. IDs must be separated with commas.'));

	$ret .= get_submit_button();

	echo toolbox_content($ret, 'divi-global');
}


function toolbox_help_guidelines_callback() {
	$ret = 'Something awesome is coming here.';
	echo toolbox_content($ret, 'help-guidelines');
}

function toolbox_report_callback() {
	$ret = 'Something awesome is coming here.';
	echo toolbox_content($ret, 'report');
}


function toolbox_old_information_callback() {
	$ret = 'Something awesome is coming here.';
	echo toolbox_content($ret, 'old-information');
}