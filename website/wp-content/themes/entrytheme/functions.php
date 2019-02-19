<?php

// delete the default comment, post and page
wp_delete_comment( 1 );
wp_delete_post( 1, TRUE );
wp_delete_post( 2, TRUE );
 
// we need to include the file below because the activate_plugin() function isn't normally defined in the front-end
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
// activate pre-bundled plugins
activate_plugin( 'woocommerce/woocommerce.php' );
activate_plugin( 'shadowsocks-hub/shadowsocks-hub.php' );

// switch the theme to "Storefront"
switch_theme( 'storefront' );
 
?>
