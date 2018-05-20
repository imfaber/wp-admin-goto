<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
	die;
}

foreach (get_users() as $user) {
	delete_user_meta( $user->ID, '_admin_goto_pages' );
}
