<?php

//Reject if accessed directly or when not uninstalling
defined( 'WP_UNINSTALL_PLUGIN' ) || die( 'Our survey says: ... X.' );

delete_site_option( 'bop_newsletters_version' ); //change this

//Uninstall code - remove everything with wiping
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->bop_newsletters_subscribers}" );

$gs = bop_newsletters_get_group_ids();
foreach( $gs as $g ){
  bop_newsletters_delete_group( $g );
}
