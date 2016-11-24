<?php 

//Reject if accessed directly
defined( 'ABSPATH' ) || die( 'Our survey says: ... X.' );

//Plugin code
require_once bop_newsletters_plugin_path( 'class-bop-newsletter-subscriber.php' );
require_once bop_newsletters_plugin_path( 'class-bop-newsletter-group.php' );
require_once bop_newsletters_plugin_path( 'functions.php' );
require_once bop_newsletters_plugin_path( 'post.php' );


wp_cache_add_non_persistent_groups( 'bop_newsletters' );


add_action( 'admin_menu', function(){
  add_menu_page(
    __( 'Bop Newsletter Groups', 'bop-newsletters' ),
    __( 'Bop Newsletters', 'bop-newsletters' ),
    'edit_bop_newsletters', //cap
    'bop-newsletters',
    function(){
      if( ! current_user_can( 'edit_bop_newsletters' ) ) wp_die( 'Insufficient permissions.' );
      require bop_newsletters_plugin_path( 'templates/admin/list.php' );
    },
    'dashicons-email-alt',
    80
  );
  add_submenu_page(
    'bop-newsletters',
    __( 'Bop Newsletter Groups', 'bop-newsletters' ),
    __( 'All Groups', 'bop-newsletters' ),
    'edit_bop_newsletters', //cap
    'bop-newsletters'
  );
  
  add_submenu_page(
    'bop-newsletters',
    __( 'New Newsletter Group', 'bop-newsletters' ),
    __( 'Add New', 'bop-newsletters' ),
    'edit_bop_newsletters', //cap
    'bop-newsletter-new',
    function(){
      if( ! current_user_can( 'edit_bop_newsletters' ) ) wp_die( 'Insufficient permissions.' );
      require bop_newsletters_plugin_path( 'templates/admin/new.php' );
    }
  );
  
  add_submenu_page(
    'bop-newsletters',
    __( 'Edit Newsletter Group', 'bop-newsletters' ),
    __( 'Edit Group', 'bop-newsletters' ),
    'edit_bop_newsletters', //cap
    'bop-newsletter-edit',
    function(){
      if( ! current_user_can( 'edit_bop_newsletters' ) ) wp_die( 'Insufficient permissions.' );
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
      if( ! isset( $_GET['id'] ) || bop_newsletters_group_exists( $_GET['id'] ) === false ){
        wp_redirect( admin_url( 'admin.php?page=bop-newsletter-new' ) );
        exit;
      }
    }
  }
} );

//newsletter sign up in user edit page
add_action( 'in_admin_footer', function(){
  global $pagenow, $user_id;
  if( $pagenow == 'user-edit.php' || ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) ){
    if( current_user_can( 'edit_users_bop_newsletters', $user_id ) ){
      require bop_newsletters_plugin_path( 'templates/admin/user-edit.php' );
    }
  }
} );

add_action( 'admin_enqueue_scripts', function(){
  wp_enqueue_script( 'bop-newsletters-admin', plugins_url() . '/bop-newsletters/assets/js/admin.js', ['jquery', 'wp-ajax-response', 'underscore'], '0.1.0', true );
} );


//update subscriber email when user email updated
add_filter( 'send_email_change_email', function( $send, $user, $userdata ){
  $subscriber = Bop_Newsletter_Subscriber::subscriber_exists( ['user_id'=>$user['ID']] );
  if( $subscriber ){
    $subscriber->email = $userdata['user_email'];
    $subscriber->update();
  }
  return $send;
}, 10, 3 );

//success and error messages
add_action( 'admin_notices', function(){
  $response = bop_newsletters_post_response();
  if( ! $response )
    return;
  ?>
  <?php if( $response['success'] ): ?>
    <div class="notice notice-success is-dismissible">
      <p>
        <?php if( isset( $response['data']['message'] ) ): ?>
          <?php echo $response['data']['message'] ?>
        <?php else: ?>
          <?php _e( 'Success!', 'bop-newsletters' ); ?>
        <?php endif ?>
      </p>
    </div>
  <?php else: ?>
    <div class="notice notice-error is-dismissible">
      <?php $wpe = &$response['data']['error']; if( is_wp_error( $wpe ) ): ?>
        <p><?php _e( 'Error: ', 'bop-newsletters' ); ?><br>
          <ul>
            <?php foreach( $wpe->get_error_codes() as $code ): ?>
              <li><?php echo $wpe->get_error_message( $code ) ?></li>
            <?php endforeach ?>
          </ul>
        </p>
      <?php else: ?>
        <p><?php _e( 'An Error occured.', 'bop-newsletters' ); ?></p>
      <?php endif ?>
    </div>
  <?php endif ?>
  <?php
} );


add_filter( 'map_meta_cap', function( $ucaps ){
  if( ( $k = array_search( 'edit_bop_newsletters', $ucaps ) ) !== false ){
    $ucaps[$k] = 'manage_options';
  }
  if( ( $k = array_search( 'edit_users_bop_newsletters', $ucaps ) ) !== false ){
    $ucaps[$k] = 'edit_user';
  }
  return $ucaps;
}, 10, 1 );
