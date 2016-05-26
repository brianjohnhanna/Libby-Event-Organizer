<br /><br />
<label for="groupType">Group Type*</label>
<select id="groupType" name="libby[group_type]" required>
  <?php foreach ( $group_types as $group_type ) : ?>
    <option value="<?php echo $group_type->term_id; ?>"><?php echo $group_type->name; ?></option>
  <?php endforeach; ?>
</select>
