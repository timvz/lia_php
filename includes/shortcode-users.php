<?php

class UserClass
{
	public $user_id;
	public $display_name;
	public $org_id;
	
	public function __construct($user_id, $display_name, $org_id)
	{
		$this->user_id = $user_id;
		$this->display_name = $display_name;
		$this->org_id = $org_id;
	}
}

function find_users_from_array($user_array, $org_id)
{
	foreach($user_array as $user)
	{
		if ($user->org_id == $org_id)
			return $user;
	}
	return null;
}

add_shortcode('user_organizations', 'user_organizations');
function user_organizations($atts)
{

	global $wpdb;
	$message = get_query_var('message', "" );
	
	$user_query = <<<EQRY
SELECT users.id as user_id, users.display_name as display_name,
orgs.id as org_id, orgs.name as org_name
FROM wp_hi0lk7_users AS users
LEFT JOIN wp_help_organizations AS orgs ON users.organization_id = orgs.id
ORDER BY org_name
EQRY;

	$user_text_noorg;
	$results = $wpdb->get_results($user_query);
	$orgs_style;
	$users_with_orgs = Array();
	foreach ($results as $result)
	{
		if (is_null($result->org_id))
		{
			$user_text_noorg .= 
			  "<p draggable=\"true\" ondragstart=\"drag(event)\" " .
					"id=\"user_$result->user_id\">$result->display_name</p>";;
		}
		else
		{
			if (isset($users_with_orgs[$result->org_id]))
			{
				$users_in_org = $users_with_orgs[$result->org_id];
				$users_in_org[] = 
			    	new UserClass($result->user_id, $result->display_name, $result->org_id);
			    $users_with_orgs[$result->org_id] = $users_in_org;
			}
			else
			{
				$users_in_org = Array();
				$users_in_org[] = 
			    	new UserClass($result->user_id, $result->display_name, $result->org_id);
			    $users_with_orgs[$result->org_id] = $users_in_org;
			}
		}
		
	}
	
	//Now select all the organizations for the left side list of <divs>.
	$org_query = <<<EORGQRY
select id, name, address, city, state, zip
from wp_help_organizations
order by name
EORGQRY;

	$results = $wpdb->get_results($org_query);
	$org_count = $wpdb->num_rows;
	$orgs = Array();
	$user_org_string = "<table id=\"orgtable\">";
	$i = 1;
	foreach ($results as $result)
	{
		$orgs[] = $result->id;
		$user_org_string .= "<tr><td align=\"left\">";
		$user_org_string .= GetLink(Permalink::Organization,
			Array(id => $result->id), $result->name);
		$user_org_string .= 
			"<br/><div id=\"org_$result->id\" ondrop=\"drop(event)\" ondragover=\"allowDrop(event)\">";
		if (isset($users_with_orgs[$result->id]))
		{
			$users_in_this_org = $users_with_orgs[$result->id];
			foreach($users_in_this_org as $user_in_org)
			{
				$user_org_string .=
					"<p draggable=\"true\" ondragstart=\"drag(event)\" " .
					"id=\"user_$user_in_org->user_id\">$user_in_org->display_name</p>";
			}
		}
		$user_org_string .= "</div></td>";
		if ($i++ == 1)
		{
			$user_org_string .= "<td valign=\"top\" rowspan=$org_count><div>" .
				"No Organization</div>" .
				"<div id=\"noorg\" ondrop=\"drop(event)\" ondragover=\"allowDrop(event)\">" .
				$user_text_noorg . "</div></td>";
		}
		$user_org_string .= "</tr>";
	}
	
	
	$user_org_string .= "</table>";
	$style_and_script = get_style_and_script($orgs);
		
	//$message not getting populated by query string for some reason.
	//if (!($message === "")) 
	//{
	//	$message .= "<br/>";
	//}
	//else
	//{
		$message = "<h2>Drag and Drop</h2>";
		$message .= "<p>Drag the user names back and forth to change user organizations.</p>";
		$message .= "<p>Click on organization names to change name / address. ";
		$new_org_link = GetLink(Permalink::Organization, Array(), "click here");
		$message .= "Or, $new_org_link to add a new organization.";
	//}

	echo <<<EPAGE
$style_and_script
$message
$user_org_string
EPAGE;
}

