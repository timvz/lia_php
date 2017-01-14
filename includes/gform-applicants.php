<?php

add_filter( 'gform_pre_render_6', 'pre_render_applicant' );
function pre_render_applicant($form)
{
	
	global $login_id;
	$login_id = get_current_user_id();
	
	add_filter( 'gform_field_value_login_id', 'applicant_populate_login_id' );
	function applicant_populate_login_id( $value ) {
		global $login_id;
   		return $login_id;
	}
	
	$id = get_query_var( 'id', -1 );
	if ($id == -1)
	{
		return $form;
	}
	global $wpdb;

	$query = "SELECT * FROM wp_help_applicant WHERE id = " . $id;
	$applicants = $wpdb->get_results($query);
	if ($wpdb->num_rows < 1)
	{
		return $form;
	}
	global $applicant;
	$applicant = $applicants[0];
	
	add_filter( 'gform_field_value_first_name', 'populate_first_name' );
	function populate_first_name( $value ) {
		global $applicant;
   		return $applicant->first_name;
   		//return "FOO";
	}
	add_filter( 'gform_field_value_last_name', 'populate_last_name' );
	function populate_last_name( $value ) {
		global $applicant;
   		return $applicant->last_name;
   		//return "FOO";
	}
	add_filter( 'gform_field_value_address_line_1', 'populate_address_line_1' );
	function populate_address_line_1( $value ) {
		global $applicant;
   		return $applicant->address_line_1;
   		//return "FOO";
	}
	add_filter( 'gform_field_value_city', 'populate_city' );
	function populate_city( $value ) {
		global $applicant;
   		return $applicant->city;
	}
	add_filter( 'gform_field_value_state', 'populate_state' );
	function populate_state( $value ) {
		global $applicant;
   		return $applicant->state;
	}
	add_filter( 'gform_field_value_zip', 'populate_zip' );
	function populate_zip( $value ) {
		global $applicant;
   		return $applicant->zip;
	}
	add_filter( 'gform_field_value_phone', 'populate_phone' );
	function populate_phone( $value ) {
		global $applicant;
   		return $applicant->phone;
	}
	add_filter( 'gform_field_value_mobile_phone', 'populate_mobile_phone' );
	function populate_mobile_phone( $value ) {
		global $applicant;
   		return $applicant->mobile_phone;
	}
	add_filter( 'gform_field_value_referred_by', 'populate_referred_by' );
	function populate_referred_by( $value ) {
		global $applicant;
   		return $applicant->referred_by;
	}
	add_filter( 'gform_field_value_employment_status', 'populate_employment_status' );
	function populate_employment_status( $value ) {
		global $applicant;
   		return $applicant->employment_status;
	}
	add_filter( 'gform_field_value_employer_name', 'populate_employer_name' );
	function populate_employer_name( $value ) {
		global $applicant;
   		return $applicant->employer_name;
	}
	add_filter( 'gform_field_value_employer_phone', 'populate_employer_phone' );
	function populate_employer_phone( $value ) {
		global $applicant;
   		return $applicant->employer_phone;
	}
	add_filter( 'gform_field_value_marital_status', 'populate_married_status' );
	function populate_married_status( $value ) {
		global $applicant;
   		return $applicant->married_status;
	}
	add_filter( 'gform_field_value_spouse_name', 'populate_spouse_name' );
	function populate_spouse_name( $value ) {
		global $applicant;
   		return $applicant->spouse_name;
	}
	add_filter( 'gform_field_value_spouse_phone', 'populate_spouse_phone' );
	function populate_spouse_phone( $value ) {
		global $applicant;
   		return $applicant->spouse_phone;
	}
	add_filter( 'gform_field_value_spouse_employer', 'populate_spouse_employer' );
	function populate_spouse_employer( $value ) {
		global $applicant;
   		return $applicant->spouse_employer;
	}
	add_filter( 'gform_field_value_spouse_employer_phone', 'populate_spouse_employer_phone' );
	function populate_spouse_employer_phone( $value ) {
		global $applicant;
   		return $applicant->spouse_employer_phone;
	}
	add_filter( 'gform_field_value_children', 'populate_children' );
	function populate_children( $value ) {
		global $applicant;
		global $wpdb;
   		$parent_id = $applicant->id;
   		$children = $wpdb->get_results("select * from wp_help_applicant_children where parent_id = " . $parent_id);
   		//$children_array[];
   		foreach ($children as $child)
   		{
   			$children_array[] = $child->first_name;
   			$children_array[] = $child->last_name;
   			$children_array[] = $child->age;
   		}
   		return $children_array;
	}
	add_filter( 'gform_field_value_residents', 'populate_residents' );
	function populate_residents( $value ) {
		global $applicant;
		global $wpdb;
   		$parent_id = $applicant->id;
   		$residents = $wpdb->get_results("select * from wp_help_applicant_residents where applicant_id = " . $parent_id);
   		//$children_array[];
   		foreach ($residents as $resident)
   		{
   			$resident_array[] = $resident->first_name;
   			$resident_array[] = $resident->last_name;
   			$resident_array[] = $resident->mobile_phone;
   			$resident_array[] = $resident->applicant_relationship;
   		}
   		return $resident_array;
	}
	
	return $form;
}

