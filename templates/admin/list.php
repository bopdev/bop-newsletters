<?php 

$nl_groups = bop_newsletters_get_groups();

?>
<div class="wrap">
  <h1>
    <?php _e( 'Bop Newsletter Groups', 'bop-newsletters' ) ?>
    <a href="?page=bop-newsletter-new" class="page-title-action"><?php _e( 'Add New' ) ?></a>
  </h1>
   
  <div class="newsletter-groups">
    <table></table>
  </div>
</div>
