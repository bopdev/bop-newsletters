<?php 
global $user_id;
$user_groups = bop_newsletters_get_users_newsletter_group_ids( $user_id );
$groups = apply_filters( 'users_available_groups.bop_newsletters', bop_newsletters_get_groups(), $user_id );
?>
<script type="template/underscorejs" id="edit-subscriber-template"><?php 
  include apply_filters( 'user_newsletter_choose_template.bop_newsletters', bop_newsletters_plugin_path( 'templates/admin/parts/newsletter-choose.php' ) );
?></script>
<script type="application/json" id="groups"><?php 
  echo json_encode( $groups );
?></script>
<script type="application/json" id="user"><?php 
  echo json_encode( ['id'=>$user_id, 'groups'=>$user_groups] );
?></script>
