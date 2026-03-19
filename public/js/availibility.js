jQuery(document).ready(function () {
    //load availability data
    if(availibilityData.nonce){
        loadAvailabilityData(availibilityData);
    }
});


window.loadAvailabilityData = function () {

    const requests = [];

    jQuery('#doctor_wrapper_outer').addClass('loading');
    

    jQuery('#doctor_wrapper .d2g_doctor').each(function () {

        const postID   = jQuery(this).data('postid');
        const docKey   = jQuery(this).data('dockey');
        const wrapper  = '#icon_list_' + postID;
        const template = jQuery(this).data('template');
        const colClass = template === 'list' ? 'col-sm-6' : '';


        // CHECK: if this doctor already has the 'icon-cc-mastercard', skip loading
        if (jQuery(this).hasClass('avail_loaded')) {
            console.log('Availabilities already loaded for doctor', postID);
            return; // skip this doctor
        }
       
        const req = fetch(availibilityData.restUrl + 'availabilities', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': availibilityData.nonce
            },
            body: JSON.stringify({
                doc_key: docKey,
                doc_id: postID
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data?.data) {
                do_doctor_availibility(data.data, postID, colClass, wrapper);
            }
        });

        requests.push(req);
    });

    Promise.all(requests).finally(() => {
        jQuery('#doctor_wrapper_outer').removeClass('loading');
    });
};


function do_doctor_availibility(data, postID, colClass, wrapper){
    jQuery('#doc_' + postID).addClass('avail_loaded');
    //process data here
    if(data.walkin_check == true){
        var walkin = '<span class="walkin '+ colClass +'">' + availibilityData.string1 + '</span>';
        jQuery('#doc_' + postID).append(walkin);
        jQuery('.post-' + postID).find('.walk_in_button').removeClass('simple_hide');    
    }

    if(data.tariffs != ''){
        let str = data.tariffs
        let newStr = str;
        jQuery('.fillup_' + postID).html(newStr);
        
        var tariffs = '<li class="list-group-item icon-cc-mastercard '+ colClass +'"> '+ data.tariffs +'</li>'
        jQuery('#icon_list_' + postID).append(tariffs);
        jQuery('.post-' + postID).find('.booking_con').css('display', 'list-item');
        
    } else {
        jQuery('.fillup_' + postID).html(availibilityData.string2);
    }

    if(data.first_availibility != ''){
        var first_availability = '<li class="list-group-item icon-clock '+ colClass +'"> '+ data.first_availibility +'</li>'
        jQuery('#icon_list_' + postID).append(first_availability);
    }

    //show more info
    jQuery(wrapper).find('.icon-info').bind('click', function(){
        jQuery(this).next().toggleClass('simple_hide');
        
    });
}