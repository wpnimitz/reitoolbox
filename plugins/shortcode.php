<?php
function webnotik_form_shortcode( $atts ){  
	$atts = shortcode_atts(
		array(
			'type' => 'seller',
			'source' => 'organic',
		), $atts, 'webnotik_form' );
	$type = $atts["type"];
	$source = $atts["source"];

	$allowed_types = array(
		'seller_form', 
		'buyer_form', 
		'private_lending_form', 
		'contractor_form', 
		'realtors_form', 
		'wholesale_form' , 
		'contact_form', 
		'extra_form', 
		'extra_form_2', 
		'extra_form_3', 
		'extra_form_4', 
		'extra_form_5'
	);

	if(in_array($type, $allowed_types)) {
		$forms = get_option('forms');
		$form = $forms[$type];

		$business_name = get_option( 'webnotik_business_name');
		$trust_badge = get_stylesheet_directory_uri() . '/assets/img/trust-badge.jpg';
		$allow_trust_badge = get_option( 'allow_trust_badge');
		if($form != "") {
			$ret = '<div class="gform_wrapper webnotik-'.$type.' webnotik-form">';

			if(!empty($source)) {
				$ret .= str_replace("%source%", $source, do_shortcode($form));
			} else {
				if(empty($source)) {
					$ret .= str_replace("%source%", "organic", do_shortcode($form));
				}
			}
			if($allow_trust_badge == "yes") {
				$ret .= '<img class="aligncenter trust_badge" src="'.$trust_badge.'" alt="'.$business_name.'" />';
			}

			$ret .= '</div>';
		} else {
			$ret = "Form is empty!";
		}
	} else {
		$ret = 'Not allowed types';
	}

	return $ret;

	
}
add_shortcode( 'webnotik_form', 'webnotik_form_shortcode' );

// function webnotik_main_topics($atts) {
// 	$atts = shortcode_atts(
// 		array(
// 			'display' => '4',
// 		), $atts, 'webnotik_main_topics' );
// 	$display = $atts["display"];

// 	$main_topics = get_option( 'webnotik_main_topics');
// 	$topics = explode(",", $main_topics);

// 	$ret = '<ul class="main-topics display-'.$display.'" >';
// 	foreach ($topics as $topic) {
// 		$ret .= '<li>' .$topic. '</li>';
// 	}
// 	$ret .= '</ul>';

// 	return $ret;
// }
// add_shortcode( 'main_topics', 'webnotik_main_topics' );

function webnotik_comparison($atts) {
	global $comparison;
	$ret = '<div class="webnotik-comparison">';
	$ret .= $comparison;
	$ret .= '</div>';
	return $ret;
}
add_shortcode( 'rei_comparison', 'webnotik_comparison' );

function display_current_year($atts) {
	return date("Y");
}
add_shortcode( 'current_year', 'display_current_year' );

function display_copyright($atts) {
	$general = get_option('general');

	$atts = shortcode_atts(
		array(
			'start_year' => (!empty($general["start_year"]) ? $general["start_year"] : date("Y")),
			'credit_company_name' => (!empty($general["credit_company_name"]) ? $general["credit_company_name"] : 'Top Results Consulting'),
			'credit_company_url' => (!empty($general["credit_company_url"]) ? $general["credit_company_url"] : 'https://topresultsconsulting.com')
		), $atts, 'footer_credits' );
	$year = date("Y");
	$start = $atts["start_year"];
	$power_name = $atts["credit_company_name"];
	$power_url = $atts["credit_company_url"];

	if(!empty($start) && $start != $year) {
		$final_year = $start . '-' .$year;
	} else{
		$final_year = $year;
	}

	if(!empty($year)) {
		return 'Copyright &copy;' . $final_year . ' ' . get_bloginfo('name') .'. All rights reserved. Powered and Maintained by <a href="'.$power_url.'" target="_blank">'.$power_name.'</a>';
	}

}
add_shortcode( 'footer_credits', 'display_copyright' );

function display_divi_layout($atts) {
	$atts = shortcode_atts(
		array(
			'id' => '',
		), $atts, 'show_layout' );
	$id = $atts["id"];

	return do_shortcode('[et_pb_section global_module="'.$id.'"][/et_pb_section]');
}
add_shortcode( 'show_layout', 'display_divi_layout' );


function webnotik_business_shortcode( $atts ){  
	$atts = shortcode_atts(
		array(
			'business' => 'weburl',
			'text' => 'LINK',
		), $atts, 'webnotik' );
	$business = $atts["business"];
	$text = $atts["text"];
	$ret = '';

	$allowed_types = array("weburl","name", "phone_number", "email_address", "address", "address_line_1", "address_line_2", "logo_url", "business_map");

	if(!$data = wp_cache_get('wda_' . $business, 'wda_' . $business . '_data_'. $text)) {
		if($business == "weburl") {

			if(!empty($text)) {
				$date = '<a href="'.get_site_url().'">' . $text . '</a>'; 
			}
			wp_cache_add( 'wda_' . $business, $data, 'wda_' . $business . '_data' );
			return $data;
		}

		if(in_array($business, $allowed_types)) {
			$business_data = get_option( 'general' );
			if(!empty($business_data['business_' . $business])) {
				$ret = $business_data['business_' . $business];
			} elseif(!empty($business_data[$business])) {
				$ret = $business_data[$business];
			} else {
				if($business == 'address') {
					$ret = $business_data["business_address_line_1"] . ', ' . $business_data["business_address_line_2"];
				} else {
					$ret = '--';
				}
			}
		}
		$data =  '<span class="info-'.$business.'">'.$ret.'</span>';


		if($text == "link" && $business == "phone_number") {
			$data =  '<span class="info-'.$business.'"><a href="tel:'.$ret.'">'.$ret.'</a></span>';
		}
		if($text == "link" && $business == "email_address") {
			$data =  '<span class="info-'.$business.'"><a href="mailto:'.$ret.'">'.$ret.'</a></span>';
		}

		wp_cache_add( 'wda_' . $business, $data, 'wda_' . $business . '_data_'. $text );
	}
	return $data;		
}
add_shortcode( 'webnotik', 'webnotik_business_shortcode' );


