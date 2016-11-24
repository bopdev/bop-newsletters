<?php 

//Reject if accessed directly
defined( 'ABSPATH' ) || die( 'Our survey says: ... X.' );

if( ! class_exists( 'Bop_Newsletter_Subscriber' ) ):

class Bop_Newsletter_Subscriber implements JSONSerializable{
  
  public $id = 0;
  
  public $user_id = 0;
  
  public $created;
  
  public $email;
  
  public $group_id = 'default';
  
  public $status = '';
  
  public function __construct( $id_or_data = 0 ){
    if( $id_or_data ){
      if( is_array( $id_or_data ) || is_object( $id_or_data ) ){
        $this->fill_object( (array)$id_or_data );
      }else{
        $this->load( $id_or_data );
      }
    }
    return $this;
  }
  
  public function load( $id ){
    $fields = $this->_fetch_from_db( $id );
    $this->fill_object( $fields );
    return $this;
  }
  
  public function fill_object( $data ){
    if( isset( $data['id'] ) ){
      $this->id = (int)$data['id'];
    }
    
    $user = false;
    if( isset( $data['user_id'] ) ){
      $user = get_userdata( $data['user_id'] );
      if( $user )
        $this->user_id = $data['user_id'];
    }
    
    if( isset( $data['created'] ) ){
      if( is_object( $data['created'] ) && is_a( $data['created'], 'Datetime' ) ){
        $this->created = $data['created'];
      }elseif( is_string( $this->created ) ){
        $this->created = new Datetime( $data['created'] );
      }
    }
    
    if( isset( $data['email'] ) && is_email( $data['email'] ) ){
      $this->email = $data['email'];
    }elseif( $user ){
      $this->email = $user->user_email;
    }
    
    if( isset( $data['group_id'] ) && bop_newsletters_group_exists( $data['group_id'] ) !== false ){
      $this->group_id = $data['group_id'];
    }
    
    if( isset( $data['status'] ) ){
      $this->status = in_array( $data['status'], array_keys( $this->get_valid_statuses() ) ) ? $data['status'] : $this->get_default_status();
    }
    
    return $this;
  }
  
  protected function _fetch_from_db( $id ){
    global $wpdb;
    $fields = $wpdb->get_row(
      $wpdb->prepare(
        "SELECT t.subscriber_id AS id,
          t.user_id AS user_id,
          t.created AS created,
          t.email AS email,
          t.group_id AS group_id,
          t.status AS status
        FROM {$wpdb->bop_newsletters_subscribers} AS t
        WHERE t.subscriber_id = %d
        LIMIT 1",
        $id
      ),
      ARRAY_A
    );
    return $fields;
  }
  
  public function get_valid_statuses(){
    return apply_filters(
      'valid_statuses.bop_bookings',
      [
        'subscribed'=>[
          'labels'=>[
            'general'=>__( 'Subscribed', 'bop-bookings' )
          ]
        ],
        'unsubscribed'=>[
          'labels'=>[
            'general'=>__( 'Unsubscribed', 'bop-bookings' )
          ]
        ]
      ],
      $this
    );
  }
  
  public function get_default_status(){
    return apply_filters( 'default_status.bop_bookings', 'subscribed', $this );
  }
  
  public function insert(){
    global $wpdb;
    
    if( ! $this->email )
      return new WP_Error( 'BOP_NEWSLETTERS_ERR_NO_EMAIL', __( 'The subscriber does not have an email.', 'bop_newsletters' ) );
      
    $insert_fields = ['user_id'=>$this->user_id, 'email'=>$this->email, 'group_id'=>$this->group_id];
    
    if( $sub = self::subscriber_exists( $insert_fields ) ){
      return $sub->fill_object( ['status'=>$this->status] )->update();
    }
    
    $insert_fields['status'] = $this->status;
    
    $formats = ['%d', '%s', '%s', '%s'];
    
    $wpdb->insert( $wpdb->bop_newsletters_subscribers, $insert_fields, $formats );
    $this->id = $wpdb->insert_id;
    return $this;
  }
  
  public function update(){
    global $wpdb;
    
    if( ! $this->id )
      return new WP_Error( 'BOP_NEWSLETTERS_ERR_SUB_NOT_EXISTS', __( 'The subscriber does not exist.', 'bop_newsletters' ) );;
    
    $update_fields = ['user_id'=>$this->user_id, 'email'=>$this->email, 'group_id'=>$this->group_id, 'status'=>$this->status];
    
    $formats = ['%d', '%s', '%s', '%s'];
    
    $wpdb->update( $wpdb->bop_newsletters_subscribers, $update_fields, ['subscriber_id'=>$this->id], $formats, ['%d'] );
    return $this;
  }
  
  public function unsubscribe(){
    $this->fill_object( ['status'=>'unsubscribed'] );
    return $this->update();
  }
  
  public static function subscriber_exists( $data ){
    global $wpdb;
    
    $data = array_intersect_key( $data, ['user_id'=>'', 'email'=>'', 'group_id'=>'', 'status'=>''] );
    
    $where = "1=1";
    foreach( $data as $k=>$v ){
      if( isset( $data[$k] ) && $v ){
        $where .= "\n AND t.{$k} = %s";
      }
    }
    
    $fields = $wpdb->get_row( $wpdb->prepare( 
        "SELECT t.subscriber_id AS id,
          t.user_id AS user_id,
          t.created AS created,
          t.email AS email,
          t.group_id AS group_id,
          t.status AS status
        FROM {$wpdb->bop_newsletters_subscribers} AS t
        WHERE {$where}
        LIMIT 1",
        $data
      ),
      ARRAY_A
    );
    
    return $fields ? new Bop_Newsletter_Subscriber( $fields ) : false;
  }
  
  public function add_notification( $template_id ){
    $bmn = new Bop_Mail_Notification( ['template_id'=>$template_id, 'to_address'=>$this->email] );
    $bmn->insert();
    return $this;
  }
  
  public function get_notifications(){
    global $wpdb;
    
    if( ! $this->id ) return;
    
    $notifications = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT t.notification_id AS id,
          t.template_id AS template_id,
          t.created AS created,
          t.to_address AS to_address,
          t.send_count AS send_count,
          t.to_send AS to_send
        FROM {$wpdb->bop_mail_notifications} AS t
        INNER JOIN {$wpdb->bop_mail_notificationmeta} AS nm ON (nm.bop_mail_notification_id = t.notification_id)
        WHERE nm.meta_key = 'newsletter_subscriber'
          AND nm.meta_value = %d",
        $this->id
      )
    );
        
    for( $i = 0; $i < count( $notifications ); $i++ ){
      $notifications[$i] = new Bop_Mail_Notification( $notifications[$i] );
    }
    
    return $notifications;
  }
  
  public function jsonSerialize(){
    $output = new StdClass();
    $output->id = $this->id;
    $output->user_id = $this->user_id;
    $output->created = $this->created;
    $output->email = $this->email;
    $output->group_id = $this->group_id;
    $output->status = $this->status;
    return apply_filters( 'json_serialize.bop_newsletters', $output, $this );
  }
  
}

endif; //class exists
