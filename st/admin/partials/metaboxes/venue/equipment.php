<?php
/**
 * The display for the eqipment meta box on the venue edit screen.
 */
?>
<div class="libby-metabox">
	<div class="libby-meta-container">
		<?php foreach ($meta as $meta_values): ?>
			<div class="libby-row">
				<header>Equipment</header>
				<div class="field">
					<label for="name">Name</label>
					<input type="text" name="libby[_equipment][name][]" id="name" value="<?php echo isset($meta_values['name']) ? $meta_values['name'] : ''; ?>" />
				</div>
				<div class="field">
					<label for="description">Description</label>
					<textarea name="libby[_equipment][description][]" id="description"><?php echo isset($meta_values['description']) ? $meta_values['description'] : ''; ?></textarea>
				</div>
				<footer>
					<a class="remove-row button" <?php if ( count( $meta_values ) === 0 ) echo 'disabled'; ?>>Remove Equipment</a>
				</footer>
			</div>
		<?php endforeach; ?>
	</div>
	<a class="add-row button">Add More Equipment</a>
</div>
