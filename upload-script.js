jQuery(document).ready(function() {

	var formfield;
	rowString = jQuery('.table-row:last').attr('id');
	rowNumber = rowString[rowString.length - 1];
	rowNumber++;
	
	jQuery( '.add-button' ).click( function () {
		var newRow = jQuery('<tr />').attr('id', 'row' + rowNumber);
		newRow.html('<td><p><label for="upload_image" style="font-weight:bold;">Image ' + rowNumber + '</label><br /><input id="upload_image_' + rowNumber + '" type="text" size="80" name="upload_image_' + rowNumber + '" value="" /><br /><input class="btn_upload_image" id="upload_image_button_' + rowNumber + '" type="button" value="Upload Image" /></p><label for="title_image" style="font-weight:bold;">Title image ' + rowNumber + '</label><br /><input id="upload_title_' + rowNumber + '" type="text" size="40" name="upload_title_' + rowNumber + '" value="" /></td>');
		jQuery('#catapultGalleryTable').append(newRow);
		rowNumber++;
	});
	
	//Original uploader script from here: http://www.webmaster-source.com/2010/01/08/using-the-wordpress-uploader-in-your-plugin-or-theme/#comment-11048
	jQuery('.btn_upload_image').live( "click", function() {
		btnName = event.target.id;
		btnLen = btnName.length;
		if ( btnLen == 21 ) {
			btnNum = btnName[20];
		} else {
			btnNum = '' + btnName[20] + btnName[21];
		}
		formfield = jQuery('#upload_image_' + btnNum).attr('name');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		return false;
	});
	
	//Found the following fix here: http://austinpassy.com/snippets/wordpress/creating-custom-metaboxes-and-the-built-in-uploader/
	window.original_send_to_editor = window.send_to_editor;
	window.send_to_editor = function(html) {
		if ( formfield ) {
			imgurl = jQuery('img',html).attr('src');
			jQuery ( '#' + formfield).val(imgurl);
			tb_remove();
		} else {
			window.original_send_to_editor ( html );
		}
	}

});