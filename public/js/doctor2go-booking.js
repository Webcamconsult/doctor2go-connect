(function($){
    'use strict';

    function isEmail(email){
        var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email) ? 'OK' : 'notOK';
    }

    function d2gGetDocId(){
        return $('#wp_doc_id').val();
    }

    function d2gHandlePrefillFromLocalStorage(){
        var params = new URLSearchParams(window.location.search);
        if( params.get('book') === '1' ){
            $('#start').html(localStorage.getItem('start'));
            $('#end').html(localStorage.getItem('end'));
            $('#start_str').val(localStorage.getItem('start_str'));
            $('#end_str').val(localStorage.getItem('end_str'));
            $('#hourly_price').val(localStorage.getItem('payment_price'));
            $('#pay_price').html(localStorage.getItem('payment_price'));
            $('#vat').val(localStorage.getItem('payment_vat'));
            $('#currency').val(localStorage.getItem('payment_currency'));
            $('#pay_cur').html(localStorage.getItem('payment_currency'));
            $('#location').html(localStorage.getItem('location'));
            $('#location_id').val(localStorage.getItem('doc_location_id'));
            $('#questionnaire').val(localStorage.getItem('questionnaire'));
        }
    }

    function d2gHandleBookingWrapperDisplay(){
        var params = new URLSearchParams(window.location.search);
        var hasBook = params.get('book') === '1';
        var hasCreate = params.get('create_account') === '1';

        if( hasBook || hasCreate ){
            $('#booking_form_wrapper').removeClass('simple_hide');
            var goal = '#booking_form_wrapper';
            setTimeout(function(){
                $('body').scrollTo(goal,{duration:'slow', offset : -260});
            }, 300);
        }
    }

    function d2gInitCalendar(){
        if( typeof FullCalendar === 'undefined' ){
            return;
        }

        var timezone = d2gBookingVars.d2g_timezone && d2gBookingVars.d2g_timezone !== '' ? d2gBookingVars.d2g_timezone : Intl.DateTimeFormat().resolvedOptions().timeZone;
        var docSlots = '';
        var calendarEl = document.getElementById('calendar');
        if( ! calendarEl ){
            return;
        }

        var calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                right: 'prev,next',
                left: 'title',
                center: ''
            },
            initialDate: d2gBookingVars.current_date,
            navLinks: true,
            navLinkDayClick: function( date, jsEvent ) {
                jsEvent.preventDefault();
                return false; // do nothing on day header click
            },
            moreLinkContent: function(arg){
                return {
                    html: '<div class="btn btn-outline-secondary text-align-center">' + arg.num + '<br><span class="not_mobile">' + d2gBookingVars.i18n.more_slots_text + '</span></div>'
                };
            },
            nowIndicator: true,
            weekNumbers: false,
            weekNumberCalculation: 'ISO',
            editable: false,
            selectable: true,
            dayMaxEvents: true,
            events: function(fetchInfo, successCallback, failureCallback){
                var docId = d2gGetDocId();

                if( d2gBookingVars.only_cal === false ){
                    var parentElement = document.getElementById('icon_list_' + docId);
                    if( parentElement ){
                        var hasChildWithClass = parentElement.querySelector('.icon-clock') !== null;
                        void(hasChildWithClass);
                    }
                }

                if( docSlots !== '' ){
                    successCallback(docSlots);
                    return;
                }

                var ajax_url = d2gBookingVars.ajax_url;

                $.ajax({
                    url: ajax_url,
                    type: 'POST',
                    data: {
                        action : 'd2g_load_availability_data',
                        doc_id : docId
                    },
                    success: function(response){
                        console.log(response);

                        if( d2gBookingVars.only_cal === false ){
                            if( response.data.walkin_check === true ){
                                var walkin = '<span class="walkin">' + d2gBookingVars.i18n.walk_in_consult + '</span>';
                                $('.walk_in_button').removeClass('simple_hide');
                                void(walkin);
                            }

                            if( response.data.tariffs !== '' ){
                                var tariffs = '<li class="icon-cc-mastercard col-sm-6 list-group-item"> ' + response.data.tariffs + '</li>';
                                $('#icon_list_' + docId).append(tariffs);
                                $('.fillup_' + docId).each(function(){
                                    $(this).html(response.data.tariffs);
                                });
                                $('body').find('.booking_con').css('display', 'list-item');
                            } else {
                                $('.fillup_' + docId).each(function(){
                                    $(this).html(d2gBookingVars.i18n.not_available);
                                });
                            }

                            if( response.data.first_availibility !== '' ){
                                var first_availability = '<li class="icon-clock col-sm-6 list-group-item"> ' + response.data.first_availibility + '</li>';
                                $('#icon_list_' + docId).append(first_availability);
                                $('.calendar_button').removeClass('simple_hide');
                            } else {
                                $('#calendar_wrapper').css('display', 'none');
                            }
                        }

                        console.log(response.data);

                        if( d2gBookingVars.in_tabs === true ){
                            if( response.data.walkin_check === true ){
                                $('.walk_in_button').removeClass('simple_hide');
                            }

                            if( response.data.tariffs !== '' ){
                                $('.fillup_' + docId).each(function(){
                                    $(this).html(response.data.tariffs);
                                });
                            } else {
                                $('.fillup_' + docId).each(function(){
                                    $(this).html(d2gBookingVars.i18n.not_available);
                                });
                            }

                            if( response.data.first_availibility !== '' ){
                                var first_availability_tab = '<li class="list-group-item icon-clock col-sm-6"> ' + response.data.first_availibility + '</li>';
                                $('#icon_list_' + docId).append(first_availability_tab);
                                $('.calendar_button').removeClass('simple_hide');
                            } else {
                                $('#calendar_wrapper').css('display', 'none');
                            }
                        }

                        docSlots = response.data.doc_slots;
                        if( response.data.first_availibility !== '' ){
                            successCallback(response.data.doc_slots);
                        }
                    },
                    error: function(){
                        failureCallback(console.log('there was an error'));
                    }
                });
            },
            locale: d2gBookingVars.locale,
            timeZone: timezone,
            slotDuration: '00:15:00',
            scrollTime: '09:00:00',
            eventClick: function(info){
                var payment_price = info.event._def.extendedProps.payment_price;
                var payment_currency = info.event._def.extendedProps.payment_currency;
                var payment_vat = info.event._def.extendedProps.payment_vat;
                var questionnaire = info.event._def.extendedProps.questionnaire;

                console.log(info.event._def.extendedProps);

                var part1 = info.event.startStr.split('T');
                var stDateParts = part1[0].split('-');
                var stDate = stDateParts[2] + '/' + stDateParts[1] + '/' + stDateParts[0];
                var part1Time = part1[1].indexOf('-') !== -1 ? part1[1].split('-')[0] : part1[1].split('+')[0];
                part1Time = part1Time.slice(0, -3);
                var niceStart = stDate + ' ' + d2gBookingVars.i18n.at + ' ' + part1Time + ' (' + timezone + ')';

                var part2 = info.event.endStr.split('T');
                var endDateParts = part2[0].split('-');
                var endDate = endDateParts[2] + '/' + endDateParts[1] + '/' + endDateParts[0];
                var part2Time = part2[1].indexOf('-') !== -1 ? part2[1].split('-')[0] : part2[1].split('+')[0];
                part2Time = part2Time.slice(0, -3);
                var niceEnd = endDate + ' ' + d2gBookingVars.i18n.at + ' ' + part2Time + ' (' + timezone + ')';

                var doc_location = '';
                var doc_location_id = '';
                if( info.event._def.extendedProps.location !== null ){
                    doc_location = info.event._def.extendedProps.location.location_name + ': ' + info.event._def.extendedProps.location.location_full_adress_url;
                    doc_location_id = info.event._def.extendedProps.location.location_id;
                } else {
                    doc_location = d2gBookingVars.i18n.video;
                }

                $('#start').html(niceStart);
                localStorage.setItem('start', niceStart);

                $('#end').html(niceEnd);
                localStorage.setItem('end', niceEnd);

                $('#start_str').val(info.event.startStr);
                localStorage.setItem('start_str', info.event.startStr);

                $('#end_str').val(info.event.endStr);
                localStorage.setItem('end_str', info.event.endStr);

                $('#hourly_price').val(payment_price);
                $('#pay_price').html(payment_price);
                localStorage.setItem('payment_price', payment_price);

                $('#vat').val(payment_vat);
                localStorage.setItem('payment_vat', payment_vat);

                $('#currency').val(payment_currency);
                $('#pay_cur').html(payment_currency);
                localStorage.setItem('payment_currency', payment_currency);

                $('#location').html(doc_location);
                localStorage.setItem('location', doc_location);

                $('#location_id').val(doc_location_id);
                localStorage.setItem('doc_location_id', doc_location_id);

                $('#questionnaire').val(questionnaire);
                localStorage.setItem('questionnaire', questionnaire);

                $('#booking_form_wrapper').removeClass('simple_hide');
                setTimeout(function(){
                    $('body').scrollTo('#booking_form_wrapper',{duration:'fast', offset : -50});
                }, 200);
            },
            eventContent: function(info){
                var location_name = '';
                var partStart = '';
                if( info.event._def.extendedProps.location !== null ){
                    location_name = info.event._def.extendedProps.location.location_name;
                    partStart = '<span class="physical">';
                } else {
                    location_name = d2gBookingVars.i18n.video;
                    partStart = '<span class="online">';
                }
                var partEnd = '</span>';
                var part1 = info.event.startStr.split('T');
                var theDay = part1[0];
                void(theDay);

                var part1Time = part1[1].indexOf('-') !== -1 ? part1[1].split('-')[0] : part1[1].split('+')[0];
                part1Time = part1Time.slice(0, -3);

                var part2 = info.event.endStr.split('T');
                var part2Time = part2[1].indexOf('-') !== -1 ? part2[1].split('-')[0] : part2[1].split('+')[0];
                part2Time = part2Time.slice(0, -3);

                var html = partStart + part1Time + '-' + part2Time + ' / ' + info.event._def.extendedProps.payment_price + info.event._def.extendedProps.payment_currency + '<br>' + location_name + partEnd;
                return { html: html };
            }
        });

        calendar.render();

        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(){
            calendar.updateSize();
        });
    }

    function d2gInitBookingForm(){
        $('.myrequired').focus(function(){
            $(this).css('border', 'none');
        });

        $('#submit_booking').click(function(e){
            e.preventDefault();

            if( $('#tel_number').is(':checked') ){
                return false;
            }

            var checker = false;
            var ajax_url = d2gBookingVars.ajax_url;
            var email = $('#patient_email').val();
            var uname = $('#uname').val();
            var pass = $('#pass1').val();
            var rpass = $('#pass2').val();
            var user_action = $('#user_action').val();
            var user_tel = $('#p_tel').val();
            var location_id = $('#location_id').val();
            var checker_message = '';

            $('.myrequired').each(function(){
                if( $(this).val() === '' ){
                    $(this).css('border', '1px solid #ff5000');
                    checker = true;
                    checker_message = d2gBookingVars.i18n.fill_required;
                }
                $('body').scrollTo('#booking_form_wrapper',{duration:'slow', offset : -200});
            });

            var data = {
                action                : 'd2g_create_wcc_appointment',
                start                 : $('#start_str').val(),
                end                   : $('#end_str').val(),
                vat                   : $('#vat').val(),
                email                 : $('#patient_email').val(),
                patient_fname         : $('#patient_fname').val(),
                patient_lname         : $('#patient_lname').val(),
                wp_doc_id             : $('#wp_doc_id').val(),
                wcc_user_id           : $('#wcc_user_id').val(),
                wp_user_id            : $('#wp_user_id').val(),
                docPrice              : $('#hourly_price').val(),
                comment               : $('#patient_comment').val(),
                user_action           : user_action,
                pass                  : pass,
                location_id           : location_id,
                token                 : $('#token').val(),
                p_tel                 : $('#p_tel').val(),
                currency              : $('#currency').val(),
                questionnaire_id      : $('#questionnaire').val(),
                'g-recaptcha-response': typeof captchaCodeCalendar !== 'undefined' ? captchaCodeCalendar : '',
                _wpnonce              : $('#_wpnonce').val()
            };

            if( isEmail(email) === 'notOK' ){
                $('#email').css('border-color', '#ff5000');
                checker = true;
                checker_message = checker_message + d2gBookingVars.i18n.invalid_email;
            }

            if( d2gBookingVars.recaptcha_site_key && d2gBookingVars.recaptcha_site_key !== '' ){
                if( typeof captchaCodeCalendar === 'undefined' || ! captchaCodeCalendar || captchaCodeCalendar.length === 0 ){
                    checker = true;
                    checker_message += d2gBookingVars.i18n.recaptcha_required;
                }
            }

            if( checker === false ){
                $('#booking_form').addClass('loading');
                $('#error').addClass('simple_hide');
                $('#app_msg').addClass('simple_hide');
                $('#app_msg_success').addClass('simple_hide');

                $("#loader_booking").show(); // show loader before request

                $.post(ajax_url, data, function(response){
                    console.log(response);
                    if( response !== 'error' ){
                        var redirectURLBase = d2gBookingVars.appointment_conf_url;
                        var redirectURL = encodeURIComponent(redirectURLBase + '?booked_consult=video&app=');

                        var answer = '<p class="success">' + d2gBookingVars.i18n.reservation_success + '</p>';

                        if( response.send_to_payment === true ){
                            answer += '<p>' + d2gBookingVars.i18n.reservation_payment_info + '</p>';
                            answer += '<p>' + d2gBookingVars.i18n.reservation_redirect + '</p>';
                            answer += '<p><a target="_blank" class="btn btn-default" href="' + d2gBookingVars.waiting_room_url + 'payment/' + response.appointment_id + '?locale=' + d2gBookingVars.locale + '&redirect_url=' + redirectURL + response.appointment_id + '">';
                            answer += d2gBookingVars.i18n.pay_now + '</a></p>';
                        }

                        $("#loader_booking").hide(); // show loader before request
                        $('#app_msg_success').html(answer).removeClass('simple_hide');

                        if( response.send_to_payment === true ){
                            setTimeout(function(){
                                window.location.href = d2gBookingVars.waiting_room_url + 'payment/' + response.appointment_id + '?locale=' + d2gBookingVars.locale + '&redirect_url=' + redirectURL + response.appointment_id;
                            }, 1500);
                        } else {
                            setTimeout(function(){
                                window.location.href = redirectURLBase + '?app=' + response.appointment_id + '&client_token=' + response.client_token;
                            }, 1500);
                        }
                    } else {
                        var answer = '<p>' + d2gBookingVars.i18n.error_general + '</p>';
                        $('#error').html(answer).removeClass('simple_hide');
                    }

                    $('#booking_form').toggleClass('loading');
                    $('#booking_form').toggleClass('simple_hide');
                    var goal = '#booking_form_wrapper';
                    $('body').scrollTo(goal,{duration:'slow', offset : -260});
                });
            } else {
                $('#error').css('display', 'block').html(checker_message);
            }

            return false;
        });
    }

    function d2gInitHolidayAlert(){
        if( ! d2gBookingVars.start_holiday || ! d2gBookingVars.end_holiday ){
            return;
        }

        try{
            var start = new Date(d2gBookingVars.start_holiday);
            var end = new Date(d2gBookingVars.end_holiday);
            var check = new Date();

            start.setHours(0,0,0,0);
            end.setHours(0,0,0,0);
            check.setHours(0,0,0,0);

            if( check >= start && check <= end ){
                var options = { day: '2-digit', month: '2-digit', year: 'numeric' };
                var formattedStart = start.toLocaleDateString(undefined, options);
                var formattedEnd = end.toLocaleDateString(undefined, options);
                alert(d2gBookingVars.i18n.holiday_attention + ' ' + formattedStart + ' - ' + formattedEnd);
            }
        } catch(e){
            console.log(e);
        }
    }

    $(document).ready(function(){
        d2gHandlePrefillFromLocalStorage();
        d2gHandleBookingWrapperDisplay();
        d2gInitBookingForm();
        d2gInitHolidayAlert();
    });

    document.addEventListener('DOMContentLoaded', function(){
        d2gInitCalendar();
    });

})(jQuery);
