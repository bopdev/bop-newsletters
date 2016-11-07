<?php 
//Don't worry, it's already been checked
$group = new Bop_Newsletter_Group( $_GET['id'] );

$subs_page = isset( $_GET['subscribers_page'] ) ? (int)$_GET['subscribers_page'] : 1;
$subs_limit = isset( $_GET['subscribers_page_length'] ) ? (int)$_GET['subscribers_page_length'] : 25;

$subs_offset = ( $subs_page - 1 ) * $subs_page_length;
?>
<div class="wrap">
  <h1>
    <?php _e( 'Edit Newsletter Group', 'bop-newsletters' ) ?>
  </h1>
  
  <form action="#" method="post">
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="id" value="<?php echo esc_attr( $group->id ) ?>">
    <div id="poststuff">
      <div id="titlediv">
        <div id="titlewrap">
          <label class="" id="title-prompt-text" for="title"><?php _e( 'Enter title here' ) ?></label>
          <input type="text" name="title" size="30" value="" id="title" spellcheck="true" autocomplete="off" value="<?php echo esc_attr( $group->id ) ?>">
        </div>
      </div>
    </div>
    <?php submit_button( __( 'Save', 'bop-newsletters' ) ) ?>
  </form>
  
  <div class="subscribers">
    <table class="widefat">
      <thead><th>EMAIL</th></thead>
      <tbody>
        <?php foreach( $group->get_subscribers( ['limit'=>$subs_limit, 'offset'=>$subs_offset] ) as $sub ): ?>
          <tr>
            <td><?php echo $sub->email ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
      <tfoot></tfoot>
    </table>
  </div>
</div>
