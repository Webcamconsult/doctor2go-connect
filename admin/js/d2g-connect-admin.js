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
	, target = jQuery('.wrap input[name="d2gc_placeholder"]')

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
	, target = jQuery('.wrap input[name="d2gc_logo"]')

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



/* ########################################################################## */
/* SETTINGS PAGE: TRANSLATIONS OF DOCTOR PROFILES                             */
/* ########################################################################## */
// Check: D2GC_Doctor_Sync::ajax_check (read-only). Align: D2GC_Doctor_Sync::ajax_align in batches of 10.
jQuery(function ($) {
	var $box = $('#d2gc-translations');
	if (!$box.length || typeof myStrData === 'undefined') return;

	var s        = myStrData;
	var nonce    = $box.data('nonce');
	var $check   = $('#d2gc-translations-check');
	var $align   = $('#d2gc-translations-align');
	var $spinner = $('#d2gc-translations-spinner');
	var $result  = $('#d2gc-translations-result');
	var pending  = []; // sources of the doctors with differences

	var format = function (str) {
		var args = Array.prototype.slice.call(arguments, 1);
		return String(str).replace(/%(\d)\$d/g, function (m, i) { return args[i - 1]; }).replace(/%d/, args[0]);
	};

	var setBusy = function (busy) {
		$check.prop('disabled', busy);
		$align.prop('disabled', busy || !pending.length);
		$spinner.toggleClass('is-active', busy);
	};

	var showError = function () {
		$result.empty().append($('<div class="notice notice-error inline"><p></p></div>').find('p').text(s.tr_error).end());
	};

	var render = function (data) {
		pending = $.map(data.doctors, function (d) { return d.source; });
		$result.empty();

		if (!pending.length) {
			$result.append($('<div class="notice notice-success inline"><p></p></div>').find('p').text(s.tr_none).end());
			return;
		}

		$result.append($('<p><strong></strong></p>').find('strong').text(format(s.tr_summary, pending.length, data.total)).end());
		var $list = $('<ul class="d2gc-translations-list"></ul>');
		$.each(data.doctors, function (i, d) {
			var $li = $('<li></li>');
			$li.append($('<a target="_blank"></a>').attr('href', d.edit_link).text(d.title));
			$li.append(document.createTextNode(' (' + d.language + ', ' + format(s.tr_languages, d.languages) + ') ' + s.tr_differs_in + ' ' + d.differences.join(', ') + ' '));
			$li.append($('<a href="#" class="d2gc-translations-align-one"></a>').attr('data-source', d.source).text(s.tr_align_one));
			$list.append($li);
		});
		$result.append($list);
	};

	var check = function () {
		setBusy(true);
		return $.post(s.ajax_url, { action: 'd2gc_translations_check', nonce: nonce })
			.done(function (r) { if (r && r.success) { render(r.data); } else { showError(); } })
			.fail(showError)
			.always(function () { setBusy(false); });
	};

	// align the given sources in batches of 10, then check again
	var align = function (sources) {
		var total = sources.length, done = 0, queue = sources.slice();
		setBusy(true);

		var next = function () {
			if (!queue.length) { check(); return; }
			var batch = queue.splice(0, 10);
			$result.empty().append($('<p></p>').text(format(s.tr_progress, done, total)));
			$.post(s.ajax_url, { action: 'd2gc_translations_align', nonce: nonce, sources: batch })
				.done(function (r) {
					if (!r || !r.success) { showError(); setBusy(false); return; }
					done += batch.length;
					next();
				})
				.fail(function () { showError(); setBusy(false); });
		};
		next();
	};

	$check.on('click', check);
	$align.on('click', function () { if (pending.length) align(pending); });
	$result.on('click', '.d2gc-translations-align-one', function (e) {
		e.preventDefault();
		align([parseInt($(this).data('source'), 10)]);
	});
});


/* ########################################################################## */
/* SETTINGS PAGE: TABS                                                        */
/* ########################################################################## */
// The tabs only show/hide panels of ONE form, so "Save" always saves every setting (options.php empties options
// that are missing in the form). The active tab is remembered, because options.php redirects without the #hash.
jQuery(function ($) {
	var $wrap = $('.d2gc-settings');
	if (!$wrap.length) return;

	var $tabs   = $wrap.find('.d2gc-tabs .nav-tab');
	var key     = 'd2gc_settings_tab';
	var store   = {
		get: function () { try { return window.localStorage.getItem(key); } catch (e) { return null; } },
		set: function (v) { try { window.localStorage.setItem(key, v); } catch (e) {} }
	};

	var show = function (tab) {
		if (!$tabs.filter('[data-tab="' + tab + '"]').length) tab = $tabs.first().data('tab');
		$tabs.removeClass('nav-tab-active').attr('aria-selected', 'false')
			.filter('[data-tab="' + tab + '"]').addClass('nav-tab-active').attr('aria-selected', 'true');
		$wrap.find('.d2gc-panel').removeClass('is-active').filter('[data-tab="' + tab + '"]').addClass('is-active');
		// nothing to save on the translations tab (AJAX tool outside the form)
		$wrap.toggleClass('d2gc-no-save', 'translations' === tab);
		store.set(tab);
	};

	$wrap.addClass('d2gc-js');
	show((window.location.hash || '').replace('#', '') || store.get());

	$tabs.on('click', function (e) {
		e.preventDefault();
		var tab = $(this).data('tab');
		show(tab);
		if (window.history && window.history.replaceState) window.history.replaceState(null, '', '#' + tab);
	});
});


