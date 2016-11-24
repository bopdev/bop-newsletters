<div class="newsletter_subscribe">
  <form method="post" action="#">
    <input type="hidden" name="action" value="add_subscriber">
    <input type="hidden" name="group" value="<?php echo $group->id ?>">
    <label for="email"><?php _e( 'Email: ', 'bop-newsletters' ) ?></label>
    <input type="email" name="email" placeholder="<?php echo esc_attr( __( 'Enter your email address', 'bop-newsletters' ) ) ?>">
    <?php submit_button( __( 'Subscribe', 'bop-newsletters' ) ) ?>
  </form>
</div>
