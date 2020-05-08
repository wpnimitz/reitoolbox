<?php
function if_selected_option($value, $data) {
	if($value == $data) { 
		return ' selected';
	} else {
		return '';
	}
}

//Register Meta Box
function citypage_pro_metabox() {
    add_meta_box( 
    	'citypage_pro_metabox_id', 
    	esc_html__( 'City Page Details', 'citypage-pro' ), 
    	'citypage_pro_metabox_callback', 
    	'page', 
    	'normal', 
    	'high'
    );
}
add_action( 'add_meta_boxes', 'citypage_pro_metabox');


//Add fields to meta box
function citypage_pro_metabox_callback( $meta_id ) {
	
	wp_nonce_field( 'citypage_pro_metabox_callback', 'citypage_pro_nonce' );
	

	echo '<div class="city-page-details">';

	echo '<div class="form-group">';
	$city_name = get_post_meta( $meta_id->ID, 'city_name', true);


	//just encase the website is used the old child theme
	if(empty($city_name)) {
		$city_name = get_post_meta( $meta_id->ID, 'city_keyword', true);
	}

	echo '<label for="city_name">City Name / Target City</label>';
	echo '<input id="city_name" type="text" value="'.$city_name.'" placeholder="" name="city_name">';
	echo '<span style="display:block">Usage: [city_name].</span>';
	echo '</div>'; //end form-group


	echo '<div class="form-group">';
	$geo_number = get_post_meta( $meta_id->ID, 'geo_number', true);
	echo '<label for="geo_number">Geo Phone Number</label>';
	echo '<input id="geo_number" type="text" value="'.$geo_number.'" placeholder="" name="geo_number">';
	echo '<span style="display:block">Usage: [geo_number].</span>';
	echo '<span style="display:block">If this is empty, it will show the business phone number instead.</span>';
	echo '</div>'; //end form-group


	echo '<div class="form-group">';
	$city_map = htmlspecialchars_decode(get_post_meta( $meta_id->ID, 'city_map', true ));
	echo '<label for="city_map">City Map <span class="map-try">Try</span></label>';
	echo '<span style="display:block">Make sure to grab your map from Google Maps and select small in the embed code.</span>';
	echo '<textarea id="city_map" type="text" placeholder="" name="city_map">'.$city_map.'</textarea>';
	echo '<span style="display:block">Usage: [city_map].</span>';
	echo '<span style="display:block">If this is empty, it will show the business map in the toolbox\'s general settings.</span>';
	echo '<div class="map-try-wrapper"></div>';
	echo '</div>'; //end form-group


	echo '<div><h4>The Following Fields is not necessary, but you can use them and the default value is whatever default you have on the toolbox general settings</h4></div>';


	echo '<div class="form-group">';
	$business_name = get_post_meta( $meta_id->ID, 'business_name', true);
	echo '<label for="business_name">Business Name</label>';
	echo '<input id="business_name" type="text" value="'.$business_name.'" placeholder="" name="business_name">';
	echo '<span style="display:block">Usage: [business_name].</span>';
	echo '</div>'; //end form-group

	echo '<div class="form-group">';
	$email_address = get_post_meta( $meta_id->ID, 'email_address', true);
	echo '<label for="email_address">Email Address</label>';
	echo '<input id="email_address" type="text" value="'.$email_address.'" placeholder="" name="email_address">';
	echo '<span style="display:block">Usage: [email_address].</span>';
	echo '</div>'; //end form-group



	// echo '<div class="form-group">';
	// $city_indexed = get_post_meta( $meta_id->ID, 'city_indexed', true );
	// echo '<label for="city_indexed">Is this city page already been indexed by Google?</label>';
	// echo '<select id="city_indexed" type="text" placeholder="" name="city_indexed">';
	// echo '<option value="no" '.if_selected_option("no",$city_indexed).'> No </option>';
	// echo '<option value="yes" '.if_selected_option("yes",$city_indexed).'> Yes  </option>';	
	// echo '</select>';
	// echo '</div>'; //end form-group




	echo '</div>'; //city page details end

	//echo '<option value="sold" '.if_selected_option("sold",$property_status).'> Sold </option>';


}

function rental_meta_box_save_metabox( $post_id ) {
  if( !isset( $_POST['citypage_pro_nonce'] ) || !wp_verify_nonce( $_POST['citypage_pro_nonce'],'citypage_pro_metabox_callback') ) 
    return;

  if ( !current_user_can( 'edit_post', $post_id ))
    return;

  if ( isset($_POST['city_name']) ) {        
    update_post_meta($post_id, 'city_keyword', sanitize_text_field($_POST['city_name']));  //backward compatibility    
    update_post_meta($post_id, 'city_name', sanitize_text_field($_POST['city_name']));      
  }

  if ( isset($_POST['geo_number']) ) {        
    update_post_meta($post_id, 'geo_number', sanitize_text_field($_POST['geo_number']));      
  }

  if ( isset($_POST['city_map']) ) {        
    update_post_meta($post_id, 'city_map', htmlspecialchars($_POST['city_map']));      
  }

  if ( isset($_POST['business_name']) ) {        
    update_post_meta($post_id, 'business_name', sanitize_text_field($_POST['business_name']));      
  }

  if ( isset($_POST['email_address']) ) {        
    update_post_meta($post_id, 'email_address', sanitize_text_field($_POST['email_address']));      
  }

}
add_action('save_post', 'rental_meta_box_save_metabox');