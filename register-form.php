<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<div class="tml tml-register" id="theme-my-login<?php $template->the_instance(); ?>">
	<?php $template->the_action_template_message( 'register' ); ?>
	<?php $template->the_errors(); ?>
	<form name="registerform" id="registerform<?php $template->the_instance(); ?>" action="<?php $template->the_action_url( 'register', 'login_post' ); ?>" method="post">
		<?php if ( 'email' != $theme_my_login->get_option( 'login_type' ) ) : ?>
		<p>
			<label for="first_name<?php $template->the_instance(); ?>"><?php _e( 'First Name', 'theme-my-login' ) ?></label>
			<input type="text" name="first_name" id="first_name<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'first_name' ); ?>" size="20"/>
		</p>
		<p>
			<label for="last_name<?php $template->the_instance(); ?>"><?php _e( 'Last Name', 'theme-my-login' ) ?></label>
			<input type="text" name="last_name" id="last_name<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'last_name' ); ?>" size="20"/>
		</p>
		
		<p>
			<label for="phone<?php $template->the_instance(); ?>"><?php _e( 'Phone', 'theme-my-login' ) ?></label>
			<input type="text" name="phone" id="phone<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'phone' ); ?>" size="20" size="20" />
		</p>
		<p>
			<label for="organization<?php $template->the_instance(); ?>"><?php _e( 'Organization', 'theme-my-login' ) ?></label>
			<input type="text" name="organization" id="organization<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'organization' ); ?>" size="20" />
		</p>
		<p><hr></p>
		<p class="tml-user-login-wrap">
			<label for="user_login<?php $template->the_instance(); ?>"><?php _e( 'Username (A unique name to identify you.)', 'theme-my-login' ); ?></label>
			<input type="text" name="user_login" id="user_login<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'user_login' ); ?>" size="20"/>
		</p>
		<?php endif; ?>

		<p class="tml-user-email-wrap">
			<label for="user_email<?php $template->the_instance(); ?>"><?php _e( 'E-mail', 'theme-my-login' ); ?></label>
			<input type="text" name="user_email" id="user_email<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'user_email' ); ?>" size="20"/>
		</p>

		<?php do_action( 'register_form' ); ?>

		<p class="tml-registration-confirmation" id="reg_passmail<?php $template->the_instance(); ?>"><?php echo apply_filters( 'tml_register_passmail_template_message', __( 'Registration confirmation will be e-mailed to you.', 'theme-my-login' ) ); ?></p>

		<p class="tml-submit-wrap">
			<input type="submit" name="wp-submit" id="wp-submit<?php $template->the_instance(); ?>" value="<?php esc_attr_e( 'Register', 'theme-my-login' ); ?>" />
			<input type="hidden" name="redirect_to" value="<?php $template->the_redirect_url( 'register' ); ?>" />
			<input type="hidden" name="instance" value="<?php $template->the_instance(); ?>" />
			<input type="hidden" name="action" value="register" />
		</p>
	</form>
	<?php $template->the_action_links( array( 'register' => false ) ); ?>
</div>
