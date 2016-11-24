<form method="post" action="#">
  <input type="hidden" name="action" value="bop_newsletters">
  <input type="hidden" name="object_type" value="subscriber">
  <input type="hidden" name="deed" value="add">
  <input type="hidden" name="user_id" value="{{{user.id}}">
  <table class="form-table">
    <tr>
      <th><?php _e( 'Newsletters', 'bop-newsletters' ) ?></th>
      <td>
        <select name="group_id[]" multiple>
          <# _.each(groups, function(group){ #>
            <option value="{{group.id}}"<#= _.contains( group.id, user.groups ) ? ' selected' : '' #>>{{group->labels->title}}</option>
          <# }); #>
        </select>
      </td>
    </tr>
  </table>
</form>
