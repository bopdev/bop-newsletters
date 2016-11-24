<tr class="subscriber-row status-{{subscriber.status}}" data-id="{{subscriber.id}}">
  <th scope="row" class="check-column"><input form="subscribers-bulk-action" type="checkbox" name="ids[]" value="{{subscriber.id}}"></th>
  <td data-colname="Email">
    <strong>{{subscriber.email}}</strong>
    <div class="row-actions">
      <span class="inline">
        <a href="#" class="edit" aria-label="<?php echo esc_attr( __( 'Edit “{{subscriber.email}}”' ) ) ?>"><?php _e( 'Edit' ) ?></a>
      </span>
    </div>
  </td>
  <td data-colname="Status">{{subscriber.status}}</td>
</tr>
