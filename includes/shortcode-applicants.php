<?php

add_shortcode('applicant_entry_counts', 'applicant_entry_counts');
function applicant_entry_counts($atts)
{
	global $wpdb;
	$query = <<<EQUERY1
SELECT users.display_name AS name, users.ID AS ID, orgs.name as org_name, 
COUNT( apps.id ) AS entries, MAX( apps.timestamp ) AS last_update
FROM wp_hi0lk7_users AS users
LEFT JOIN wp_help_applicant AS apps ON users.ID = apps.created_by
LEFT OUTER JOIN wp_help_organizations AS orgs ON users.organization_id = orgs.ID
GROUP BY users.ID
EQUERY1;

	echo "Below are the counts of applicants entered by users on the system.";
	echo "<table><thead><tr><th align=left>User Display Name</th>";
	echo "<th align=left>Organization</th>";
	echo "<th align=left>Applicants Added</th>";
	echo "<th align=left>Last Known Entry Date</th></tr></thead>";
	$entry_counts = $wpdb->get_results($query);
	foreach ($entry_counts as $entry_count)
	{
		if ($entry_count->entries > 0)
		{
			echo "<tr><td>$entry_count->name</td><td>";
			echo GetLink(Permalink::OrganizeUsers, 
				Array(), $entry_count->org_name);
			echo "</td><td>";
			echo GetLink(Permalink::Applicants, 
				Array("created_by" => $entry_count->ID), 
				$entry_count->entries . " applicants");
			echo "</td><td>$entry_count->last_update</td></tr>";
		}
	}
	echo "</table>";
}

add_shortcode('find_applicants', 'find_applicants');
function find_applicants($atts)
{
	extract(shortcode_atts(array('arg' => 'default'), $atts));

	global $wpdb;
	$user_filter = (array_key_exists('users', $_GET)) ? $_GET['users'] : "";
	if ($user_filter && $user_filter != 'all') 
	{
		$user_filter = <<<EUSERFILTER
where wp_help_applicant.last_name like
'%$user_filter%' or
wp_help_applicant.first_name like
'%$user_filter%'
EUSERFILTER;
	}
	else
	{
		$user_filter = "";
	}
		
	$creator = "";
	$created_by = (array_key_exists('created_by', $_GET)) ? $_GET['created_by'] : "";
	if ($created_by)
	{
		$user_filter = " where wp_help_applicant.created_by = " . $created_by;	
		$owner_rs = $wpdb->get_results("select display_name from wp_hi0lk7_users where ID = " . $created_by);
		if ($wpdb->num_rows > 0)
		{
			$owner_row = $owner_rs[0];
			$creator = $owner_row->display_name;	
		}
	}
	
	$query = <<<EQUERY
select wp_help_applicant.id as id, first_name, last_name, 
mobile_phone, phone 
from wp_help_applicant 
$user_filter 
order by last_name
EQUERY;
	
	$applicants = $wpdb->get_results($query);
	
	if ($creator)
	{
		echo "Showing records entered by <b>" . $creator . "</b>";
	}
	
	echo "<table class=\"display hover row-border\" id=\"search_table\">" . PHP_EOL;
	echo "<thead><tr><th align='left'>Last Name</th><th align='left'>First Name</th><th>Phone</th><th>Action</th>";
	echo "</tr></thead>";
	echo "<tfoot><tr><th align='left'>Last Name</th><th align='left'>First Name</th><th>Phone</th><th>Action</th>";
	echo "</tr></tfoot><tbody>";
	foreach ($applicants as $applicant)
	{
		$phone = $applicant->mobile_phone;
		if ($phone != null)
			$phone = $phone . "  <i>(mobile)</i>";
		else 
		{
			$phone = $applicant->phone;
			if ($phone != null)
				$phone = $phone . "  <i>(home)</i>";
		}
		
		$row = <<<EROW
<tr><td>$applicant->last_name</td>
<td>$applicant->first_name</td>
<td align=center>$phone</td>
<td align=center>
<select id="action-$applicant->id" onchange="PageJump($applicant->id)">
  <option value="null">--Select--
  <option value="vp">View Profile
  <option value="ep">Edit Profile
  <option value="vh">View History
  <option value="ar">Add Request
  <option value="da">Delete Applicaant
</select></td></tr>
EROW;
		echo $row;
	}
	echo "</tbody></table>" . PHP_EOL;
	
	$vp = get_permalink(Permalink::ViewProfile) . "?id=";
	$ep = get_permalink(Permalink::EditProfile) . "?id=";
	$vh = get_permalink(Permalink::ViewHistory) . "?applicant_id=";
	$ar = get_permalink(Permalink::AddRequest) . "?applicant_id=";
	$da = get_permalink(Permalink::DeleteApplicant) . "?id=";
	
	echo <<<ESCRIPT
<script>
function PageJump(id) {

		var actionid = "action-".concat(id);
		var x = document.getElementById(actionid).value;
		switch(x) 
		{
		case "vp":
			window.location.href = "$vp".concat(id);
			break;  
		case "ep":
			window.location.href = "$ep".concat(id);
			break;
		case "vh":
			window.location.href = "$vh".concat(id);
			break;
		case "ar":
			window.location.href = "$ar".concat(id);
			break;
		case "da":
			window.location.href = "$da".concat(id);
			break;
		default:
			break;
		}
	//}
	
	//clickID = "";
}
</script>
ESCRIPT;
	echo PHP_EOL;
}

