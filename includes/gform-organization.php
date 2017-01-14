<?php

add_filter( 'gform_pre_render_8', 'pre_render_organization' );
function pre_render_organization($form)
{
	
	$id = get_query_var( 'id', -1 );
	if ($id == -1)
	{
		return $form;
	}
	global $wpdb;

	$query = "SELECT * FROM wp_help_organizations WHERE id = " . $id;
	$orgs = $wpdb->get_results($query);
	if ($wpdb->num_rows < 1)
	{
		return $form;
	}
	global $org;
	$org = $orgs[0];
	
	add_filter( 'gform_field_value_ID', 'populate_id' );
	function populate_id( $value ) {
		global $org;
   		return $org->id;
   		//return "FOO";
	}	add_filter( 'gform_field_value_name', 'populate_name' );
	function populate_name( $value ) {
		global $org;
   		return $org->name;
   		//return "FOO";
	}
	add_filter( 'gform_field_value_address', 'populate_address' );
	function populate_address( $value ) {
		global $org;
   		return $org->address;
   		//return "FOO";
	}
	add_filter( 'gform_field_value_city', 'populate_city' );
	function populate_city( $value ) {
		global $org;
   		return $org->city;
	}
	add_filter( 'gform_field_value_state', 'populate_state' );
	function populate_state( $value ) {
		global $org;
   		return $org->state;
	}
	add_filter( 'gform_field_value_zip', 'populate_zip' );
	function populate_zip( $value ) {
		global $org;
   		return $org->zip;
	}
	
	return $form;
}

//Handle the post submissions.
add_filter( 'gform_confirmation_8', 'confirm_new_organization', 10, 4);
function confirm_new_organization($confirmation, $form, $entry, $ajax )
{
	$org_id = 0 + $entry["2"]; //coerce id into an int value
	$name = $entry["1"];
	$street = $entry["3.1"];
	$city = $entry["3.3"];
	$state = $entry["3.4"];
	$zip = $entry["3.5"];

	//print_r($entry);
    
    global $wpdb;
    $update_or_added;
	if ($org_id >= 0)
	{
		$update_or_added = "updated";
		$wpdb->update('wp_help_organizations', 
			array ( 'name' => $name,
					'address' => $street,
					'city' => $city,
					'state' => $state,
					'zip' => $zip
				   ),
			array( 'id' => $org_id ), null, null
		); 
		if ($wpdb->last_error != '')
		{
			$db_error_msg = wpdb_get_error($wpdb);
			return $db_error_msg;
		}
	}
	else
	{
		$update_or_added = "added";
		$wpdb->insert('wp_help_organizations', 
			array ( 'name' => $name,
					'address' => $street,
					'city' => $city,
					'state' => $state,
					'zip' => $zip
				   ),
			array ( '%s', //'name'
					'%s', //'address'
					'%s', //'city'
					'%s', //'state' 
					'%d'  //'zip'
				  )
		);
		if ($wpdb->last_error != '')
		{
			$db_error_msg = wpdb_get_error($wpdb);
			return $db_error_msg;
		}
		$org_id = $wpdb->insert_id;

	}
    
 	return array( 'redirect' => get_permalink(Permalink::OrganizeUsers) .
 		"?message=Thank you, $name was $update_or_added");
 }

?>