function webnotik_legal_pages( $atts ){  
	$atts = shortcode_atts(
		array(
			'for' => 'privacy',
			'text' => 'Privacy Policy' //supports url as well
		), $atts, 'legal_pages' );
	$for = $atts["for"];
	$text = $atts["text"];

	$allowed_types = array("privacy", "terms");
	if(in_array($for, $allowed_types)) {
		$business_data = get_option( 'general' );
		$url = $business_data['privacy_url'];
		if($for != 'privacy') {$url = $business_data['terms_of_use_url'];}

		if(!empty($text)) {
			return '<a href="'.$url.'">'.$text.'</a>';
		} else {
			return $url;
		}
	}
}
add_shortcode( 'legal_pages', 'webnotik_legal_pages' );

function webnotik_city_pages( $atts ){
	$atts = shortcode_atts(
		array(
			'type' => 'list', //or inline
			'after' => '|',
			'limit' => 0,
			'column' => 2,
		), $atts, 'city_pages' );
	$type = $atts["type"];
	$after = $atts["after"];
	$limit = $atts["limit"];
	$column = $atts["column"];

	$city_pages_data = get_option('city_pages');

	$subpages = $city_pages_data["names"];
	$subid = $city_pages_data["urls"];
	$ret = '';

	if($limit == 0){
		$limit_count = count($subpages);
	} else {
		$limit_count = $limit;
	}


	if($type == "list") {
		$ret .= '<ul class="column-'.$column.'">';
	}

	for ($i=1; $i < $limit_count; $i++) { 

		if($type == "list") {
			$ret .= '<li><a href="'. $subid[$i] . '">'. $subpages[$i] . '</a></li>';
		} else {
			$ret .= '<a href="'. $subid[$i] . '">'. $subpages[$i] . '</a>';
			if( ($i+1) != count($subpages)) {
				if($after == ',') {
					$ret .= '' . $after . " ";
				} else {
					$ret .= " " . $after . " ";
				}
			}

		}
	}

	if($type == "list") {
		$ret .= "</ul>";
	}
	
	return $ret;
}
add_shortcode( 'city_pages', 'webnotik_city_pages' );


function webnotik_city_keywords( $atts ){
	global $post;

	//$city_keyword = get_post_meta($post->ID, 'city_keyword', true);
	$city_name = get_post_meta($post->ID, 'city_name', true);

	if(!empty($city_name)) {
		return '<span class="city city-meta">' . $city_name . '</span>';
	} else {
		if( !is_page() ) {
			return '<span class="city not-page">City Name</span>';
		} else {
			$city_pages = get_option('city_pages');
			$main_target = $city_pages["names"][0];
			return '<span class="city city-none">' . $main_target . '</span>';
		}
	}



}
add_shortcode( 'city_keywords', 'webnotik_city_keywords' ); //needs to be deprecated
add_shortcode( 'city_name', 'webnotik_city_keywords' );



function webnotik_city_map( $atts ){
	global $post;
	$city_map = htmlspecialchars_decode(get_post_meta( $post->ID, 'city_map', true));

	if(empty($city_map)) {
		$business_data = get_option( 'general' );
		$city_map = $business_data['business_map'];
	}
		
	return '<div class="city-map">' . $city_map . '</div>';
}
add_shortcode( 'city_map', 'webnotik_city_map' ); 


function webnotik_geo_number( $atts ){
	global $post;
	$geo_number = get_post_meta( $post->ID, 'geo_number', true);

	if(empty($geo_number)) {
		$business_data = get_option( 'general' );
		$geo_number = $business_data['business_phone_number'];
	}
		
	return $geo_number;
}
add_shortcode( 'geo_number', 'webnotik_geo_number' );


function webnotik_business_name( $atts ){
	global $post;
	$business_name = get_post_meta( $post->ID, 'business_name', true);
	if(empty($business_name)) {
		$business_data = get_option( 'general' );
		$business_name = $business_data['business_name'];
	}		
	return $business_name;
}
add_shortcode( 'business_name', 'webnotik_business_name' ); 


function webnotik_email_address( $atts ){
	global $post;
	$email_address = get_post_meta( $post->ID, 'email_address', true);
	if(empty($email_address)) {
		$business_data = get_option( 'general' );
		$email_address = $business_data['business_email_address'];
	}		
	return $email_address;
}
add_shortcode( 'email_address', 'webnotik_email_address' ); 