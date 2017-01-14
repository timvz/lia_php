<?php

add_filter( 'gform_pre_render_7', 'pre_render_assistance' );
function pre_render_assistance($form)
{
	global $wpdb;
	global $login_id_7;
	$login_id_7 = get_current_user_id();
	
	add_filter( 'gform_field_value_login_id', 'request_populate_login_id' );
	function request_populate_login_id( $value ) {
		global $login_id_7;
   		return $login_id_7;
	}
	
	global $assistance_record;
	$id = get_query_var( 'id', -1 );
	if ($id != -1)
	{
		$assistance_records = $wpdb->get_results("SELECT * FROM wp_help_assistance WHERE id = " . $id);
		$assistance_records_count = $wpdb->num_rows;
		$assistance_record = $assistance_records[0];
	}
	
	//Find the voucher dropdown.
	foreach ( $form['fields'] as &$field ) {
		
		//Have to iterate through the fields to find the correct id.  No way to
		//pull directly out of the fields array, as those indexes could change.
        if ( $field->id != 46 ) {
            continue;
        }
        
   		$vouchers = $wpdb->get_results("select * from wp_help_voucher_types");
   		$voucher_array = Array();
   		$voucher_array[] = array( 'text' => 'None', 'value' => 0 );
   		foreach ($vouchers as $voucher)
   		{
   			if ($voucher->id == $assistance_record->voucher_id)
   				$voucher_array[] = array( 'text' => $voucher->name, 'value' => $voucher->id, 'isSelected' => true );
   			else
   				$voucher_array[] = array( 'text' => $voucher->name, 'value' => $voucher->id );
   		}
   		
   		$field['choices'] = $voucher_array;
   		
        break;
    }
    
	//Don't need to to do the rest, unless this form was called for a particular
	//request id.
	if ($id == -1)
	{
		return $form;
	}

	if ($assistance_records_count < 1)
	{
		return $form;
	}
	
	add_filter( 'gform_field_value_date_of_request', 'populate_date_of_request' );
	function populate_date_of_request( $value ) {
		global $assistance_record;
		if (strtotime($assistance_record->date_of_request) > 0)
			return $assistance_record->date_of_request;
		else
   			return null;
	}
	add_filter( 'gform_field_value_referred_by', 'populate_referred_by' );
	function populate_referred_by( $value ) {
		global $assistance_record;
   		return $assistance_record->referred_by;
	}
	add_filter( 'gform_field_value_requested_from', 'populate_requested_from' );
	function populate_requested_from( $value ) {
		global $assistance_record;
   		return $assistance_record->requested_from;
	}
	add_filter( 'gform_field_value_request_dollar_amt', 'populate_request_dollar_amt' );
	function populate_request_dollar_amt( $value ) {
		global $assistance_record;
   		return $assistance_record->request_dollar_amt;
	}
	add_filter( 'gform_field_value_assisted', 'populate_assisted' );
	function populate_assisted( $value ) {
		global $assistance_record;
   		return $assistance_record->assisted;
	}
	add_filter( 'gform_field_value_assisted_date', 'populate_assisted_date' );
	function populate_assisted_date( $value ) {
		global $assistance_record;
		if (strtotime($assistance_record->assisted_date) > 0)
			return $assistance_record->assisted_date;
		else
   			return null;
	}
	add_filter( 'gform_field_value_assisted_details', 'populate_assisted_details' );
	function populate_assisted_details( $value ) {
		global $assistance_record;
   		return $assistance_record->assisted_details;
	}
	add_filter( 'gform_field_value_request_description', 'populate_request_description' );
	function populate_request_description( $value ) {
		global $assistance_record;
   		return $assistance_record->request_description;
	}
	add_filter( 'gform_field_value_reason_for_request', 'populate_reason_for_request' );
	function populate_reason_for_request( $value ) {
		global $assistance_record;
   		return $assistance_record->reason_for_request;
	}
	add_filter( 'gform_field_value_remarks', 'populate_remarks' );
	function populate_remarks( $value ) {
		global $assistance_record;
   		return $assistance_record->remarks;
	}
	add_filter( 'gform_field_value_info_verified', 'populate_info_verified' );
	function populate_info_verified( $value ) {
		global $assistance_record;
   		return $assistance_record->info_verified;
	}
	add_filter( 'gform_field_value_info_verified_ok', 'populate_info_verified_ok' );
	function populate_info_verified_ok( $value ) {
		global $assistance_record;
   		return $assistance_record->info_verified_ok;
	}
	add_filter( 'gform_field_value_info_verified_notes', 'populate_info_verified_notes' );
	function populate_info_verified_notes( $value ) {
		global $assistance_record;
   		return $assistance_record->info_verified_notes;
	}
	add_filter( 'gform_field_value_follow_up_date', 'populate_follow_up_date' );
	function populate_follow_up_date( $value ) {
		global $assistance_record;
		if (strtotime($assistance_record->follow_up_date) > 0)
			return $assistance_record->follow_up_date;
		else
   			return null;
	}
	add_filter( 'gform_field_value_follow_up_showed', 'populate_follow_up_showed' );
	function populate_follow_up_showed( $value ) {
		global $assistance_record;
   		return $assistance_record->follow_up_showed;
	}
	add_filter( 'gform_field_value_follow_up_noshow_reason', 'populate_follow_up_noshow_reason' );
	function populate_follow_up_noshow_reason( $value ) {
		global $assistance_record;
   		return $assistance_record->follow_up_noshow_reason;
	}
	add_filter( 'gform_field_value_applicant_id', 'populate_applicant_id' );
	function populate_applicant_id( $value ) {
		global $assistance_record;
   		return $assistance_record->applicant_id;
	}
	add_filter( 'gform_field_value_assistance_id', 'populate_assistance_id' );
	function populate_assistance_id( $value ) {
		global $assistance_record;
   		return $assistance_record->id;
	}
	return $form;
}