add_shortcode('nav_search', 'nav_search');
function nav_search($atts)
{
	
	extract(shortcode_atts(array('arg' => 'default'), $atts));

	global $wpdb;
	$qry_string = (array_key_exists('users', $_GET)) ? $_GET['users'] : "";
	$user_filter;
	if ($qry_string && $qry_string != 'all') 
	{
		$filter = <<<EUSERFILTER
WHERE app.first_name LIKE  '$qry_string'
OR app.last_name LIKE  '$qry_string'
OR users1.display_name LIKE  '%$qry_string%'
OR org.name LIKE  '%$qry_string%'
EUSERFILTER;
	}
	else
	{
		$filter = "";
	}
	
	$query = <<<EQUERY
SELECT app.id AS id, app.first_name, app.last_name, app.mobile_phone, 
app.phone, assist.id AS assist_id, assist.date_of_request, 
assist.request_description, assist.reason_for_request, 
assist.requested_from, users1.display_name AS created_by, 
org.name AS created_by_org
FROM wp_help_applicant AS app
LEFT JOIN wp_help_assistance AS assist ON app.id = assist.applicant_id
LEFT JOIN wp_hi0lk7_users AS users1 ON app.created_by = users1.id
LEFT JOIN wp_hi0lk7_users AS users2 ON assist.created_by = users2.id
LEFT JOIN wp_help_organizations AS org ON users1.organization_id = org.id
$filter
ORDER BY last_name, assist.date_of_request DESC
EQUERY;
	
	$applicants = $wpdb->get_results($query);
	
	if ($wpdb->num_rows < 1)
	{
		echo "Searched applicant names, record owners, and organizations.";
		echo "<br>No matches found for <b>'$qry_string'</b>.";
		return;
	}
	
	$applicant_array = array();
	$assistance_array = array();
	foreach ($applicants as $applicant)
	{
		$applicant_key = "_".$applicant->id;
		if (!array_key_exists($applicant_key, $applicant_array))
		{
			$applicant_array[$applicant_key] = $applicant;
		}
		if (!is_null($applicant->assist_id))
		{
			if (array_key_exists($applicant_key, $assistance_array))
			{
				$applicant_assist_records = $assistance_array[$applicant_key];
				$applicant_assist_records[] = $applicant;
				$assistance_array[$applicant_key] = $applicant_assist_records;
			}
			else
			{
				$applicant_assist_records = array();
				$applicant_assist_records[] = $applicant;
				$assistance_array[$applicant_key] = $applicant_assist_records;
			}
		}
	}
	
	echo "Below are records who's applicant names, record owners, or ";
	echo "record owner organizations have <b>'$qry_string'</b> in their name.";
	echo "<table class=\"display hover row-border\" id=\"search_table\">" . PHP_EOL;
	echo "<thead><tr><th align='left'>Last Name</th>";
	echo "<th align='left'>First Name /<br>Date of Request</th>";
	echo "<th align='left'>Phone /<br>Request Description</th>";
	echo "<th align='left'>Added By</th>";
	echo "<th align='center'>Action</th>";
	echo "</tr></thead>";
	//echo "<tfoot><tr><th align='left'>Last Name</th><th align='left'>First Name</th>";
	//echo "<th align='left'>Phone</th><th align='left'>Action</th></tr></tfoot>";
	echo "<tbody>";
	
	$last_appid_for_loop = -1;
	$last_assist_id = -1;
	$assistance_row;
	
	$applicant_keys = array_keys($applicant_array);
	for ($i = 0; $i < count($applicant_keys); ++$i)
	{
		$key = $applicant_keys[$i];
		$applicant = $applicant_array[$key];
		$app_row = "";
		$phone = $applicant->mobile_phone;
		
		if ($phone != null)
			$phone = $phone . "  <i>(mobile)</i>";
		else 
		{
			$phone = $applicant->phone;
			if ($phone != null)
				$phone = $phone . "  <i>(home)</i>";
		}
		$app_row = <<<EAPPROW
<tr>
<td>$applicant->last_name</td>
<td>$applicant->first_name</td>
<td>$phone</td>
<td>$applicant->created_by_org</td>
<td align="center">
<select id="action-$applicant->id" onchange="PageJump($applicant->id)">
  <option value="null">--Select--
  <option value="vp">View Profile
  <option value="ep">Edit Profile
  <option value="vh">View History
  <option value="ar">Add Request
  <option value="da">Delete Applicaant
</select></td></tr>
EAPPROW;
		
		$assistance_row = "";
		if (array_key_exists($key, $assistance_array))
		{
			$assistance_records = $assistance_array[$key];
			$assistance_row = "";
			for ($j = 0; $j < count($assistance_records); ++$j)
			{
				$applicant = $assistance_records[$j];
				$description = $applicant->request_description;
				if (strlen($description) > 40)
				{
					$description = substr($description, 0, 40) . " ...";
				}
				$action_link = GetLink(Permalink::ViewRequest, 
					Array("id" => $applicant->assist_id), "View") . 
					"&nbsp;&nbsp;|&nbsp;&nbsp;" .
					GetLink(Permalink::AddRequest, 
					Array("id" => $applicant->assist_id), "Edit");;
				$assistance_row .= <<<EASSISTROW
<tr><td></td>
<td><font size=-1 color="blue">$applicant->date_of_request</font></td>
<td><font size=-1 color="blue">$description</font></td>
<td><font size=-1 color="blue">$applicant->created_by_org</font></td>
<td align="center"><font size=-1>$action_link</font></td>
</tr>
EASSISTROW;
				
			}
			
		}
		
		echo $app_row . $assistance_row;
	}
	echo "</tbody></table>" . PHP_EOL;
	
	$vp = get_permalink(Permalink::ViewProfile) . "?id=";
	$ep = get_permalink(Permalink::EditProfile) . "?id=";
	$vh = get_permalink(Permalink::ViewHistory) . "?applicant_id=";
	$ar = get_permalink(Permalink::AddRequest) . "?applicant_id=";
	$da = get_permalink(Permalink::DeleteApplicant) . "?id=";
	
	echo <<<ESCRIPT
<script>
function PageJump(id) {

		var actionid = "action-".concat(id);
		var x = document.getElementById(actionid).value;
		switch(x) 
		{
		case "vp":
			window.location.href = "$vp".concat(id);
			break;  
		case "ep":
			window.location.href = "$ep".concat(id);
			break;
		case "vh":
			window.location.href = "$vh".concat(id);
			break;
		case "ar":
			window.location.href = "$ar".concat(id);
			break;
		case "da":
			window.location.href = "$da".concat(id);
			break;
		default:
			break;
		}
	//}
	
	//clickID = "";
}
</script>
ESCRIPT;
	echo PHP_EOL;
}

