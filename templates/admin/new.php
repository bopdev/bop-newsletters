<div class="wrap">
  <h1>
    <?php _e( 'Add Newsletter Group', 'bop-newsletters' ) ?>
  </h1>
  
  <form action="<?php echo 'admin.php?page=bop-newsletters' ?>" method="post">
    <input type="hidden" name="action" value="bop_newsletters">
    <input type="hidden" name="object_type" value="group">
    <input type="hidden" name="deed" value="add">
    <div id="poststuff">
      <div id="titlediv">
        <div id="titlewrap">
          <label for="title"><?php _e( 'Title: ', 'bop-newsletters' ) ?></label>
          <input type="text" name="title" size="30" value="" id="title" spellcheck="true" autocomplete="off" placeholder="<?php _e( 'Enter Title Here' ) ?>">
        </div>
      </div>
    </div>
    <?php submit_button( __( 'Add', 'bop-newsletters' ) ) ?>
  </form>
</div>
