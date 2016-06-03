<?php
/**
 * The display for the eqipment meta box on the venue edit screen.
 */
?>
<div class="libby-metabox">
	<div class="libby-meta-container">
		<?php foreach ($meta as $meta_values): ?>
			<div class="libby-row">
				<header>Setup</header>
				<div class="field">
					<label for="name">Name</label>
					<input type="text" name="libby[_setup_options][name][]" id="name" value="<?php echo isset($meta_values['name']) ? $meta_values['name'] : ''; ?>" />
				</div>
				<div class="field">
					<label for="description">Description</label>
					<textarea name="libby[_setup_options][description][]" id="description"><?php echo isset($meta_values['description']) ? $meta_values['description'] : ''; ?></textarea>
				</div>
				<div class="field">
					<label for="diagram">Diagram</label>
					<span class="diagram-meta"></span>
					<input type="hidden" class="diagram" name="libby[_setup_options][diagram][]" value="<?php echo isset($meta_values['diagram']) ? $meta_values['diagram'] : ''; ?>" />
					<a class="button open-media">Upload or Choose from Library</a>

				</div>
				<footer>
					<a class="remove-row button">Remove Setup Option</a>
				</footer>
			</div>
		<?php endforeach; ?>
	</div>
	<a class="add-row button">Add Setup Option</a>
</div>