add_shortcode('profile', 'show_profile');
function show_profile($atts)
{
	extract(shortcode_atts(array('arg' => 'default'), $atts));
	
	global $wpdb;
	$id = (array_key_exists('id', $_GET)) ? $_GET['id'] : "";
	
	if (is_numeric($id))
	{
		$combo_box = <<<ESELECT
<select id="action-$id" onchange="PageJump($id)">
  <option value="null">--Select--
  <option value="ep">Edit Profile
  <option value="vh">View History
  <option value="ar">Add Request
  <option value="da">Delete Applicant
</select>
ESELECT;
		$ep = get_permalink(Permalink::EditProfile) . "?id=";
		$vh = get_permalink(Permalink::ViewHistory) . "?applicant_id=";
		$ar = get_permalink(Permalink::AddRequest) . "?applicant_id=";
		$da = get_permalink(Permalink::DeleteApplicant) . "?id=";
	
	    $combo_box_js = <<<EJS
<script>
function PageJump(id) {

		var actionid = "action-".concat(id);
		var x = document.getElementById(actionid).value;
		switch(x) 
		{
		case "ep":
			window.location.href = "$ep".concat(id);
			break;
		case "vh":
			window.location.href = "$vh".concat(id);
			break;
		case "ar":
			window.location.href = "$ar".concat(id);
			break;
		case "da":
			window.location.href = "$da".concat(id);
			break;
		default:
			break;
		}
}
</script>
EJS;

		$requests = $wpdb->get_results(
			"select id from wp_help_assistance where applicant_id = " .
			$id);
		$request_count = $wpdb->num_rows;
		$request_id = -1;
		if ($request_count == 1)
		{
			$request_id = $requests[0]->id;
		}
		
		$applicants = $wpdb->get_results(
			"SELECT * FROM wp_help_applicant WHERE id = " . $id);
		
		$applicant = $applicants[0];
		$phone = '';
		if ($applicant->phone != null)
		{
			$phone = "Home: " . $applicant->phone;
		} 
		if ($applicant->mobile_phone != null)
		{
			if ($phone != '') $phone = $phone . "<br>";
			$phone = $phone . "Mobile: " . $applicant->mobile_phone;
		}
		
		$work = "Work:  Unemployed";
		if ($employment_status != "None")
		{
			$work = "Work:  " . $applicant->employer_name;
			if ($applicant->employer_phone != null)
			{
				$work = $work . "<br/>Work Phone: " . 
					$applicant->employer_phone;
			}
		}
		
		$married_status = $applicant->married_status . "<br/>";
		$spouse_info = "<br/>" . $applicant->married_status;
		if ($applicant->married_status == 'Married' ||
			$applicant->married_status == 'Domestic Partner')
		{
			$spouse_info = "<u>" . $spouse_info . ":</u> " . 
				$applicant->spouse_name;
			$spouse_info = $spouse_info . "<br>Phone: " . 
				$applicant->spouse_phone;
			$spouse_info = $spouse_info . "<br>Work: " . 
				$applicant->spouse_employer;
			$spouse_info = $spouse_info . "<br>Work Phone: " . 
				$applicant->spouse_employer_phone;
			$married_status = "";
		}
		else
		{
			$spouse_info = "";
		}
		
		$requests_alink;
		if ($request_count > 0)
		{
			if ($request_count > 1)
			{
				$requests_alink = GetLink(Permalink::ViewHistory,
					Array("applicant_id" => $id), 
					$request_count . ' requests for assistance.');
			}
			else
			{
				$requests_alink = GetLink(Permalink::ViewRequest,
					Array("id" => $request_id), 
					$request_count . ' request for assistance.');
			}
		}
		else
		{
			$requests_alink = 'No requests for assistance.';
		}
		
		$applicant_text = <<<E_A_TEXT
$applicant->first_name $applicant->last_name<br/>
$applicant->address_line_1<br/>
$applicant->city, $applicant->state $applicant->zip<br/>
$phone<br/>
$work<br/>
$married_status
$requests_alink
E_A_TEXT;
	
		$kids = $wpdb->get_results(
			"SELECT * FROM wp_help_applicant_children " .
			"WHERE parent_id = " . $id);
		$kid_text = "No Children Living At Home";
		if ($wpdb->num_rows > 0)
		{
			$kid_text = "<u>Children Living At Home:</u>";
			foreach ($kids as $kid)
			{
				$kid_text = $kid_text . "<br>" . $kid->first_name .
					"&nbsp;" . $kid->last_name . "&nbsp;(Age:&nbsp;" . 
					$kid->age . ")";
			}	
		}
		
		
		$residents = $wpdb->get_results(
			"SELECT * FROM wp_help_applicant_residents " .
			"WHERE applicant_id = " . $id);
		$res_text = "No Live-In Residents";
		if ($wpdb->num_rows > 0)
		{
			$res_text = "<u>Residents Living In Home:</u>";
			foreach ($residents as $res)
			{
				$res_text = $res_text . "<br>" . $res->applicant_relationship .
				": " . $res->first_name ." " . $res->last_name . 
					" (Mobile: " . $res->mobile_phone . ")";
			}	
		}
	
		//$profile_url = get_permalink(178);
		//$profile_link = GetLink(Permalink::EditProfile,
		//	Array("id" => $id), "Edit Profile");
		
		//$add_request_url = get_permalink(216);
		//$add_request_link = GetLink(Permalink::AddRequest,
		//	Array("applicant_id" => $id), "Add New Request");
		
		$profile_text = <<<ETEXT1
$combo_box_js
<table id='profile'>
  <tbody>
  <tr>
	<td width=200 align="center"><img src='/images/head-shoulders.jpg' width=125 height=125><br/>
	  $combo_box
	</td>
	<td valign="top">$applicant_text</td>
	<td align="left" valign="top">$spouse_info</td>
  </tr>
  <tr>
	<td colspan=3>
	  <table><tr><td width=200>$kid_text</td><td>$res_text</td></tr></table>
	</td>
   </tr>
  </tbody>
</table>
ETEXT1;
	
		echo $profile_text;
	
			
	}
	else  //$id is not numeric 
	{
		echo "Could not find an applicant profile for id='" . $id . "'";
	}
}