/* ########################################################################## */
/* DOCTOR EDIT SCREEN: TABS                                                   */
/* ########################################################################## */
// Tab bar from D2gConnect_Admin::d2gc_doctor_edit_tabs(). Only shows/hides the boxes and the editor of the one post
// form, so "Update" always saves everything. Boxes of other plugins and the side column are not part of a tab.
jQuery(function ($) {
	var $nav = $('.d2gc-doctor-tabs');
	if (!$nav.length) return;

	var map = {
		personal:       ['#personal-info'],
		bio:            ['#postdivrich', '#postexcerpt'],
		experience:     ['#education', '#work', '#publication'],
		prices:         ['#d2gc-prices'],
		questionnaires: ['#d2gc-questionnaires'],
		availability:   ['#d2gc-availability']
	};
	var all     = $.map(map, function (ids) { return ids; }).join(',');
	var key     = 'd2gc_doctor_tab';
	var current = null;
	var loaded  = false; // the editor is only hidden after window load, so TinyMCE initialises with the right size

	var store = {
		get: function () { try { return window.localStorage.getItem(key); } catch (e) { return null; } },
		set: function (v) { try { window.localStorage.setItem(key, v); } catch (e) {} }
	};

	var show = function (tab) {
		if (!map[tab]) tab = 'personal';
		current = tab;

		$(all).not(loaded ? '' : '#postdivrich').addClass('d2gc-tab-hidden');
		$(map[tab].join(',')).removeClass('d2gc-tab-hidden');

		$nav.find('.nav-tab').removeClass('nav-tab-active').attr('aria-selected', 'false')
			.filter('[data-tab="' + tab + '"]').addClass('nav-tab-active').attr('aria-selected', 'true');
		store.set(tab);

		if ('bio' === tab) {
			// the editor was hidden: let WordPress/TinyMCE recalculate its size
			setTimeout(function () {
				$(window).trigger('resize');
				if (window.tinymce && window.tinymce.get('content')) {
					window.tinymce.get('content').execCommand('mceRepaint');
				}
			}, 0);
		}
	};

	$nav.on('click', '.nav-tab', function (e) {
		e.preventDefault();
		show($(this).data('tab'));
	});

	show(store.get());
	$(window).on('load', function () {
		loaded = true;
		show(current);
	});
});


/* ########################################################################## */
/* SETTINGS PAGE: MANUAL AVAILABILITY SYNC (Doctors tab)                      */
/* ########################################################################## */
// D2gConnect_Cron::ajax_list, then D2gConnect_Cron::ajax_run in batches of 3 (each doctor is one call to doctor2go.online).
jQuery(function ($) {
	var $box = $('#d2gc-availability-sync');
	if (!$box.length || typeof myStrData === 'undefined') return;

	var s        = myStrData;
	var nonce    = $box.data('nonce');
	var $run     = $('#d2gc-availability-sync-run');
	var $spinner = $('#d2gc-availability-sync-spinner');
	var $result  = $('#d2gc-availability-sync-result');

	var format = function (str) {
		var args = Array.prototype.slice.call(arguments, 1);
		return String(str).replace(/%(\d)\$d/g, function (m, i) { return args[i - 1]; }).replace(/%d/, args[0]);
	};
	var notice = function (type, text) {
		return $('<div class="notice inline"><p></p></div>').addClass('notice-' + type).find('p').text(text).end();
	};
	var setBusy = function (busy) {
		$run.prop('disabled', busy);
		$spinner.toggleClass('is-active', busy);
	};
	var fail = function () { $result.empty().append(notice('error', s.tr_error)); setBusy(false); };

	$run.on('click', function () {
		setBusy(true);
		$result.empty();
		$.post(s.ajax_url, { action: 'd2gc_availability_sync_list', nonce: nonce })
			.done(function (r) {
				if (!r || !r.success) { fail(); return; }
				var queue = r.data.doctors.slice(), total = queue.length, done = 0, failed = [];
				if (!total) { $result.append(notice('warning', s.as_none)); setBusy(false); return; }

				var next = function () {
					if (!queue.length) {
						$result.empty().append(notice(failed.length ? 'warning' : 'success', format(s.as_done, total - failed.length)));
						if (failed.length) {
							$result.append($('<p></p>').text(s.as_failed + ' ' + failed.join(', ')));
						}
						setBusy(false);
						return;
					}
					var batch = queue.splice(0, 3);
					$result.empty().append($('<p></p>').text(format(s.as_progress, done, total)));
					$.post(s.ajax_url, { action: 'd2gc_availability_sync_run', nonce: nonce, doctors: batch })
						.done(function (r2) {
							if (!r2 || !r2.success) { fail(); return; }
							done += batch.length;
							failed = failed.concat(r2.data.failed);
							$('#d2gc-sync-newest').text(r2.data.status.newest || '');
							$('#d2gc-sync-oldest').text(r2.data.status.oldest || '');
							next();
						})
						.fail(fail);
				};
				next();
			})
			.fail(fail);
	});
});
