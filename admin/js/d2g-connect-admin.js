(function ($) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	$( document ).ready(
		function () {
			// alert('super');
			$( '.remove_btn' ).click(
				function () {
					$( this ).parent().remove();

					return false;
				}
			);
			$( '.opener' ).click(
				function () {
					$( this ).toggleClass( 'active' );
					$( this ).next().slideToggle();

				}
			)
		}
	);
})( jQuery );


jQuery(document).ready(function($){
			
	$('.add_exp').click(function(e){
		e.preventDefault;
		var rowID = $(this).attr('data-entry-id');
		add_form_row('exp', rowID);
		return false;
	});

	$('.add_edu').click(function(e){
		e.preventDefault;
		var rowID = $(this).attr('data-entry-id');
		add_form_row('edu', rowID);
		return false;
	});

	$('.add_pub').click(function(e){
		e.preventDefault;
		var rowID = $(this).attr('data-entry-id');
		add_form_row('pub', rowID);
		return false;
	});
});
	

function add_form_row( type, rowID){
	var newRowID = parseInt(rowID) + 1;
	jQuery('.add_' + type).attr('data-entry-id', newRowID);
	if(type == 'edu' || type == 'exp'){
		var row = '<div class="row exp_edu ' + type +'_'+newRowID+'"><a class="remove_btn btn-add" href="#"><span class="icon-minus-circled"></span> </a>' +
			'<div class="col-sm-3"><div class="row"><div class="col-sm-6">' +
			'<input type="text" class="" id="d2g_exp_edu_start_date' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_start_date]" placeholder="' + myStrData.str_start + '"/>'+
			'</div><div class="col-sm-6"><input type="text" class="" id="d2g_exp_edu_end_date' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_end_date]" placeholder="' + myStrData.str_end + '"/>'+
			'</div></div></div>';
		if(type == 'edu'){
			
			row = row +
				'<div class="col-sm-3"><input type="text" class="" id="d2g_exp_edu_study' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_study]" placeholder="' + myStrData.str_study_area + '"/></div>' +
				'<div class="col-sm-3"><input type="text" class="" id="d2g_exp_edu_title' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_title]" placeholder="' + myStrData.str_degree + '"/></div>' +
				'<div class="col-sm-3"><input type="text" class="" id="d2g_exp_edu_org' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_org]" placeholder="' + myStrData.str_institution + '"/></div>' +
				'</div>';
		} else {
			row = row +
				'<div class="col-sm-3"><input type="text" class="" id="d2g_exp_edu_expertise" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_expertise]" placeholder="' + myStrData.str_expertise + '"/></div>' +
				'<div class="col-sm-3"><input type="text" class="" id="d2g_exp_edu_title" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_title]" placeholder="' + myStrData.str_position + '"/></div>' +
				'<div class="col-sm-3"><input type="text" class="" id="d2g_exp_edu_org' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_org]" placeholder="' + myStrData.str_organisation + '"/></div>' +
				'</div>';
		}
	} else {

		var row = '<div class="row exp_edu  ' + type +'_'+newRowID+'"><a class="remove_btn btn-add" href="#"><span class="icon-minus-circled"></span> </a>' +
			'<div class="col-sm-2"><input type="text" class="" id="d2g_pub_title' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_pub_title]" placeholder="' + myStrData.str_title + '"/></div>' +
			'<div class="col-sm-2"><input type="text" class="" id="d2g_pub_link' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_pub_link]" placeholder="' + myStrData.str_web_link + '"/></div>' +
			'<div class="col-sm-2"><input type="text" class="" id="d2g_pub_journal' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_pub_journal]" placeholder="' + myStrData.str_journal + '"/></div>' +
			'<div class="col-sm-2"><input type="text" class="" id="d2g_pub_type' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_pub_type]" placeholder="' + myStrData.str_type_of_publication + '"/></div>' +
			'<div class="col-sm-2"><input type="text" class="" id="d2g_pub_author' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_pub_author]" placeholder="' + myStrData.str_author+ '"/></div>' +
			'<div class="col-sm-2"><input type="text" class="" id="d2g_pub_date' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_pub_date]" placeholder="' + myStrData.str_publication_date + '"/></div>' +
			'</div>';
	}


	jQuery('.' + type + '_wrapper').append(row);

	jQuery('.remove_btn').bind('click', function(){
		jQuery(this).parent().remove();
		return false;
	});

}


jQuery(document).ready(function($){
	var custom_uploader
	, click_elem = jQuery('.wpse-228085-upload')
	, target = jQuery('.wrap input[name="d2g_placeholder"]')

	click_elem.click(function(e) {
		e.preventDefault();
		//If the uploader object has already been created, reopen the dialog
		if (custom_uploader) {
			custom_uploader.open();
			return;
		}
		//Extend the wp.media object
		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Image',
			button: {
				text: 'Choose Image'
			},
			multiple: false
		});
		//When a file is selected, grab the URL and set it as the text field's value
		custom_uploader.on('select', function() {
			attachment 	= custom_uploader.state().get('selection').first().toJSON();
			console.log(attachment);
			var attUrl	= attachment.id;
			target.val(attUrl);
		});
		//Open the uploader dialog
		custom_uploader.open();
	});      
});

jQuery(document).ready(function($){
	var custom_uploader2
	, click_elem = jQuery('.wpse-upload')
	, target = jQuery('.wrap input[name="d2g_logo"]')

	click_elem.click(function(e) {
		e.preventDefault();
		//If the uploader object has already been created, reopen the dialog
		if (custom_uploader2) {
			custom_uploader2.open();
			return;
		}
		//Extend the wp.media object
		custom_uploader2 = wp.media.frames.file_frame = wp.media({
			title: 'Choose Image',
			button: {
				text: 'Choose Image'
			},
			multiple: false
		});
		//When a file is selected, grab the URL and set it as the text field's value
		custom_uploader2.on('select', function() {
			attachment 	= custom_uploader2.state().get('selection').first().toJSON();
			console.log(attachment);
			var attUrl	= attachment.id;
			target.val(attUrl);
		});
		//Open the uploader dialog
		custom_uploader2.open();
	});      
});