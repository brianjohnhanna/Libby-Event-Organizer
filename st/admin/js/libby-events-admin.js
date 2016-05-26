(function( $ ) {
	'use strict';
	var file_frame, image_data;

	$(document).ready(function(){
		$( ".libby-meta-container" ).sortable();
		var $metaContainer = $('.libby-meta-container'),
				$addButton = $('a.add-row'),
				$removeButton = $('a.remove-row');

		$addButton.on('click', function(e){
			console.log('cicked');
			var $container = $(this).closest('.libby-metabox').find('.libby-meta-container'),
					$row = $container.find('.libby-row'),
					$newRow = $row.eq(0).clone();

			$newRow.find(':input').val('');
			$container.append($newRow);
		});

		$removeButton.on('click', function(e){
			var $container = $(this).closest('.libby-meta-container'),
					$rows = $container.find('.libby-row');
			if ($rows.length > 1) {
				$(this).closest('.libby-row').remove();
			}
		});

	});

	$('.open-media').on('click', function(){
		var $this = $(this);
		if ( undefined !== file_frame ) {

			file_frame.open();
			return;

		}
		file_frame = wp.media.frames.file_frame = wp.media({
			title:    "Insert Media",    // For production, this needs i18n.
			button:   {
				text: "Upload Diagram"     // For production, this needs i18n.
			},
			multiple: false
		});

		/**
		 * Setup an event handler for what to do when an image has been
		 * selected.
		 */
		file_frame.on( 'select', function() {

			image_data = file_frame.state().get( 'selection' ).first().toJSON();
			$this.siblings('input[type="hidden"]').val(image_data.id);
			$this.siblings('.diagram-meta').html('<a href="' + image_data.url + '" target="_blank">' + image_data.filename + '</a>');
			for ( var image_property in image_data ) {

				console.log( image_property + ': ' + image_data[ image_property ] );

			}

		});

		// Now display the actual file_frame
		file_frame.open();
	});

	var $branchSelect = $('#branch');
	$branchSelect.on('change', function(e){
		var branchId = $(this).val();
		if (branchId) {
			var data = {
				'action': 'get_branch_location',
				'branch_id': branchId
			};
			$.get(ajaxurl, data, function(response){
				var location = JSON.parse(response),
						address = location.address,
						addressParts = address.split(', ');
				$('input[name="eo_venue[address]"]').val(addressParts[0]);
				$('input[name="eo_venue[city]"]').val(addressParts[1]);
				$('input[name="eo_venue[state]"]').val(addressParts[2]).trigger('change');
			});
		}
		else {
			$('input[name="eo_venue[address]"]').val('');
			$('input[name="eo_venue[city]"]').val('');
			$('input[name="eo_venue[state]"]').val('').trigger('change');
		}

	});

	function setAddressParts(address) {

	}

})( jQuery );