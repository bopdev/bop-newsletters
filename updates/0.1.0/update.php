<?php 

//Reject if accessed directly
defined( 'BOP_PLUGIN_UPDATING' ) || die( 'Our survey says: ... X.' );

//Update (or install) script

//DB
global $wpdb;

//Guide: https://codex.wordpress.org/Creating_Tables_with_Plugins
//Check https://core.trac.wordpress.org/browser/trunk/src/wp-admin/includes/schema.php#L0 for example sql


$charset_collate = $wpdb->get_charset_collate();
$max_index_length = 191;

$sql = "CREATE TABLE {$wpdb->bop_newsletters_subscribers} (
  subscriber_id bigint(20) unsigned NOT NULL auto_increment,
  user_id bigint(20) unsigned NOT NULL default '0',
  created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  email varchar(255) NOT NULL default '',
  group varchar(42) default 'default',
  status varchar(63) default 'subscribed',
  PRIMARY KEY  (subscriber_id),
  KEY user_id (user_id),
  KEY group (group),
  KEY status (status)
) $charset_collate;";

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $sql );

unset( $sql, $charset_collate );