//Handle the post submissions.  
add_filter( 'gform_confirmation_6', 'confirm_new_applicant', 10, 4);
function confirm_new_applicant($confirmation, $form, $entry, $ajax )
{	
	$id = 0 + $entry["18"]; //coerce id into an int value
	$first_name = $entry["32.3"];
	$last_name = $entry["32.6"];
	$street_1 = $entry["6.1"];
	$city = $entry["6.3"];
	$state = $entry["6.4"];
	$zip = $entry["6.5"];
	$country = $entry["6.6"];
	$phone = $entry["7"];
	$mobile_phone = $entry["38"];
	$referred_by = $entry["13"];
	$employment_status = $entry["26"];
	$login_id = $entry["41"];
	
	if ($employment_status != 'None')
	{
		$employer_name = $entry["14"];
		//$employer_name = ($employer_name) ? '"' . $employer_name . '"' : 'NULL';
		$employer_phone = $entry["29"];
		//$employer_phone = ($employer_phone) ? '"' . $employer_phone . '"' : 'NULL';
	}
	else
	{
		$employer_name = null;
		$employer_phone = null;
	}
	//$employment_status = '"' . $employment_status . '"';
	
	$marital_status = $entry["23"];
	if ($marital_status == 'Married' || $marital_status == 'Domestic Partner')
	{
		$spouse_name = $entry["11"];
		//$spouse_name = ($spouse_name) ? '"' . $spouse_name . '"' : 'NULL';
		$spouse_phone = $entry["29"];
		//$spouse_phone = ($spouse_phone) ? '"' . $spouse_phone . '"' : 'NULL';
		$spouse_employer_name = $entry["15"];
		//$spouse_employer_name = ($spouse_employer_name) ? '"' . $spouse_employer_name . '"' : 'NULL';
		$spouse_employer_phone = $entry["28"];
		//$spouse_employer_phone = ($spouse_employer_phone) ? '"' . $spouse_employer_phone . '"' : 'NULL';
	}
	else
	{
		$spouse_name = null;
		$spouse_phone = null;
		$spouse_empolyer_name = null;
		$spouse_empolyer_phone = null;
	}

	global $wpdb;
	if ($id >= 0)
	{
		$wpdb->query('START TRANSACTION');
		$wpdb->update('wp_help_applicant', 
			array ( 'updated_by' => $login_id,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'address_line_1' => $street_1,
					'city' => $city,
					'state' => $state,
					'zip' => $zip,
					'phone' => $phone,
					'mobile_phone' => $mobile_phone,
					'referred_by' => $referred_by,
					'employment_status' => $employment_status,
					'employer_name' => $employer_name,
					'employer_phone' => $employer_phone,
					'married_status' => $marital_status,
					'spouse_name' => $spouse_name,
					'spouse_phone' => $spouse_phone,
					'spouse_employer' => $spouse_employer_name,
					'spouse_employer_phone' => $spouse_employer_phone
				   ),
			array( 'id' => $id ), null, null
		); 
		if ($wpdb->last_error != '')
		{
			$db_error_msg = wpdb_get_error($wpdb);
			$wpdb->query('ROLLBACK');
			return $db_error_msg;
		}
		
		//$sql = "delete from wp_help_applicant_children where parent_id = " . $id;
		$wpdb->delete( 'wp_help_applicant_children', array( 'parent_id' => $id ), array( '%d' ) );
		if ($wpdb->last_error != '')
		{
			$db_error_msg = wpdb_get_error($wpdb);
			$wpdb->query('ROLLBACK');
			return $db_error_msg;
		}
		//$sql = "delete from wp_help_applicant_residents where applicant_id = " . $id;
		$wpdb->delete( 'wp_help_applicant_residents', array( 'applicant_id' => $id ), array( '%d' ) );
		if ($wpdb->last_error != '')
		{
			$db_error_msg = wpdb_get_error($wpdb);
			$wpdb->query('ROLLBACK');
			return $db_error_msg;
		}
		
		$child_array = unserialize($entry["21"]);
		if ($child_array)
		{
			foreach ($child_array as $child)
			{   	
				if ($child['First Name'] != null || $child['Last Name'] != null)
				{
					$wpdb->insert('wp_help_applicant_children',
						   array ('first_name' => $child['First Name'],
							'last_name' => $child['Last Name'],
							'age' => $child['Age'],
							'parent_id' => $id),
						array ('%s', '%s', '%d', '%d')
					);
					if ($wpdb->last_error != '')
					{
						$db_error_msg = wpdb_get_error($wpdb);
						$wpdb->query('ROLLBACK');
						return $db_error_msg;
					}
				}
			}
		}
		
		$res_array = unserialize($entry["22"]);
		if ($res_array)
		{
			foreach ($res_array as $res)
			{
				if ($res['First Name'] != null || $res['Last Name'] != null)
				{
					$wpdb->insert('wp_help_applicant_residents',
						array ('first_name' =>$res['First Name'],
						   'last_name' => $res['Last Name'],
						   'mobile_phone' => $res['Mobile Phone'] ,
						   'applicant_relationship' => $res['Relationship'],
						   'applicant_id' => $id),
						array ('%s', '%s', '%s', '%s', '%d')
					);
					if ($wpdb->last_error != '')
					{
						$db_error_msg = wpdb_get_error($wpdb);
						$wpdb->query('ROLLBACK');
						return $db_error_msg;
					}
				}
			}
		}
		$wpdb->query('COMMIT');
	}
	else
	{
		$db_err_msg;
		
		//First make sure we don't have a unique key violation.
		//Duplicate first and last names are not allowed.
		$appl_id = $wpdb->get_var("select id from wp_help_applicant where " .
					 "first_name = '$first_name' and last_name = '$last_name'");
		if (!is_null($appl_id))
		{
			$first_name = ucfirst($first_name);
			$last_name = ucfirst($last_name);
			$error_msg = "Error: an applicant named <b>$first_name $last_name</b> already exists.";
			$error_msg .= "<br/><br/>" . GetLink(Permalink::EditProfile, Array("id" => $appl_id), "Click Here");
			$error_msg .= " to update $first_name's information.";
			$error_msg .= "<br/><br/>" . GetLink(Permalink::AddRequest, Array("applicant_id" => $appl_id), "Click Here");
			$error_msg .= " to enter a new assistance request for $first_name.<br/><br/>";
			return $error_msg;
		}
		
		$wpdb->query('START TRANSACTION');
		$wpdb->insert('wp_help_applicant', 
			array ( 'created_by' => $login_id,
					'updated_by' => $login_id,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'address_line_1' => $street_1,
					'city' => $city,
					'state' => $state,
					'zip' => $zip,
					'phone' => $phone,
					'mobile_phone' => $mobile_phone,
					'referred_by' => $referred_by,
					'employment_status' => $employment_status,
					'employer_name' => $employer_name,
					'employer_phone' => $employer_phone,
					'married_status' => $marital_status,
					'spouse_name' => $spouse_name,
					'spouse_phone' => $spouse_phone,
					'spouse_employer' => $spouse_employer_name,
					'spouse_employer_phone' => $spouse_employer_phone
				   ),
			array ('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
		); 
		if ($wpdb->last_error != '')
		{
			$db_error_msg = wpdb_get_error($wpdb);
			$wpdb->query('ROLLBACK');
			return $db_error_msg;
		}
		$id = $wpdb->insert_id;
		
		$child_array = unserialize($entry["21"]);
		if ($child_array)
		{
			foreach ($child_array as $child)
			{   	
				if ($child['First Name'] != null || $child['Last Name'] != null)
				{
					$wpdb->insert('wp_help_applicant_children',
						array ('first_name' => $child['First Name'],
						   'last_name' => $child['Last Name'],
						   'age' => $child['Age'],
						   'parent_id' => $id),
						array ('%s', '%s', '%d', '%d')
					);
					if ($wpdb->last_error != '')
					{
						$db_error_msg = wpdb_get_error($wpdb);
						$wpdb->query('ROLLBACK');
						return $db_error_msg;
					}
				}
			}
		}
		$res_array = unserialize($entry["22"]);
		if ($res_array)
		{
			foreach ($res_array as $res)
			{
				if ($res['First Name'] != null || $res['Last Name'] != null)
				{
					$wpdb->insert('wp_help_applicant_residents',
						array ('first_name' =>$res['First Name'],
						   'last_name' => $res['Last Name'],
						   'mobile_phone' => $res['Mobile Phone'] ,
						   'applicant_relationship' => $res['Relationship'],
						   'applicant_id' => $id),
						array ('%s', '%s', '%s', '%s', '%d')
					);
					if ($wpdb->last_error != '')
					{
						$db_error_msg = wpdb_get_error($wpdb);
						$wpdb->query('ROLLBACK');
						return $db_error_msg;
					}
				}
			}
		}
		$wpdb->query('COMMIT');
 	}
 	return array( 'redirect' => get_permalink(194) . '?id=' . $id );
}

?>
