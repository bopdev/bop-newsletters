<?php $nl_groups = bop_newsletters_get_groups(); ?>
<div class="wrap">
  <h1>
    <?php _e( 'Bop Newsletter Groups', 'bop-newsletters' ) ?>
    <a href="<?php echo bop_newsletters_add_group_link() ?>" class="page-title-action"><?php _e( 'Add New' ) ?></a>
  </h1>
   
  <div class="newsletter-groups">
    <?php if( count( $nl_groups ) ): ?>
      <table class="widefat">
        <thead>
          <tr>
            <th><?php _e( 'Name', 'bop-newsletters' ) ?></th>
            <td><span class="sreen-reader-text"><?php _e( 'Actions', 'bop-newsletters' ) ?></span></td>
          </tr>
        </thead>
        <tbody>
          <?php foreach( $nl_groups as $group ): ?>
            <tr>
              <td><?php echo $group->labels['title'] ?></td>
              <td>
                <a href="<?php echo esc_url( $group->get_edit_link() ) ?>" class="btn"><?php _e( 'Edit', 'bop-newsletters' ) ?></a>
                <!--<form method="post" action="#">
                  <input type="hidden" name="object-type" value="newsletter_group">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?php echo $group->id ?>">
                  <button href="#"><?php _e( 'Delete', 'bop-newsletters' ) ?></button>
                </form>-->
              </td>
            </tr>
          <?php endforeach ?>
        </tbody>
        <tfoot></tfoot>
      </table>
    <?php endif ?>
  </div>
</div>
