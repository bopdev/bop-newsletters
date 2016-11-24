<?php 

//admin area save actions
add_action( 'admin_init', function bop_newsletters_post(){
  if( ! isset( $_POST['action'] ) || ! isset( $_POST['deed'] ) || $_POST['action'] !== 'bop_newsletters' )
    return;
  
  if( ! current_user_can( 'edit_bop_newsletters' ) || ( isset( $_POST['user_id'] ) && ! current_user_can( 'edit_users_bop_newsletters', $data['user_id'] ) ) ){ 
    $response = [
      'success'=>false,
      'data'=>['error'=>new WP_Error( 'BOP_NEWSLETTERS_ERR_NO_PERMISSION', __( 'You do not have permission to do this.', 'bop_newsletters' ) )]
    ];
  }else{
    $response = [
      'success'=>false,
      'data'=>['error'=>new WP_Error( 'BOP_NEWSLETTERS_ERR_UNKNOWN_ACTION', __( 'The requested action is unknown.', 'bop_newsletters' ) )]
    ];
    
    switch( $_POST['object_type'] ){
      case 'group':
        $data = [];
        if( isset( $_POST['title'] ) ){
          $data['id'] = sanitize_title( $_POST['title'] );
          $data['labels'] = ['title'=>sanitize_text_field( $_POST['title'] )];
        }
        if( isset( $_POST['id'] ) ){
          $data['id'] = $_POST['id'];
        }
        
        if( ! isset( $data['id'] ) ){
          $group = new WP_Error( 'BOP_NEWSLETTERS_ERR_GROUP_NO_ID', __( 'The group identifier was not provided.', 'bop_newsletters' ) );
        }else{
          switch( $_POST['deed'] ){
            case 'add':
              $group = bop_newsletters_add_group( $data );
            break;
            case 'edit':
              $group = bop_newsletters_edit_group( $data );
            break;
          }
        }
        
        $response = ['success'=>(!is_wp_error( $group ))];
        if( $response['success'] ){
          $response['data'] = ['group'=>$group];
        }else{
          $response['data'] = ['error'=>$group];
        }
      break;
      case 'subscriber':
        $fields = ['user_id', 'email', 'group_id', 'status'];
        $data = [];
        foreach( $fields as $field ){
          if( isset( $_POST[$field] ) )
            $data[$field] = $_POST[$field];
        }
        
        switch( $_POST['deed'] ){
          case 'add':
            $bns = new Bop_Newsletter_Subscriber( $data );
            $bns = $bns->insert();
          break;
          case 'edit':
            if( ! isset( $_POST['id'] ) && (int)$_POST['id'] > 0 ){
              $bns = new WP_Error( 'BOP_NEWSLETTERS_ERR_SUB_NO_ID', __( 'The subscriber identifier was not provided.', 'bop_newsletters' ) );
              break;
            }
            $bns = new Bop_Newsletter_Subscriber( (int)$_POST['id'] );
            $bns->fill_object( $data );
            $bns = $bns->update();
          break;
        }
        
        $response = ['success'=>(!is_wp_error( $bns ))];
        if( $response['success'] ){
          $response['data'] = ['subscriber'=>$bns];
        }else{
          $response['data'] = ['error'=>$bns];
        }
      break;
    }
  }
  bop_newsletters_post_response( $response );
  
  return;
}, 10 );

add_action( 'wp_ajax_bop_newsletters', function(){
  $response = bop_newsletters_post_response();
  echo json_encode( $response );
  wp_die();
}, 20 );

function bop_newsletters_post_response( $response = null ){
  if( ! is_null( $response ) ){
    return wp_cache_set( 'post_response', $response, 'bop_newsletters' );
  }
  return wp_cache_get( 'post_response', 'bop_newsletters' );
}

//front end save actions
add_action( 'init', function(){
  if( apply_filters( 'allow_default_front_subscribe.bop_newsletters', false ) ){
    if( isset( $_POST['action'] ) && $_POST['action'] == 'add_subscriber' ){
      $wpe = new WP_Error();
      if( ! isset( $_POST['group'] ) ){
        $wpe->add( 'BOP_NEWSLETTERS_ERR_GROUP_NO_ID', __( 'The group identifier was not provided.', 'bop_newsletters' ) );
      }
      if( ! isset( $_POST['email'] ) ){
        $wpe->add( 'BOP_NEWSLETTERS_ERR_NO_EMAIL', __( 'No email address was provided.', 'bop_newsletters' ) );
      }
      if( bop_newsletters_group_exists( $_POST['group'] ) === false ){
        $wpe->add( 'BOP_NEWSLETTERS_ERR_GROUP_NOT_EXISTS', __( 'The group does not exist.', 'bop_newsletters' ) );
      }
      if( ! is_email( $_POST['email'] ) ){
        $wpe->add( 'BOP_NEWSLETTERS_ERR_INVALID_EMAIL', __( 'The email address provided was invalid', 'bop_newsletters' ) );
      }
      
      $response = ['success'=>false, 'error'=>$wpe];
      
      if( ! count( $wpe->get_codes() ) ){
        $data = ['group_id'=>$_POST['group'], 'email'=>$_POST['email']];
        
        $bns = new Bop_Newsletter_Subscriber( $data );
        $bns = $bns->insert();
        
        $response = ['success'=>(!is_wp_error( $bns ))];
        if( $response['success'] ){
          $response['data'] = ['subscriber'=>$bns];
        }else{
          $response['data'] = ['error'=>$bns];
        }
        
      }
      
      bop_newsletters_post_response( $response );
    }
  }
} );
