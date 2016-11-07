<?php 

//Reject if accessed directly
defined( 'ABSPATH' ) || die( 'Our survey says: ... X.' );

if( ! class_exists( 'Bop_Newsletter_Group' ) ):

class Bop_Newsletter_Group{
  
  public $id = 'default';
  
  public $labels = [];
  
  public $settings = [];
  
  public $subscribers = [];
  
  protected $parsed_vars = [];
  
  public function __construct( $id_or_data = 0 ){
    if( $id_or_data ){
      if( is_array( $id_or_data ) || is_object( $id_or_data ) ){
        $this->fill_object( (array)$id_or_data );
      }else{
        $this->load( $id );
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
      $this->id = $data['id'];
    }
    
    if( isset( $data['labels'] ) ){
      $this->labels = $data['labels'];
    }
    
    if( isset( $data['settings'] ) ){
      $this->settings = $data['settings'];
    }
    
    if( isset( $data['subscribers'] ) ){
      $subs = [];
      foreach( $data['subscribers'] as $sub ){
        if( is_object( $sub ) && is_a( $sub, 'Bop_Newsletter_Subscriber' ) ){
          $subs[] = $sub;
        }else{
          $subs[] = new Bop_Newsletter_Subscriber( $sub );
        }
      }
    }
  }
  
  protected function _fetch_from_db( $id ){
    $group_definition = get_option( 'bop_newsletter_group_' . $id, false );
    if( $group_definition != false ){
      $this->fill_object( $group_definition );
    }
  }
  
  public function parse_subscribers_vars( $vars = [] ){
    $vars['limit'] = isset( $vars['limit'] ) ? $vars['limit'] : 0;
    $vars['offset'] = isset( $vars['offset'] ) ? $vars['offset'] : 0;
    $vars['status'] = isset( $vars['status'] ) ? (array)$vars['status'] : [$this->get_default_status()];
    $this->parsed_vars = $vars;
    return $vars;
  }
  
  public function get_subscribers( $vars = [] ){
    global $wpdb;
    
    $vars = $this->parse_subscribers_vars( $vars );
    
    if( ! $this->subscribers || $vars != $this->parsed_vars ){
      $sql_pieces = [$this->id];
      $sql = "SELECT t.subscriber_id AS id,
        t.user_id AS user_id
        t.created AS created,
        t.email AS email,
        t.group AS group,
        t.status AS status
      FROM {$wpdb->bop_newsletters_subscribers} AS t
      WHERE t.group = %s
        AND t.status IN (" . implode( ", ", array_fill( 0, count( $vars['status'] ), "%d" ) ) . ")";
      
      if( $limit > 0 ){
        $sql_pieces[] = $offset;
        $sql_pieces[] = $limit;
        $sql .= "\nLIMIT %d, %d";
      }
      
      $subs = $wpdb->get_results( $wpdb->prepare( $sql, $sql_pieces ) );
      $this->fill_object( $subs );
    }
    
    return $this->subscribers;
  }
  
  public function insert(){
    if( $this->id )
      add_option( 'bop_newsletter_group_' . $this->id, ['labels'=>$this->labels, 'settings'=>$this->settings] );
      
    return $this;
  }
  
  public function update(){
    if( $this->id )
      update_option( 'bop_newsletter_group_' . $this->id, ['labels'=>$this->labels, 'settings'=>$this->settings] );
      
    return $this;
  }
  
  public function delete(){
    if( $this->id )
      delete_option( 'bop_newsletter_group_' . $this->id );
      
    return $this;
  }
  
  public function send_email( $template_id ){
    
    if( ! $group->id || ! $template_id ) return;
    
    $subs = apply_filters( 'send_email_subscribers.bop_newsletters', $this->get_subscribers(), $template_id, $this );
    
    foreach( $subs as $sub ){
      $bmn = new Bop_Mail_Notification( ['template_id'=>$template_id, 'to_address'=>$sub->email] );
      $bmn->insert();
    }
  }
  
}

endif;
