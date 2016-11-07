<?php 

//Reject if accessed directly
defined( 'ABSPATH' ) || die( 'Our survey says: ... X.' );

//Plugin code

require_once bop_newsletters_plugin_path( 'class-bop-newsletter-subscriber.php' );
require_once bop_newsletters_plugin_path( 'class-bop-newsletter-group.php' );
require_once bop_newsletters_plugin_path( 'functions.php' );

add_action( 'admin_menu', function(){
  add_menu_page(
    __( 'Bop Newsletter Groups', 'bop-newsletters' ),
    __( 'Bop Newsletters', 'bop-newsletters' ),
    'manage_options', 'bop-newsletters',
    function(){
      if( ! current_user_can( 'manage_options' ) ) wp_die( 'Insufficient permissions.' );
      require bop_newsletters_plugin_path( 'templates/admin/list.php' );
    },
    'dashicons-email-alt',
    80
  );
  add_submenu_page(
    'bop-newsletters',
    __( 'Bop Newsletter Groups', 'bop-newsletters' ),
    __( 'All Groups', 'bop-newsletters' ),
    'manage_options',
    'bop-newsletters'
  );
  
  add_submenu_page(
    'bop-newsletters',
    __( 'New Newsletter Group', 'bop-newsletters' ),
    __( 'Add New', 'bop-newsletters' ),
    'manage_options',
    'bop-newsletter-new',
    function(){
      if( ! current_user_can( 'manage_options' ) ) wp_die( 'Insufficient permissions.' );
      require bop_newsletters_plugin_path( 'templates/admin/new.php' );
    }
  );
  
  add_submenu_page(
    'bop-newsletters',
    __( 'Edit Newsletter Group', 'bop-newsletters' ),
    __( 'Edit Group', 'bop-newsletters' ),
    'manage_options',
    'bop-newsletter-edit',
    function(){
      if( ! current_user_can( 'manage_options' ) ) wp_die( 'Insufficient permissions.' );
      require bop_newsletters_plugin_path( 'templates/admin/edit.php' );
    }
  );
} );

//hack to stop submenu item appearing
add_filter( 'parent_file', function( $file ){
  remove_submenu_page( 'bop-newsletters', 'bop-newsletter-edit' );
  return $file;
} );

//add page conditions
add_action( 'admin_init', function(){
  global $pagenow;
  if( $pagenow == 'admin.php' ){
    if( $_GET['page'] == 'bop-newsletter-edit' ){
      if( ! isset( $_GET['id'] ) || ! bop_newsletters_group_exists( $_GET['id'] ) ){
        wp_redirect( admin_url( 'admin.php?page=bop-newsletter-new' ) );
        exit;
      }
    }
  }
} );


//update subscriber email when user email updated
add_filter( 'send_email_change_email', function( $send, $user, $userdata ){
  $subscriber = new Bop_Newsletter_Subscriber( $user['ID'] );
  if( $subscriber->id ){
    $subscriber->email = $userdata['user_email'];
    $subscriber->update();
  }
  return $send;
}, 10, 3 );
