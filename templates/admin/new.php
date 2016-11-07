<div class="wrap">
  <h1>
    <?php _e( 'Add Newsletter Group', 'bop-newsletters' ) ?>
  </h1>
  
  <form action="#" method="post">
    <input type="hidden" name="action" value="add">
    <div id="poststuff">
      <div id="titlediv">
        <div id="titlewrap">
          <label class="" id="title-prompt-text" for="title"><?php _e( 'Enter title here' ) ?></label>
          <input type="text" name="title" size="30" value="" id="title" spellcheck="true" autocomplete="off">
        </div>
      </div>
    </div>
    <?php submit_button( __( 'Add', 'bop-newsletters' ) ) ?>
  </form>
  <?php  ?>
</div>
