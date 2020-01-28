<?php
function webnotik_form_shortcode( $atts ){  
	$atts = shortcode_atts(
		array(
			'type' => 'seller',
			'source' => 'organic',
		), $atts, 'webnotik_form' );
	$type = $atts["type"];
	$source = $atts["source"];

	$allowed_types = array('seller_form', 'buyer_form', 'private_lending_form', 'contractor_form', 'realtors_form', 'wholesale_form' , 'contact_form', 'extra_form');

	if(in_array($type, $allowed_types)) {
		$forms = get_option('forms');
		$form = $forms[$type];

		$business_name = get_option( 'webnotik_business_name');
		$trust_badge = get_stylesheet_directory_uri() . '/assets/img/trust-badge.jpg';
		$allow_trust_badge = get_option( 'allow_trust_badge');
		if($form != "") {
			$ret = '<div class="gform_wrapper webnotik-'.$type.'">';

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


function webnotik_business_shortcode( $atts ){  
	$atts = shortcode_atts(
		array(
			'business' => 'seller',
			'text' => 'LINK',
			'type' => 'html',
		), $atts, 'webnotik' );
	$business = $atts["business"];
	$type = $atts["type"];
	$text = $atts["text"];
	$ret = '';

	if($business == "weburl") {
		return get_site_url();
	}

	$allowed_types = array("weburl","name", "phone_number", "email_address", "address", "address_line_1", "address_line_2", "logo_url");

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
	if($type != 'html') {
		return $ret;
	} else {

		return '<span class="info-'.$business.'">'.$ret.'</span>';
	}
		
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
	$atts = shortcode_atts(
		array(
			'type' => 'single', //or inline
			'item' => 'main'
		), $atts, 'city_keywords' );

	$type = $atts["type"];
	$item = $atts["item"];

	if(is_front_page()) {
		$city_pages_data = get_option('city_pages');
		$target = $city_pages_data["names"][0];
		return '<span class="city city-'.$target.'">' . $target . '</span>';
	} else {
		$city_keyword = get_post_meta($post->ID, 'city_keyword', true);
		if(!empty($city_keyword)) {
			return '<span class="city city-meta">' . $city_keyword . '</span>';
		}
	}

	
	$exclude_words = array( 'for', 'my', 'in', 'In', 'We', 'Buy', 'Houses', 'House', 'Cash', 'Fast', 'Sell');
	$post_title = get_the_title($post->ID);

	foreach ($exclude_words as $ex_word) {
		$post_title = str_replace($ex_word, '', $post_title);
	}
	
	if(!empty($post_title)) {
		$ret = $post_title;
	} else {
		$ret = 'City Name';
	}
		
	return '<span class="city city-'.$post_title.'">' . $ret . '</span>';
}
add_shortcode( 'city_keywords', 'webnotik_city_keywords' );