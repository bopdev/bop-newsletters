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
  if( bop_newsletters_group_exists( $group_data['id'] ) ){
    $group = new Bop_Newsletter_Group( $group_data );
    $group->insert();
    
    $groups = bop_newsletters_get_group_ids();
    $groups[] = $group->id;
    update_option( 'bop_newsletter_groups', $groups, 'yes' );
  }
  return $group;
}

function bop_newsletters_delete_group( $group_id ){
  if( $key = bop_newsletters_group_exists( $group_data['id'] ) ){
    $group = new Bop_Newsletter_Group( $group_data );
    $group->delete();
    
    $groups = bop_newsletters_get_group_ids();
    unset( $groups[$key] );
    update_option( 'bop_newsletter_groups', array_values( $groups ), 'yes' );
  }
  return $group;
}