add_shortcode('delete_applicant', 'delete_applicant');
function delete_applicant($atts) 
{

	extract(shortcode_atts(array('arg' => 'default'), $atts));
	
	global $wpdb;
	$id = (array_key_exists('id', $_GET)) ? $_GET['id'] : "";
	$confirmed = (array_key_exists('confirmed', $_GET)) ? 1 : 0;
	$record_type = (array_key_exists('type', $_GET)) ? $_GET['type'] : "applicant";
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
		//echo "<a href='" . get_permalink(247) . "?id=" . 
		//	$id . "&confirmed=true&type=applicant'>Yes!  Delete this applicant.</a>";
		echo GetLink(Permalink::DeleteApplicant,
			Array("id" => $id, 
				  "confirmed" => "true", 
				  "type" => "applicant"),
			"Yes! Delete this applicant.");
	}
	else
	{
		$login_id = get_current_user_id();		
		$wpdb->delete( 'wp_help_applicant', array( 'id' => $id ) );
		$wpdb->delete( 'wp_help_assistance', array( 'applicant_id' => $id ) );
		$wpdb->query( "UPDATE wp_help_applicant_audit SET deleted_by = $login_id WHERE id = $id" );
 
		$num_records = $wpdb->num_rows;
		echo $first_name . " " . $last_name . " and all assistance requests have been deleted.";
	}
}



?>