//Handle the post submissions.  Form ID 2 is the "New Applicant" gravity form.
add_filter( 'gform_confirmation_7', 'confirm_new_assistance_request', 10, 4);
function confirm_new_assistance_request($confirmation, $form, $entry, $ajax )
{
	$applicant_id = 0 + $entry["33"]; //coerce id into an int value
	$assistance_id = 0 + $entry["35"]; //coerce id into an int value
    $date_of_request = $entry["21"];
    $referred_by = $entry["3"];
    $requested_from = $entry["4"];
    $request_dollar_amt = $entry["6"];
    $request_description = $entry["5"];
    $reason_for_request = $entry["10"];
    $assisted = $entry["7"];
    $assisted_date = $entry["9"];
    $assisted_details = $entry["8"];
    $remarks = $entry["12"];
    $info_verified = $entry["29"];
    $info_verified_ok = $entry["15"];
    $info_verified_notes = $entry["16"];
    $follow_up_date = $entry["18"];
    $follow_up_showed = $entry["19"];
    $follow_up_noshow_reason = $entry["20"];
    $login_id = $entry["45"];
    $voucher_id = $entry["46"];

    if ($assisted_date == '') 
    	$assisted_date = 'null';
    if ($follow_up_date == '')
    	$follow_up_date = 'null';
    
    global $wpdb;
    
	if ($assistance_id >= 0)
	{
		
		$wpdb->update('wp_help_assistance', 
			array ( 'updated_by' => $login_id,
					'date_of_request' => $date_of_request,
					'referred_by' => $referred_by,
					'request_description' => $request_description,
					'requested_from' => $requested_from,
					'request_dollar_amt' => $request_dollar_amt,
					'assisted' => $assisted,
					'assisted_details' => $assisted_details,
					'assisted_date' => $assisted_date,
					'reason_for_request' => $reason_for_request,
					'remarks' => $remarks,
					'info_verified' => $info_verified,
					'info_verified_ok' => $info_verified_ok,
					'info_verified_notes' => $info_verified_notes,
					'follow_up_date' => $follow_up_date,
					'follow_up_showed' => $follow_up_showed,
					'follow_up_noshow_reason' => $follow_up_noshow_reason,
					'applicant_id' => $applicant_id,
					'voucher_id' => $voucher_id
				   ),
			array( 'id' => $assistance_id ), null, null
		); 
		$follow_up_date_format = '%d';
		
		

		
		if ($wpdb->last_error != '')
		{
			$db_error_msg = wpdb_get_error($wpdb);
			return $db_error_msg;
		}
	}
	else
	{
		$wpdb->insert('wp_help_assistance', 
			array ( 'created_by' => $login_id,
					'updated_by' => $login_id,
					'date_of_request' => $date_of_request,
					'referred_by' => $referred_by,
					'request_description' => $request_description,
					'requested_from' => $requested_from,
					'request_dollar_amt' => $request_dollar_amt,
					'assisted' => $assisted,
					'assisted_details' => $assisted_details,
					'assisted_date' => $assisted_date,
					'reason_for_request' => $reason_for_request,
					'remarks' => $remarks,
					'info_verified' => $info_verified,
					'info_verified_ok' => $info_verified_ok,
					'info_verified_notes' => $info_verified_notes,
					'follow_up_date' => $follow_up_date,
					'follow_up_showed' => $follow_up_showed,
					'follow_up_noshow_reason' => $follow_up_noshow_reason,
					'applicant_id' => $applicant_id,
					'voucher_id' => $voucher_id
				   ),
			array ( '%d', //'created_by' => $login_id,
					'%d', //'updated_by' => $login_id,
					'%s', //'date_of_request' => $date_of_request,
					'%s', //'referred_by' => $referred_by,
					'%s', //'request_description' => $request_description,
					'%s', //'requested_from' => $requested_from,
					'%d', //'request_dollar_amt' => $request_dollar_amt,
					'%s', //'assisted' => $assisted,
					'%s', //'assisted_details' => $assisted_details,
					'%s', //'assisted_date' => $assisted_date,
					'%s', //'reason_for_request' => $reason_for_request,
					'%s', //'remarks' => $remarks,
					'%s', //'info_verified' => $info_verified,
					'%s', //'info_verified_ok' => $info_verified_ok,
					'%s', //'info_verified_notes' => $info_verified_notes,
					'%s', //'follow_up_date' => $follow_up_date,
					'%s', //'follow_up_showed' => $follow_up_showed,
					'%s', //'follow_up_noshow_reason' => $follow_up_noshow_reason,
					'%d', //'applicant_id' => $applicant_id
					'%d') //'voucher_id' => $voucher_id
		); 
		if ($wpdb->last_error != '')
		{
			$db_error_msg = wpdb_get_error($wpdb);
			return $db_error_msg;
		}
		$assistance_id = $wpdb->insert_id;

	}
    
 	return array( 'redirect' => get_permalink(200) . '?id=' . $assistance_id );
 }
?>
