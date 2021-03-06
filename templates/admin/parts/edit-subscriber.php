<tr class="edit-subscriber-row">
  <td colspan="2" class="colspanchange">
    <form method="post" action="#">
      <input type="hidden" name="action" value="bop_newsletters">
      <input type="hidden" name="object_type" value="subscriber">
      <input type="hidden" name="deed" value="edit">
      <input type="hidden" name="group_id" value="{{group.id}}">
      <input type="hidden" name="id" value="{{subscriber.id}}">
      <fieldset class="inline-edit-col-left">
        <div class="inline-edit-col">
          <label>
            <span class="title"><?php _e( 'Email', 'bop-newsletters' ) ?></span>
            <span class="input-text-wrap"><input type="email" name="email" value="{{subscriber.email}}"></span>
          </label>
        </div>
      </fieldset>
      <fieldset class="inline-edit-col-right">
        <div class="inline-edit-col">
          <label>
            <span class="title"><?php _e( 'Status', 'bop-newsletters' ) ?></span>
            <select name="status">
              <# _.each( statuses.valid_statuses, function( meta, status ){ #>
                <option value="{{status}}"<# print(status == subscriber.status ? ' selected' : '') #>>{{meta.labels.general}}</option>
              <# }); #>
            </select>
          </label>
        </div>
      </fieldset>
      <p class="submit inline-edit-save">
        <button type="button" class="button-secondary cancel"><?php _e( 'Cancel' ) ?></button>
        <button type="button" class="button-primary save"><?php _e( 'Update' ) ?></button>
        <span class="spinner"></span>
        <span class="error" style="display:none"></span>
        <br class="clear">
      </p>
    </form>
  </td>
</tr>
