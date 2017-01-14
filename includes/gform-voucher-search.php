<?php

add_filter( 'gform_pre_render_9', 'pre_render_voucher_search' );
function pre_render_voucher_search($form)
{
	global $wpdb;
	global $login_id_9;
	$login_id_9 = get_current_user_id();
	
	//Find the voucher dropdown.
	foreach ( $form['fields'] as &$field ) {
		
		//Have to iterate through the fields to find the correct id.  No way to
		//pull directly out of the fields array, as those indexes could change.
        if ( $field->id != 4 ) {
            continue;
        }
        
   		$vouchers = $wpdb->get_results("select * from wp_help_voucher_types");
   		$voucher_array = Array();
   		$voucher_array[] = array( 'text' => 'Any', 'value' => 0 );
   		foreach ($vouchers as $voucher)
   		{
   			$voucher_array[] = array( 'text' => $voucher->name, 'value' => $voucher->id );
   		}
   		
   		$field['choices'] = $voucher_array;
   		
        break;
    }
    
	return $form;
}




?>
