jQuery(document).ready(function($){
    $('.more_doctors').click(function(){
        $('body').scrollTo('#end', {duration: 'slow', offset: -200});
        var pageNr = parseInt($(this).attr('data-page'));
        var ajax_url = myShortcodeData.ajax_url;
        var maxPage = myShortcodeData.maxPage;

        var intake_val = 0;

        if($('#intake').is(':checked')){
            intake_val = 1;
        }

        var subtitle_val = 0;

        if($('#sub_title').is(':checked')){
            subtitle_val = 1;
        }

        var data = {
            'action'                    : 'd2gc_doctor_call',
            'template'                  : myShortcodeData.template,
            'page'                      : pageNr,
            'cssClass'					: $('#cssClass').val(),
            'orderby'					: $('#orderby').val(),
            'order'						: $('#order').val(),
            'meta_key'					: $('#meta_key').val(),
            'posts_per_page'            : myShortcodeData.posts_per_page,
            'specialty'                 : $('#specialty_filter').val(),
            'doctor-language'           : $('#language_filter').val(),
            'country-origin'            : $('#country_filter').val(),
            'intake'                    : intake_val,
            'sub_title'                 : subtitle_val,
            '_wpnonce'   				: myShortcodeData._wpnonce,  // Inline nonce
            
        };
        

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajax_url, data, function(response) {
            
            var newPageNr = pageNr + 1;

            if(newPageNr <= maxPage ){
                $('#newPageNr').val(newPageNr);
                $('.more_doctors').attr('data-page', newPageNr);
            } else {
                $('#newPageNr').val(newPageNr);
                $('.more_doctors').css('display', 'none');
            }
            $('#doctor_wrapper').append(response);

        });

        setTimeout(function(){
            var newPageNr = !isNaN(parseInt($('#newPageNr').val(),10)) ? parseInt($('#newPageNr').val(),10) : parseInt($('.more_doctors').attr('data-page'));
            if(newPageNr > maxPage){
                $('.more_doctors').hide();
            } else {
                $('.more_doctors').show();
            }
        }, 100);
    });
});


jQuery(document).ready(function($){
					
    $('.doctor_filter').on('change', function(){
        $('#search_submit').css('display', 'none');
        $('#newPageNr').val(2);
        $('.more_doctors').attr('data-page', 2);
        $('#doctor_filters').css('opacity', '0.5');
        $('#doctor_wrapper').fadeOut();
        $('#search_error').css('display', 'none');

        if ( myShortcodeDataFilters.standalone_checker == 'false' ) { 

            var currentUrl = window.location.href;
            var url = new URL(currentUrl);
            
            if($('#specialty_filter').val() != 0){
                url.searchParams.set('doctor-specialty', $('#specialty_filter').val());
            }
            
            if($('#language_filter').val() != 0){
                url.searchParams.set('doctor-language', $('#language_filter').val());
            }

            if($('#country_filter').val() != 0){
                url.searchParams.set('country-origin', $('#country_filter').val());
            }

            if($('#consult_type').val() != 0){
                url.searchParams.set('consult_type', $('#consult_type').val());
            }

            if($('#post_id').val() != 0){
                url.searchParams.set('post_id', $('#post_id').val());
            }

            var newUrl = url.href; 
            window.history.pushState('listingparams', 'Title', newUrl);
            localStorage.setItem('backlink', newUrl);
        } 


        var intake_val = 0;

        if($('#intake').is(':checked')){
            intake_val = 1;
        }

        var subtitle_val = 0;

        if($('#sub_title').is(':checked')){
            subtitle_val = 1;
        }

        

        var ajax_url = myShortcodeDataFilters.ajax_url;
        var data = {
            'action'                    : 'd2gc_doctor_call',
            'specialty'                 : $('#specialty_filter').val(),
            'doctor-language'           : $('#language_filter').val(),
            'country-origin'            : $('#country_filter').val(),
            'consult_type'              : $('#consult_type').val() || '',
            'post_id'            		: $('#post_id').val(),
            'orderby'					: $('#orderby').val(),
            'order'						: $('#order').val(),
            'meta_key'					: $('#meta_key').val(),
            'min-price'                 : $('#amount1-price').val(),
            'max-price'                 : $('#amount2-price').val(),
            'template'                  : $('#template').val(),
            'intake'                    : intake_val,
            'sub_title'                 : subtitle_val,
            'posts_per_page'            : $('#posts_per_page').val(),
            'page'                      : 1,
            'cssClass'					: $('#cssClass').val(),
            'my_action'                 : 'filter',
            'resp'						: 'default',
            '_wpnonce'   				: myShortcodeDataFilters._wpnonce,  // Inline nonce
        };

        console.log('Filter data:', data);


        if ( myShortcodeDataFilters.standalone_checker == 'false' ) {
            //
            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            jQuery.post(ajax_url, data, function(response) {
                $('#doctor_filter_wrapper').css('opacity', '1');
                //console.log('res:' + response);
                if(response == 0){
                    response = myShortcodeDataFilters.str_no_doctors_found;
                }
                $('#doctor_wrapper_outer').removeClass('loading_doctors');
                $('#doctor_wrapper').html(response);
                $('#doctor_wrapper').fadeIn();
                
            });
        } 

        data.posts_per_page 	= -1;
        data.resp 				= 'only_count';
        data.action				= 'd2gc_doctor_count_call';
        
        
        jQuery.post(ajax_url, data, function(response) {
            console.log(response);
            $('#doc_count').html(response);
            $('#doctor_filters').css('opacity', '1');
            if(response > 0){
                $('#search_submit').css('display', 'inline-block');	
            } else {
                $('#search_error').css('display', 'block').html(myShortcodeDataFilters.str_no_doctors_found);
            }
            
        });
    });

    var waiting = 0;
});