//Returns a string of html containing the style and javascript code
//for doing drag drop functions between divisions on the screen that
//will be used to group people into the respective organizations.
function get_style_and_script($orgs)
{
	$html;
	$org_styles;
	$style = <<<ESTYLE
<style>
#orgtable {
    max-width: 500px;
    min-height: 35px;
    margin: 10px;
    padding: 10px;
    border: 1px solid black;
}
#noorgheader {
    position: relative;
    left: 520px;
}
#noorg {
    position: relative;
    min-width: 200px;
    min-height: 35px;
    margin: 10px;
    padding: 10px;
    border: 1px solid black;
}
ESTYLE;

    $org_styles = "#noorg1";
	if (count($orgs) > 0)
	{
		$x = count($orgs);
		
		for ($i = 0; $i <= $x; $i++) {
			$org_styles .= ",#org_" . $orgs[$i];
		}
		//$org_styles = substr($org_styles, 1);
		$style .= <<<EORGS
$org_styles {
    float: left;
    min-width: 200px;
    min-height: 35px;
    margin: 10px;
    padding: 10px;
    border: 1px solid black;
}
EORGS;

	}
	$style .= "</style>";
	
	$style_and_script = <<<ESCRIPT
$style
<script>
function allowDrop(ev) {
    ev.preventDefault();
}

function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
}

function drop(ev) {
    event.preventDefault();

    var data = event.dataTransfer.getData("Text");
    
    var user_element = document.getElementById(data);
    
    var orgTarget;
    if (event.target.id.indexOf("user") >= 0)
        orgTarget = event.target.parentElement;
    else
        orgTarget = event.target;
        
    orgTarget.appendChild(user_element);
    
    var userArray = user_element.id.split("_");
    var userID = userArray[1];
    
    var orgID
    if (orgTarget.id == "noorg")
    	orgID = 0;
    else
    {
    	var orgArray = orgTarget.id.split("_");
    	orgID = orgArray[1];
    }
    
    //Do some AJAX stuff to update the user's organization.
    var qryString = "?user_id=" + userID + "&" + "org_id=" + orgID;
    				
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200)
        {
            alert(this.responseText);
        }
        else if (this.readyState == 4)
        {
        	alert('Server error code ' + this.status);
        }
    };
    xhttp.open("GET", "/set-user-organization/ " + qryString, true);
    xhttp.send();
} 
</script>
ESCRIPT;

	return $style_and_script;

}
	
add_shortcode('update_user_org', 'update_user_org');
function update_user_org($atts)
{
	global $wpdb;
	
	$user_id = (array_key_exists('user_id', $_GET)) ? $_GET['user_id'] : false;
	$org_id = (array_key_exists('org_id', $_GET)) ? $_GET['org_id'] : false;
	$cleared = ($org_id == "0") ? true : false;
	
	if ($user_id === false || $org_id === false)
	{
		echo "Error:  Invalid query string.";
		return;
	}

	$data = Array('organization_id' => $org_id);
	$where = Array('id' => $user_id);
	$result = $wpdb->update('wp_hi0lk7_users', $data, $where);
	$return_msg;
	if ($result === false)
	{
		$return_msg = "Database error:  $wpdb->last_error";
	}
	else if ($result === 0)
	{
		$return_msg = "No data updated.";
	}
	else if ($result > 0)
	{
		if ($cleared)
			$return_msg = "User organization was cleared.";
		else
			$return_msg = "User organization was updated.";
	}
	
	echo $return_msg;
}
?>
