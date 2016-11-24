<?php 

//Reject if accessed directly
defined( 'ABSPATH' ) || die( 'Our survey says: ... X.' );

if( ! class_exists( 'Bop_Newsletter_Group' ) ):

class Bop_Newsletter_Group implements JSONSerializable{
  
  public $id;
  
  public $labels = [];
  
  public $settings = [];
  
  public $subscribers = [];
  
  protected $parsed_vars = [];
  
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
      $this->subscribers = $subs;
    }
  }
  
  protected function _fetch_from_db( $id ){
    $group_definition = get_option( 'bop_newsletter_group_' . $id, false );
    if( $group_definition != false ){
      $this->fill_object( array_merge( $group_definition, ['id'=>$id] ) );
    }
  }
  
  public function parse_subscribers_vars( $vars = [] ){
    $dummy_subscriber = new Bop_Newsletter_Subscriber( ['group'=>$this->id] );
    
    $vars['limit'] = isset( $vars['limit'] ) ? $vars['limit'] : 0;
    $vars['offset'] = isset( $vars['offset'] ) ? $vars['offset'] : 0;
    $vars['status'] = isset( $vars['status'] ) ? (array)$vars['status'] : [$dummy_subscriber->get_default_status()];
    $this->parsed_vars = $vars;
    return $vars;
  }
  
  public function get_subscribers( $vars = [] ){
    global $wpdb;
    
    $vars = $this->parse_subscribers_vars( $vars );
    
    if( ! $this->subscribers || $vars != $this->parsed_vars ){
      $sql = "SELECT t.subscriber_id AS id,
        t.user_id AS user_id,
        t.created AS created,
        t.email AS email,
        t.group_id AS group_id,
        t.status AS status
      FROM {$wpdb->bop_newsletters_subscribers} AS t
      WHERE t.group_id = %s
        AND t.status IN (" . implode( ", ", array_fill( 0, count( $vars['status'] ), "%s" ) ) . ")";
      
      $sql_pieces = [$this->id];
      $sql_pieces = array_merge( $sql_pieces, $vars['status'] );
      
      if( $vars['limit'] > 0 ){
        $sql .= "\nLIMIT %d\nOFFSET %d";
        $sql_pieces[] = $vars['limit'];
        $sql_pieces[] = $vars['offset'];
      }
      
      $subs = $wpdb->get_results( $wpdb->prepare( $sql, $sql_pieces ), ARRAY_A );
      $this->fill_object( ['subscribers'=>$subs] );
    }
    
    return $this->subscribers;
  }
  
  public function insert(){
    if( ! $this->id )
      return false;
    
    return add_option( 'bop_newsletter_group_' . $this->id, ['labels'=>$this->labels, 'settings'=>$this->settings] );
  }
  
  public function update(){
    if( ! $this->id )
      return false;
    
    return update_option( 'bop_newsletter_group_' . $this->id, ['labels'=>$this->labels, 'settings'=>$this->settings] );
  }
  
  public function delete(){
    if( ! $this->id )
      return false;
    
    return delete_option( 'bop_newsletter_group_' . $this->id );
  }
  
  public function send_email( $template_id ){
    
    if( ! $this->id || ! $template_id ) return;
    
    $subs = apply_filters( 'send_email_subscribers.bop_newsletters', $this->get_subscribers(), $template_id, $this );
    
    foreach( $subs as $sub ){
      $sub->add_notification( $template_id );
    }
  }
  
  public function get_edit_link(){
    return apply_filters( 'get_edit_group_link.bop_newsletters', admin_url( 'admin.php?page=bop-newsletter-edit&id=' . $this->id ), $this );
  }
  
  public function jsonSerialize(){
    $output = new StdClass();
    $output->id = $this->id;
    $output->labels = $this->labels;
    $output->subscribers = $this->subscribers;
    return apply_filters( 'json_serialize.bop_newsletters', $output, $this );
  }
}

endif;
