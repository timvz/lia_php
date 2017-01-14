<?php

//Used on the assistande form.
add_shortcode('view_assistance_request', 'view_assistance_request');
function view_assistance_request($atts) 
{
	
	global $wpdb;
	
	//If we have an assistance_id ("id"), then we are updating an existing request.
	$id = get_query_var( 'id', -1 );

	$request = $wpdb->get_row("SELECT app.id AS app_id, app.first_name, 
		app.last_name, assist.* , voucher.name AS voucher_name
		FROM wp_help_assistance AS assist
		JOIN wp_help_applicant AS app ON assist.applicant_id = app.id
		LEFT JOIN wp_help_voucher_types AS voucher ON assist.voucher_id = voucher.id
		WHERE assist.id = $id");
	
	$applicant_id = $request->app_id;
	$first_name = $request->first_name;
	$request_date = TrimZeroDate($request->date_of_request);
	$follow_up_date = TrimZeroDate($request->follow_up_date);
	//$view_profile_url = get_permalink(194) . "?id=" . $applicant_id;
	$view_profile_link = GetLink(Permalink::ViewProfile,
		Array("id" => $applicant_id),
		$request->last_name . ", " . $request->first_name);
	
	$referred_by = ($request->referred_by) ? "<b>Referred by:</b> $request->referred_by <br/>" : "";
	$requested_from = ($request->requested_from) ? "<b>Requested of:</b> $request->requested_from <br/>" : "";
	$request_dollar_amt = ($request->request_dollar_amt) ? "<b>Amount: </b>\$$request->request_dollar_amt<br/>" : "";
	$assisted = ($request->assisted) ? "<b>Assisted: </b>$request->assisted<br/>" : "";
	$voucher = ($request->voucher_name) ? "<b>Voucher: </b>$request->voucher_name<br/>" : "";
	
	$top_middle_info = <<<MIDLINFO
<b>Requested by:<br/></b>$view_profile_link<br/>
<b>On:</b>  $request_date<br/>
$referred_by
$requested_from
$request_dollar_amt
$assisted
$voucher
<b>What&nbsp;Provided/Reason&nbsp;Denied:<br/></b>$request->assisted_details
MIDLINFO;

	$info_verified = ($request->info_verified) ? "<b>Info Verified: </b>$request->info_verified<br/>" : "";
	$info_verified_ok = ($request->info_verified_ok) ? "<b>Info Checked Out: </b>$request->info_verified_ok<br/>" : "";
	$info_verified_notes = ($request->info_verified_notes) ? "<b>Verification Notes: </b>$request->info_verified_notes<br/>" : "";

	$middle_middle_info = <<<MIDLMIDL
$info_verified
$info_verified_ok
$info_verified_notes
MIDLMIDL;

	$follow_up_showed = ($request->follow_up_showed) ? "<b>Follow Up Showed: </b>$request->follow_up_showed<br/>" : "";
	$follow_up_noshow_reason = ($request->follow_up_noshow_reason) ? "<b>No Show Reason: </b>$request->follow_up_noshow_reason<br/>" : "";
	$follow_up_date = ($request->follow_up_date) ? "<b>Follow Up Date: </b>$request->follow_up_date<br/>" : "";
	
	$middle_right_info = <<<MIDLRIGHT
$follow_up_showed
$follow_up_date
$follow_up_noshow_reason
MIDLRIGHT;

	$request_description = ($request->request_description) ? 
		"<b>Request Description:</b><br/>$request->request_description<br/>" : "";
	$reason_for_request = ($request->reason_for_request) ? 
		"<b>Reason For Request:</b><br/>$request->reason_for_request<br/>" : "";
	$remarks = ($request->remarks) ? 
		"<b>Remarks:</b><br/>$request->remarks" : "";


	$top_right_info = <<<RIGHTINFO
$request_description
$reason_for_request
$remarks
RIGHTINFO;
	
	$other_requests = $wpdb->get_results("SELECT id, date_of_request, requested_from
			assisted, request_description FROM wp_help_assistance WHERE applicant_id = " . 
			$applicant_id . " and id != " . $id);
	//Build the table content, from query results above.
	"<table><thead><tr><th>No additional requests from " .
		$request->first_name . "</th></tr></thead></table>";
	if ($wpdb->num_rows > 0)
	{
		$other_request_content = "<b>Other Requests:</b><table><thead><tr>
		    <th align='left'>Request Date</th>
		    <th align='left'>Organization</th>
     		<th align='left'>Request Description</th>
     		<th align='left'>Help Provided</th>
     		<th align='left'>View</th></tr></thead><tbody>";
     		
		foreach ($other_requests as $other_request)
		{
			//$link = get_permalink(200) . "?id=" . $other_request->id;
			$link = GetLink(Permalink::ViewRequest,
				Array("id" => $other_request->id), "View");
			
			$yesno = ($other_request->assisted > 0) ? "Yes" : "No";
			
			$other_request_content .= "<tr><td>" . 
				TrimZeroDate($other_request->date_of_request) . "</td>" .
				"<td>" . $other_request->requested_from . "</td>" .
				"<td>" . $other_request->request_description . "</td>" .
				"<td>" . $yesno . "</td>" .
				"<td>" . $link . "</td></tr>";
				
		}
		$other_request_content .= "</tbody></table>";
	}
	//$assistance_url = get_permalink(216);
	$edit_reqeust_link = GetLink(Permalink::AddRequest,
		Array("id" => $id), "Edit This Request");
	$new_request_link = GetLink(Permalink::AddRequest,
		Array("applicant_id" => $applicant_id), 
		"New&nbsp;Request&nbsp;for&nbsp;" . $first_name);
	
	$assistance_text = <<<ETEXT1
<table id='request'>
  <tr>
    <td width=200 align="center" valign="top">
    <img src='/images/helping-hand.jpg' width=125 height=125><br/>
        $edit_reqeust_link<br/><br/>$new_request_link<br/>
    </td>
    <td width=200 align="left" valign="top">$top_middle_info</td>
    <td align="left" valign="top">$top_right_info</td>
  </tr>
  <tr>
    <td width=200 align="center"></td>
    <td width=200 align="left" valign="top">$middle_middle_info</td>
    <td align="left" valign="top">$middle_right_info</td>
  </tr>  
  <tr>
    <td colspan=3>
      $other_request_content
    </td>
   </tr>
</table>
ETEXT1;

    echo $assistance_text;

}



//Used on the assistande form.
add_shortcode('applicant_last_first_name', 'applicant_last_first_name');
function applicant_last_first_name($atts) {
	
	global $wpdb;
	
	//If we have an applicant_id, then this is a new assistance request.
	$applicant_id = get_query_var( 'applicant_id', -1 );
	//If we have an assistance_id ("id"), then we are updating an existing request.
	$assistance_id = get_query_var( 'id', -1 );
    
    if ($assistance_id >= 0)
    {
    	$assist_name = $wpdb->get_row(
    		"select app.id, app.first_name, app.last_name " .
     		"from wp_help_applicant as app, wp_help_assistance as ass " .
     		"where ass.id = " . $assistance_id .
     		" and ass.applicant_id = app.id");
     	if ($wpdb->num_rows > 0)
     	{
     		$result = "<table><thead><tr><th><b>Update " . $assist_name->first_name . 
     			" " . $assist_name->last_name . "'s request for assistance.</b></th></tr></thead></table>";
     	}
     	else
     	{
     	    $result = "<table><thead><tr><th>Error:  No requests found with 
     	    	ID " . $assistance_id . "<br/>Last Query = '" . $wpdb->last_query . "'</th></tr></thead></table>";
    	}
    }
    else
    {
    	if ($applicant_id >= 0)
    	{
    		$name = $wpdb->get_row(
    			"select first_name, last_name " .
     			"from wp_help_applicant where id = " . $applicant_id);
     		if ($wpdb->num_rows > 0)
     		{
     			$result = "<table><thead><tr><th><b>New Assistance Request For:</b> " . $name->last_name . 
     			  ", " . $name->first_name . "</th></tr></thead></table>";
     		}
     		else
     		{
     			$result = "<table><thead><tr><th>Error: no applicant found with ID " . 
     				$applicant_id . ".</th></tr></thead></table>";
     		}
    	}
    	else
    	{
    		$result = "<table><thead><tr><th>Error:  No  ID defined.  This form needs an 
    	    	assistance request ID or an Applicant ID in the URL query string.
    	    	</th></tr></thead></table>";
    	}
    }
     
    return $result;
}


//Used on the assistande form.
add_shortcode('request_summaries', 'list_other_assistance_requests');
function list_other_assistance_requests($atts) {
	
	global $wpdb;

	//If we have an applicant_id in the URL query string, then this is a new assistance request.
	$applicant_id = get_query_var( 'applicant_id', -1 );
	$new_request = ($applicant_id >= 0) ? true : false;
	
	//If we have an assistance_id ("id"), then we are updating an existing request.
	$assistance_id = get_query_var( 'id', -1 );
	$update_request = ($assistance_id >= 0) ? true : false;

	extract(shortcode_atts(array(
		'formID' => '7'
	), $atts));
	
	$thisForm = RGFormsModel::get_form_meta($formID);
	
	//$form_content = print_r($thisForm, true);
	$query = '';
	if ($new_request) 
	{
		$query = "SELECT date_of_request, requested_from
			assisted, request_description FROM wp_help_assistance WHERE applicant_id = " . 
			$applicant_id;
	}
	else if ($update_request)
	{
		$applicant_id = $wpdb->get_var("SELECT distinct(applicant_id) FROM wp_help_assistance WHERE 
			id = " . $assistance_id);
		if ($wpdb->num_rows > 0)
		{
			$query = "SELECT date_of_request, requested_from,
				assisted, request_description FROM wp_help_assistance WHERE applicant_id = " . 
				$applicant_id . " and id != " . $assistance_id;
		}
	}
	
	//Build the table content, from query results above.
	$table_content = "<thead><tr><th>No Additional Requests</th></tr></thead>";
	if ($query != '')
	{
		$table_content = "<thead><tr><th>Request Date</th><th>Organization</th>
     		<th>Help Provided</th><th>Description</th></tr>";
     	$results = $wpdb->get_results($query);
		foreach ($results as $result)
		{
			$yesno = ($result->assisted > 0) ? "Yes" : "No";
			$table_content .= "<tr><td>" . TrimZeroDate($result->date_of_request) . "</td>" .
				"<td>" . $result->requested_from . "</td>" .
				"<td>" . $yesno . "</td>" .
				"<td>" . $result->request_description . "</td></tr>";
		}
	}
     
     $content_table = "<table>" . $table_content . "</table>";
     
     
     return $content_table;
}


add_shortcode('list_requests', 'list_requests');
function list_requests($atts) 
{

	global $wpdb;
	
	//If we have an assistance_id ("id"), then we are updating an existing request.
	$applicant_id = get_query_var( 'applicant_id', -1 );
	$query;
	if ($applicant_id >= 0)
	{
		$query = "SELECT app.id as app_id, app.first_name, app.last_name, ass.* 
			FROM wp_help_assistance as ass, wp_help_applicant as app
			WHERE ass.applicant_id = " . $applicant_id . " and
			ass.applicant_id = app.id";
			
	}
	else
	{
		$query = "SELECT app.id as app_id, app.first_name, app.last_name, ass.* 
			FROM wp_help_assistance as ass, wp_help_applicant as app
			WHERE ass.applicant_id = app.id";
	}
	
    $requests = $wpdb->get_results($query);
    $details_url = get_permalink(200);
    $profile_url = get_permalink(194);
    
    $output = "No Request History Found";
    if ($wpdb->num_rows > 0)
    {
        $output = "<table class=\"display hover row-border\" id=\"search_table\">" . PHP_EOL;;
        $image = "";
        if ($request->fulfilled)
        {
            $image = "<img src='/images/green-check.jpg' height=40 width=40 border=0 />";
        }
        $output .= "<thead><tr><th align='left'>Applicant</th><th>Request&nbsp;Date</th>";
        $output .= "<th align='left'>Description</th>";
        $output .= "<th>Amount</th><th>Request&nbsp;Details</th>";
        $output .= "</tr></thead>";
        $output .= "<tbody>";
        foreach ($requests as $request)
        {
        	$assistance_link = GetLink(Permalink::AddRequest,
        		Array("id" => $request->id), "Edit");
        	//$assistance_link = $assistance_url . "?id=" . $request->id;
        	$details_link = GetLink(Permalink::ViewRequest,
        		Array("id" => $request->id), "Show");
        	//$details_link = $details_url . "?id=" . $request->id;
        	$profile_link = GetLink(Permalink::ViewProfile,
        		Array("id" => $request->app_id),
        		$request->first_name . "&nbsp;" . $request->last_name);
        	//$profile_link = $profile_url . "?id=" . $request->app_id;
            $amount = ($request->request_dollar_amt == null) ? "none" :
            	"$" . $request->request_dollar_amt;
            $output .= "<tr>";
            $output .= "<td>" . $profile_link . "</td>";
            $output .= "<td>" . TrimZeroDate($request->date_of_request) . "</td>";
            $output .= "<td>" . $request->request_description . "</td>";
            $output .= "<td>" . $amount . "</td>";
            $output .= "<td align='center'>" . $details_link . "&nbsp;&nbsp;&nbsp;";
            $output .= $assistance_link . "</td>";
            $output .= "</tr>";
        }
        $output .= "</tbody></table>";
	}
	echo $output;
	
}

/*
add_shortcode('delete_request', 'delete_request');
function delete_request($atts) 
{

	extract(shortcode_atts(array('arg' => 'default'), $atts));
	
	global $wpdb;
	$id = (array_key_exists('id', $_GET)) ? $_GET['id'] : "";
	$confirmed = (array_key_exists('confirmed', $_GET)) ? 1 : 0;
	//Valid record types are 'applicant', 'request', 'service'.
	
	
	$name = $wpdb->get_row("select first_name, last_name from " .
		"wp_help_applicant where id = " . 
		$id, ARRAY_N);

	$first_name = $name[0];
	$last_name = $name[1];

	if (!$confirmed) 
	{
		echo "Are you sure you want to delete <b>" . $first_name . 
			" " . $last_name . "</b>?<br><br>This will delete all " .
			"assistance requests associated with this person too.<br><br>";
		echo PHP_EOL . PHP_EOL;
		echo GetLink(Permalink::DeleteApplicant,
			Array("id" => $id, 
				  "confirmed" => "true", 
				  "type" => "applicant"),
			"Yes!  Delete this applicant.");
			
		//echo "<a href='" . get_permalink(247) . "?id=" . 
		//	$id . "&confirmed=true&type=applicant'>Yes!  Delete this applicant.</a>";
	}
	else
	{
		$wpdb->delete( 'wp_help_applicant', array( 'id' => $id ) );
		$wpdb->delete( 'wp_help_assistance', array( 'applicant_id' => $id ) );
		$num_records = $wpdb->num_rows;
		echo $first_name . " " . $last_name . " and all assistance requests have been deleted.";
	}
}
*/
?>
