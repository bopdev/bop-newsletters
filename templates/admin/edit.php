<?php 
//Don't worry, it's already been checked
$group = new Bop_Newsletter_Group( $_GET['id'] );

$dummy_subscriber = new Bop_Newsletter_Subscriber( ['group'=>$group->id] );

//get available filters for page
$subs_page_lengths = apply_filters( 'available_subscribers_page_lengths.bop_newsletters', [25, 50, 100, 200] );
$bulk_actions = apply_filters( 'edit_subscribers_bulk_actions.bop_newsletters', ['unsubscribe'=>__( 'Unsubscribe', 'bop-newsletters' )] );

//get chosen filters for page
$subs_page = isset( $_GET['subscribers_page'] ) ? (int)$_GET['subscribers_page'] : 1;

if( isset( $_GET['subscribers_page_length'] ) ){
  $subs_limit = (int)$_GET['subscribers_page_length'];
  $subs_limit = in_array( $subs_limit, $subs_page_lengths ) ? $subs_limit : $subs_page_lengths[0];
}else{
  $subs_limit = $subs_page_lengths[0];
}

$subs_offset = ( $subs_page - 1 ) * $subs_limit;

$subs_status_filter = isset( $_GET['subscribers_status'] ) && in_array( $_GET['subscribers_status'], array_keys( $dummy_subscriber->get_valid_statuses() ) ) ? $_GET['subscribers_status'] : $dummy_subscriber->get_default_status();

//get subscribers
$group->get_subscribers( ['limit'=>$subs_limit, 'offset'=>$subs_offset, 'status'=>$subs_status_filter] );
?>
<div id="edit-newsletter-group" class="wrap">
  <h1>
    <?php _e( 'Edit Newsletter Group', 'bop-newsletters' ) ?>
  </h1>
  
  <form action="#" method="post">
    <input type="hidden" name="action" value="bop_newsletters">
    <input type="hidden" name="object_type" value="group">
    <input type="hidden" name="deed" value="edit">
    <input type="hidden" name="id" value="<?php echo $group->id ?>">
    <div id="poststuff">
      <div id="titlediv">
        <div id="titlewrap">
          <label for="title"><?php _e( 'Title: ', 'bop-newsletters' ) ?></label>
          <input type="text" name="title" size="30" value="<?php echo esc_attr( $group->labels['title'] ) ?>" id="title" spellcheck="true" autocomplete="off" placeholder="<?php _e( 'Enter Title Here' ) ?>">
        </div>
      </div>
    </div>
    <?php submit_button( __( 'Edit', 'bop-newsletters' ) ) ?>
  </form>
  
  <div class="subscribers">
    <div class="tablenav top">
      <?php /*<div class="alignleft actions bulkactions">
        <form id="subscribers-bulk-action" method="post" action="#">
          <input type="hidden" name="action" value="subscribers_bulk_action">
          <input type="hidden" name="group" value="<?php echo $group->id ?>">
          <label for="bulk_action"><?php _e( 'Bulk Action: ', 'bop-newsletters' ) ?></label>
          <select name="bulk_action">
            <?php foreach( $bulk_actions as $bulk_action=>$label ): ?>
              <option value="<?php echo $bulk_action ?>"><?php echo $label ?></option>
            <?php endforeach ?>
          </select>
          <button type="submit"><?php _e( 'Apply' ) ?></button>
        </form>
      </div> */ ?>
      <div class="alignleft actions">
        <form method="get" action="#">
          <input type="hidden" name="page" value="bop-newsletter-edit">
          <input type="hidden" name="id" value="<?php echo $group->id ?>">
          <label for="subscribers_page_length" class="alignleft"><?php _e( 'Per Page: ', 'bop-newsletters' ) ?></label>
          <select name="subscribers_page_length">
            <?php foreach( $subs_page_lengths as $length ): ?>
              <option value="<?php echo $length ?>"<?php echo $length == $subs_limit ? ' selected' : ''; ?>><?php echo $length ?></option>
            <?php endforeach ?>
          </select>
          <label for="subscribers_status" class="alignleft"><?php _e( 'Status: ', 'bop-newsletters' ) ?></label>
          <select name="subscribers_status">
            <?php foreach( $dummy_subscriber->get_valid_statuses() as $status=>$status_meta ): ?>
              <option value="<?php echo $status ?>"<?php echo $status == $subs_status_filter ? ' selected' : ''; ?>><?php echo $status_meta['labels']['general'] ?></option>
            <?php endforeach ?>
          </select>
          <button type="submit"><?php _e( 'Apply' ) ?></button>
        </form>
      </div>
    </div>
    <table class="widefat subscribers-table striped">
      <thead>
        <tr>
          <td class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Select All' ) ?></label><input id="cb-select-all-1" type="checkbox"></td>
          <th><?php _e( 'Email', 'bop-newsletters' ) ?></th>
          <th><?php _e( 'Status', 'bop-newsletters' ) ?></th>
        </tr>
      </thead>
      <tbody>
      </tbody>
      <tfoot>
        <tr>
          <td class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-2"><?php _e( 'Select All' ) ?></label><input id="cb-select-all-2" type="checkbox"></td>
          <th><?php _e( 'Email', 'bop-newsletters' ) ?></th>
          <th><?php _e( 'Status', 'bop-newsletters' ) ?></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
<script type="text/html" id="tmpl-subscriber-row"><?php 
  include apply_filters('subscriber_row_template.bop_newsletters', bop_newsletters_plugin_path( 'templates/admin/parts/subscriber-row.php' ) );
?></script>
<script type="text/html" id="tmpl-edit-subscriber"><?php 
  include apply_filters( 'edit_subscriber_template.bop_newsletters', bop_newsletters_plugin_path( 'templates/admin/parts/edit-subscriber.php' ) );
?></script>
<script type="text/html" id="tmpl-add-subscriber"><?php 
  include apply_filters('new_subscriber_template.bop_newsletters', bop_newsletters_plugin_path( 'templates/admin/parts/add-subscriber.php' ) );
?></script>
<script type="text/html" id="tmpl-add-subscriber-btn"><?php 
  include apply_filters('add_subscriber_btn_template.bop_newsletters', bop_newsletters_plugin_path( 'templates/admin/parts/add-subscriber-btn.php' ) );
?></script>
<script type="application/json" id="statuses-json"><?php 
  echo json_encode( ['default_status'=>$dummy_subscriber->get_default_status(), 'valid_statuses'=>$dummy_subscriber->get_valid_statuses()] );
?></script>
<script type="application/json" id="group-json"><?php 
  echo json_encode( $group );
?></script>
