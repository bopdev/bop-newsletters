<?php 

function bop_newsletters_get_group_ids(){
  return get_option( 'bop_newsletter_groups', [] );
}

function bop_newsletters_get_groups(){
  $groups = bop_newsletters_get_group_ids();
  
  for( $i = 0; $i < count( $groups ); $i++ ){
    $groups[$i] = new Bop_Newsletter_Group( $groups[$i] );
  }
  
  return $groups;
}

function bop_newsletters_group_exists( $group_id ){
  return array_search( $group_id, bop_newsletters_get_group_ids() );
}

function bop_newsletters_add_group( $group_data ){
  if( false !== bop_newsletters_group_exists( $group_data['id'] ) ){
    return new WP_Error( 'BOP_NEWSLETTERS_ERR_GROUP_EXISTS', __( 'The group already exists.', 'bop_newsletters' ) );
  }
  $group = new Bop_Newsletter_Group( $group_data );
  $success = $group->insert();
  
  if( ! $success )
    return new WP_Error( 'BOP_NEWSLETTERS_ERR_GROUP_ADD_FAILED', __( 'Adding the group failed.', 'bop_newsletters' ) );
  
  $groups = bop_newsletters_get_group_ids();
  $groups[] = $group->id;
  update_option( 'bop_newsletter_groups', $groups, 'yes' );
  return $group;
}

function bop_newsletters_edit_group( $group_data ){
  if( false === bop_newsletters_group_exists( $group_data['id'] ) ){
    return new WP_Error( 'BOP_NEWSLETTERS_ERR_GROUP_NOT_EXISTS', __( 'The group does not exist.', 'bop_newsletters' ) );
  }
  $group = new Bop_Newsletter_Group( $group_data['id'] );
  $group->fill_object( $group_data );
  $success = $group->update();
  
  if( ! $success )
    return new WP_Error( 'BOP_NEWSLETTERS_ERR_GROUP_EDIT_FAILED', __( 'Group update failed.', 'bop_newsletters' ) );
  
  return $group;
}

function bop_newsletters_delete_group( $group_id ){
  if( false === ( $key = bop_newsletters_group_exists( $group_data['id'] ) ) ){
    return new WP_Error( 'BOP_NEWSLETTERS_ERR_GROUP_NOT_EXISTS', __( 'The group does not exist.', 'bop_newsletters' ) );
  }
  $group = new Bop_Newsletter_Group( $group_data );
  $success = $group->delete();
  
  if( ! $success )
    return new WP_Error( 'BOP_NEWSLETTERS_ERR_GROUP_ADD_FAILED', __( 'Adding the group failed.', 'bop_newsletters' ) );
  
  $groups = bop_newsletters_get_group_ids();
  unset( $groups[$key] );
  update_option( 'bop_newsletter_groups', array_values( $groups ), 'yes' );
  return $group;
}

function bop_newsletters_add_group_link(){
  return apply_filters( 'get_add_group_link.bop_newsletters', admin_url( 'admin.php?page=bop-newsletter-new' ) );
}

function bop_newsletters_get_users_newsletter_group_ids( $user_id, $status = 'subscribed' ){
  global $wpdb;
  $group_ids = $wpdb->get_col( $wpdb->prepare( 
      "SELECT t.group_id AS group_id
      FROM {$wpdb->bop_newsletters_subscribers} AS t
      WHERE t.user_id = %d
       AND t.status = %s",
      $user_id,
      $status
    ),
    ARRAY_A
  );
  return $group_ids;
}

function get_bop_newsletter_template( $tmpl, $context = null ){
  $theme = get_template_directory();
  $tmpl = ltrim( $tmpl, '/' );
  $first_try = apply_filters( 'get_bop_newsletter_template.bop-newsletters', $theme . '/bop-newsletters/' . $tmpl . '.php', $tmpl, $context );
  
  if( is_object( $context ) ){
    if( is_a( $context, 'Bop_Newsletter_Subscriber' ) ){
      $subscriber = $context;
    }elseif( is_a( $context, 'Bop_Newsletter_Group' ) ){
      $group = $context;
    }
  }
  
  if( file_exists( $first_try ) ){
    include $first_try;
  }else{
    include bop_newsletters_plugin_path( '/templates/front/' . $tmpl . '.php' );
  }
}
