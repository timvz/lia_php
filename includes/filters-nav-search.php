<?php

function nav_search_box( $current_form, $item, $depth, $args )
{
  //$new_form = '<li><img width="400" height="33" border="0" src="/images/dot.png"/></li>' .
  $new_form = '<form action="' . get_permalink(Permalink::SearchResults) . 
  '" method="GET"><input type="text" name="users" placeholder="Search:"/>' .
  '</form>';
  return $new_form;
}
add_filter( 'get_nav_search_box_form', 'nav_search_box', 10, 4 );


?>