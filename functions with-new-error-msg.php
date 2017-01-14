<?php
$includes = __DIR__ . "/includes";
require_once($includes . "/gform-applicants.php");
require_once($includes . "/gform-assistance.php");
require_once($includes . "/gform-general.php");
require_once($includes . "/gform-organization.php");
require_once($includes . "/shortcode-assistance.php");
require_once($includes . "/shortcode-applicants.php");
require_once($includes . "/shortcode-users.php");
require_once($includes . "/custom-widgets.php");
require_once($includes . "/filters-nav-search.php");

function TrimZeroDate($date)
{
	if (strtotime($date) > 0)
			return $date;
		else
   			return "";
}

//User the following constants for the get_permalink function
//e.g. get_permalink(Permalink::ViewProfile)
abstract class Permalink
{
	const ViewProfile 		= 194;
  	const EditProfile 		= 178;
  	const Applicants 		= 229;
  	const FindApplicants 	= 181;
  	const ViewHistory 		= 234;
  	const ViewRequest		= 200;
  	const AddRequest  		= 216;
	const DeleteApplicant	= 247;
	const RecordCounts 		= 273;
	const SearchResults		= 324;
	const OrganizeUsers		= 285;
	const Organization		= 334;
	const SetUserOrgScript	= 330;
}

//Return a link in the form of <a href="{permalink}?{queryVars}">text</a>.
//$queryVars is an associative array, e.g. Array("id" => "1")
//$permalink is an int, but use the Permalinks class for values.
function GetLink($permalinkValue, $queryVars, $text)
{
	$link = "<a href='" . get_permalink($permalinkValue);
	if (count($queryVars) > 0)
	{
		$link .= "?";
		$i = 0;
		foreach ($queryVars as $key => $value)
		{
			if ($i > 0) $link .= "&";
			$link .= $key . "=" . urlencode($value);
			++$i;
		}
	}
	$link .= "'>" . $text . "</a>";
	return $link;
}

function change_invalid_username_message( $message ) {
    return str_replace( sprintf(__('<strong>ERROR</strong>: Invalid username. <a href="%s" title="Password Lost and Found">Lost your password</a>?'), 
    	site_url('wp-login.php?action=lostpassword', 'login')), 
    	sprintf(__('<strong>ERROR</strong>: Invalid username or password. <a href="%s" title="Password Lost and Found">Lost your password</a>?'), $message ));
}
add_filter( 'login_errors', 'change_invalid_username_message' );

//URL query string variables must be defined here.  Otherwise, the
//get_query_var() method will not recognize the custom variable.
add_filter( 'query_vars', 'add_query_vars_filter' );
function add_query_vars_filter( $vars )
{
  $vars[] = "id";
  $vars[] = "applicant_id";
  return $vars;
}


add_action( 'wp_enqueue_scripts', 'frontier_enqueue_styles_tim' );
function frontier_enqueue_styles_tim() {
	//Enqueue a style for the tree table
	//wp_enqueue_style( 'jquery-tree-table-screen', "//help.crossroads-ridgecrest.org/js/screen.css", array(), '0', "screen");
	//wp_enqueue_style( 'treetable-screen', "//help.crossroads-ridgecrest.org/js/screen.css", array(), '', 'screen');
	wp_enqueue_style( 'treetable', "//help.crossroads-ridgecrest.org/js/jquery.treetable.css", array(), '');
	wp_enqueue_style( 'treetable-theme', "//help.crossroads-ridgecrest.org/js/jquery.treetable.theme.default.css", array(), '');
}

add_action( 'wp_head', 'print_datatables_script' );
function print_datatables_script() {
	echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
	//181 & 229 are applicant pages.  36 is the requests page.
	if (is_page(181) || is_page(229))
	{
		echo <<<EDATATABLES
<script type="text/javascript" language="javascript" src="//code.jquery.com/jquery-1.12.0.min.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" class="init">
	$(document).ready(function(){     
		$('#search_table').DataTable(
			{	
				"scrollY": "300px", 
				"scrollCollapse": false, 
				"paging": false,
				"columns": [ { "searchable": true }, { "searchable": true },
				    { "searchable": false },{ "searchable": false } ]
			});  
		});
</script>
EDATATABLES;
    }
    else if (is_page(234))
    {
    	echo <<<EDATATABLES2
<script type="text/javascript" language="javascript" src="//code.jquery.com/jquery-1.12.0.min.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" class="init">
	$(document).ready(function(){     
		$('#search_table').DataTable(
			{	
				"scrollY": "300px", 
				"scrollCollapse": false, 
				"paging": false,
				"columns": [ { "searchable": true }, { "searchable": false },
				    { "searchable": true },{ "searchable": false },{ "searchable": false } ]
			});  
		});
</script>
EDATATABLES2;
    }
}

function wpdb_get_error($wpdb)
{
	$last_error = htmlspecialchars( (string)$wpdb->last_error, ENT_QUOTES );
	$last_results = htmlspecialchars( (string)$wpdb->last_result, ENT_QUOTES );
    $last_query = htmlspecialchars( (string)$wpdb->last_query, ENT_QUOTES );
    $error_msg = "<div id='error'>" . 
      	"<p><strong>WordPress database error:</strong>[" . $last_error . 
       	"]<br/><code>Results:  " . $last_results . "</code></p>" .
       	"<code>Query:  " . $last_query . "</code></p></div>";
	return $error_msg;
}

 
?>
