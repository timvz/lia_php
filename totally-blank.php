<?php // Template Name: Totally Blank ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php wp_title( '|', true, 'right' ); ?></title>

</head>

<body >

	<div id="post-<?php the_ID(); ?>">

		<?php
			the_post();
			the_content();
		?>

	</div>

	
</body>
</html>