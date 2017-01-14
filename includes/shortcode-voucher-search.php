<?php

add_shortcode('update_voucher_followup', 'update_voucher_followup');
function update_voucher_followup($atts)
{
	
    $request_id = $_GET['request_id'];
    $follow_up = $_GET['followed_up'];
    $event_type = $_GET['event_type'];
    $notes = $_GET['notes'];
    
    $follow_up = ($follow_up === "true") ? 1 : 0;
    
    if ($request_id > 0)
    {
    	global $wpdb;
    	if ($event_type === "checkbox")
    	{
    		if (!$wpdb->query("update wp_help_assistance set 
    			voucher_followed_up = $follow_up where id = $request_id"))
    		{
    			echo "Error: " . wpdb_get_error($wpdb);
    			return;
    		}
    		
    	}
    	else if ($event_type === "text")
    	{
    		if (!$wpdb->query("update wp_help_assistance set 
    			voucher_followup_notes = '$notes' where id = $request_id"))
    		{
    			echo "Error: " . wpdb_get_error($wpdb);
    			return;
    		}
    	}
    }
    echo "A-OK";
}

add_shortcode('voucher_search_results', 'search_vouchers');
function search_vouchers($atts)
{
	
	$oldest_date = false;
	$days_back = intval($_GET['days_back']);
	if ($days_back > 0)
	{
		$oldest_date = strtotime(date('Y-m-d') . " -" . $days_back . " days");
		$oldest_date = date('Y-m-d', $oldest_date);
	}
    $voucher_id = $_GET['voucher_types'];
    $include_follow_ups = $_GET['include_follow_ups'];
    
    global $wpdb;
    
    $min_date = "";
    $sql = "SELECT ass.id as request_id, app.first_name, app.last_name, app.phone, 
    	app.mobile_phone, ass.voucher_followed_up, ass.assisted_date,
    	ass.voucher_followup_notes, ass.reason_for_request, voucher.name as voucher_name
    	FROM wp_help_assistance AS ass 
    	JOIN wp_help_applicant AS app
    	ON ass.applicant_id = app.id
    	JOIN wp_help_voucher_types AS voucher
    	ON ass.voucher_id = voucher.id";
    $where = "where";
    if ($oldest_date != false)
    {
    	$sql .= " $where ass.assisted_date >= DATE('$oldest_date')";
    	$where = "and";
    }
    if ($voucher_id != 0)
    {
    	$sql .= " $where ass.voucher_id = $voucher_id";
    	$where = "and";
    }
    if ($include_follow_ups == false)
    {
    	$sql .= " $where ass.voucher_followed_up = 0";
    	$where = "and";
    }
    $sql .= " order by ass.assisted_date desc";
    
    $search_results = $wpdb->get_results($sql);
    	
    if ($wpdb->num_rows > 0)
    {
    	echo <<<ESCRIPT
<script>

function followed_up(request_id, str_obj_type, obj) {

    var followed_up;
    var notes;
    
	if (str_obj_type == "checkbox")
	{
	    followed_up = obj.checked;
	    notes = "";
	}
	if (str_obj_type == "text")
	{
	    notes = obj.value;
	    followed_up = "";
	}

	//Do some AJAX stuff to update the user's follow_up status.
    var qryString = "?request_id=" + request_id + "&event_type=" + str_obj_type +
    	"&followed_up=" + followed_up + "&notes=" + encodeURI(notes)
    				    
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200)
        {
            //if (alert.responseText.startsWith("Error"))
            //	alert(this.responseText);
        }
    };
    xhttp.open("GET", "/voucher-ajax-script/ " + qryString, true);
    xhttp.send();
} 
</script>
ESCRIPT;

    	echo "<table class=\"display hover row-border\" id=\"vouher_table\">" . PHP_EOL;
    	echo "<thead><tr><th align='left'>Name</th>";
    	echo "<th align='left'>Phone</th>";
    	echo "<th align='left'>Reason For Request</th>";
    	echo "<th align='left'>Voucher Date</th>";
    	echo "<th align='left'>Voucher Type</th>";
    	echo "<th align='center'>Followed Up</th>";
    	echo "<th align='center'>Follow Up Notes<br>(Tab Key to Apply)</th>";
    	echo "</tr></thead><tbody>";

    	foreach ($search_results as $result)
    	{
    		echo "<tr><td>$result->first_name $result->last_name</td>";
    		$mobile = ($result->mobile_phone) ? 
    			"$result->mobile_phone (mobile)<br>" : "";
    		$home = ($result->phone) ? 
    			"$result->phone (home)<br>" : "";
    		echo "<td>$mobile $home</td>";
    		echo "<td>$result->reason_for_request</td>";
    		echo "<td>" . GetLink(Permalink::ViewRequest, 
    			 Array("id" => $result->request_id), $result->assisted_date) . 
    			 "</td>";
    		echo "<td>$result->voucher_name</td>";
    		$checked = ($result->voucher_followed_up) ? "checked" : "";
    		echo "<td align=\"center\">
    		      <input id=\"follow_up_$result->request_id\" type=\"checkbox\" 
    		      value=\"true\" $checked onchange=\"followed_up($result->request_id, 
    			  'checkbox', document.getElementById('follow_up_$result->request_id'))\"/></td>";
    		echo "<td align=\"center\">
    		      <textarea id=\"notes_$result->request_id\" rows=\"2\" cols=\"20\" 
    		      onchange=\"followed_up($result->request_id, 'text', document.getElementById(
    		      'notes_$result->request_id'))\">$result->voucher_followup_notes</textarea></td></tr>";
    	}
    	echo "</tbody></table>";
    }
    else
    {
    	echo "No results found.";
    }
    
 }
 ?>
