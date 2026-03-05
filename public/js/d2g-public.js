// Helper: email validation
function isEmail(email) {
    var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email) ? 'OK' : 'notOK';
}

// Helper: password strength text (uses localized password messages)
function checkStrength(password) {
    if (typeof d2gPublicData === 'undefined') return '';

    var strength = 0;

    if (password.length < 8) {
        return d2gPublicData.password.short;
    }

    if (password.length > 10) strength += 1;
    if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1;
    if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/)) strength += 1;
    if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1;
    if (password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1;

    if (strength < 2) return d2gPublicData.password.weak;
    if (strength === 2) return d2gPublicData.password.good;
    return d2gPublicData.password.strong;
}

// Helper: add dynamic form rows (edu/exp/publications)
// uses d2gPublicData.str.* keys
function add_form_row(type, rowID) {
    if (typeof d2gPublicData === 'undefined') return;

    var s = d2gPublicData.str;
    var newRowID = parseInt(rowID, 10) + 1;

    jQuery('.add_' + type).attr('data-entry-id', newRowID);

    var row;

    // Education or experience rows
    if (type === 'edu' || type === 'exp') {
        row = '<div class="row exp_edu ' + type + '_' + newRowID + '"><a class="remove_btn btn-add" href="#"><span class="icon-minus-circled"></span> </a>' +
            '<div class="col-sm-3"><div class="row"><div class="col-sm-6">' +
            '<input type="text" id="d2g_exp_edu_start_date' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_start_date]" placeholder="' + s.start + '"/>' +
            '</div><div class="col-sm-6"><input type="text" id="d2g_exp_edu_end_date' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_end_date]" placeholder="' + s.end + '"/>' +
            '</div></div></div>';

        if (type === 'edu') {
            row +=
                '<div class="col-sm-3"><input type="text" id="d2g_exp_edu_study' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_study]" placeholder="' + s.study_area + '"/></div>' +
                '<div class="col-sm-3"><input type="text" id="d2g_exp_edu_title' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_title]" placeholder="' + s.degree + '"/></div>' +
                '<div class="col-sm-3"><input type="text" id="d2g_exp_edu_org' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_org]" placeholder="' + s.institution + '"/></div>' +
                '</div>';
        } else {
            row +=
                '<div class="col-sm-3"><input type="text" id="d2g_exp_edu_expertise" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_expertise]" placeholder="' + s.expertise + '"/></div>' +
                '<div class="col-sm-3"><input type="text" id="d2g_exp_edu_title" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_title]" placeholder="' + s.position + '"/></div>' +
                '<div class="col-sm-3"><input type="text" id="d2g_exp_edu_org' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_org]" placeholder="' + s.organisation + '"/></div>' +
                '</div>';
        }

    } else {
        // Publication rows
        row =
            '<div class="row exp_edu ' + type + '_' + newRowID + '"><a class="remove_btn btn-add" href="#"><span class="icon-minus-circled"></span> </a>' +
            '<div class="col-sm-2"><input type="text" id="d2g_pub_title' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_pub_title]" placeholder="' + s.title + '"/></div>' +
            '<div class="col-sm-2"><input type="text" id="d2g_pub_link' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_pub_link]" placeholder="' + s.web_link + '"/></div>' +
            '<div class="col-sm-2"><input type="text" id="d2g_pub_journal' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_pub_journal]" placeholder="' + s.journal + '"/></div>' +
            '<div class="col-sm-2"><input type="text" id="d2g_pub_type' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_pub_type]" placeholder="' + s.type_publication + '"/></div>' +
            '<div class="col-sm-2"><input type="text" id="d2g_pub_author' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_pub_author]" placeholder="' + s.author + '"/></div>' +
            '<div class="col-sm-2"><input type="text" id="d2g_pub_date' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_pub_date]" placeholder="' + s.publication_date + '"/></div>' +
            '</div>';
    }

    jQuery('.' + type + '_wrapper').append(row);

    // Bind remove button for newly added row
    jQuery('.' + type + '_wrapper .remove_btn').off('click').on('click', function () {
        jQuery(this).parent().remove();
        return false;
    });
}

// Unified document ready: all front-end behaviour
jQuery(document).ready(function ($) {

    // Guard: if localized data is missing, still let basic UI run
    var d = (typeof d2gPublicData !== 'undefined') ? d2gPublicData : null;

    /* =========================================
       GENERAL UI: fancybox, select2, toggles
    ==========================================*/

    // Fancybox with Select2 re-init in modal
    $(".fancybox").fancybox({
        afterShow: function () {
            var $modal = $(".fancybox-inner");
            $modal.find("select").select2({ dropdownParent: $modal });
        }
    });
    $('.fancybox_spec').fancybox();

    // Graphical selects
    $("#outer_main_wrapper").find('select').select2();
    $("#content").find('select').select2();

    // Simple opener toggle
    $('.opener').click(function () {
        $(this).next().slideToggle('slow');
        $(this).toggleClass('active');
        $(this).find('span').toggleClass('icon-up-open icon-down-open');
    });

    // Scroll to anchor links
    $('.scroll_to').click(function () {
        var goal = $(this).attr('href');
        var offset = $('.navbar-wrapper-fixed').hasClass('fixed_nav') ? -50 : -200;

        if ($.fn.scrollTo) {
            $('body').scrollTo(goal, { duration: 'slow', offset: offset });
        } else {
            var el = document.querySelector(goal);
            if (el) window.scrollTo({ top: el.offsetTop + offset, behavior: 'smooth' });
        }

        return false;
    });

    // Open section and scroll on load (uses page.*)
    if (d && d.page) {
        var open = d.page.open;
        var scroll_to = d.page.scroll_to;

        if (open) {
            setTimeout(function () {
                $('#' + open).trigger('click');
            }, 800);
        }

        if (scroll_to) {
            setTimeout(function () {
                if ($.fn.scrollTo) {
                    $('body').scrollTo('#' + scroll_to, { duration: 'slow', offset: -120 });
                } else {
                    var el = document.getElementById(scroll_to);
                    if (el) window.scrollTo({ top: el.offsetTop - 120, behavior: 'smooth' });
                }
            }, 800);
        }
    }

    // Sidebar sticky behaviour on doctor pages
    var screenWidth = $(window).width();
    if (
        screenWidth >= 1024 &&
        $('body').hasClass('sidebar-menu') &&
        ($('body').hasClass('d2g-doctor-overview') || $('body').hasClass('single-d2g_doctor'))
    ) {
        var $window = $(window),
            $mainMenuBarAnchor = $('#doctor_wrapper');

        $window.scroll(function () {
            if (!$mainMenuBarAnchor.length) return;

            var window_top = $window.scrollTop();
            var div_top = $mainMenuBarAnchor.offset().top - 85;
            var div_height = $mainMenuBarAnchor.height();
            var side_height = $('.sidebar').height();

            if (window_top > div_top) {
                $('body').addClass('fixed_state');

                var width = document.getElementById('doctor_wrapper').offsetWidth;
                var widthSidebar = width * 0.25;

                $('#content_wrapper').css('margin-left', '25%');
                $('#doctor_list_wrapper_outer').css('margin-left', '25%');
                $('.sidebar').css('width', widthSidebar + 'px');
            } else {
                $('body').removeClass('fixed_state');
                $('.sidebar').removeAttr('style');
                $('#content_wrapper').css('margin-left', 0);
                $('#doctor_list_wrapper_outer').css('margin-left', 0);
            }

            var window_top_dif = $('.header_slider').height() + $('.how_to_row').height();
            if (window_top - window_top_dif > (div_height - side_height)) {
                var topz = window_top - window_top_dif - (div_height - side_height);
                $('.sidebar').css('top', 85 - topz + 'px');
            }
        });
    }

    // Simple tab content switcher
    $('.tab_link').click(function () {
        var myRef = $(this).attr('ref-loc');
        $('.d2g_tab_content_wrapper').addClass('hide');
        $(myRef).removeClass('hide');
    });

    // Generic remove button
    $('.remove_btn').click(function () {
        $(this).parent().remove();
        return false;
    });

    // Back link from localStorage
    var backLink = localStorage.getItem('backlink');
    if (backLink !== null) {
        $('#backLink').attr('href', backLink);
    }

    /* =========================================
       DOCTOR PROFILE EDIT FORM (front-end)
    ==========================================*/

    // Normalize price inputs to use dots
    $('.price_input').on('input', function () {
        $(this).val(function (i, val) {
            return val.replace(/,/g, '.');
        });
    });

    // Dynamic rows: experience, education, publications
    $('.add_exp').click(function (e) {
        e.preventDefault();
        var rowID = $(this).attr('data-entry-id');
        add_form_row('exp', rowID);
        return false;
    });

    $('.add_edu').click(function (e) {
        e.preventDefault();
        var rowID = $(this).attr('data-entry-id');
        add_form_row('edu', rowID);
        return false;
    });

    $('.add_pub').click(function (e) {
        e.preventDefault();
        var rowID = $(this).attr('data-entry-id');
        add_form_row('pub', rowID);
        return false;
    });

    // Checkbox opener and required field reset
    $('.check_box_opener').click(function () {
        $(this).parent().next().slideToggle();
    });

    $('.required').focus(function () {
        $(this).css('border-color', '#c2c2c2');
    });

    // Remove buttons (initial rows)
    $('.remove_btn').off('click').on('click', function () {
        $(this).parent().remove();
        return false;
    });

    // Save doctor (AJAX)
    $('.save_doctor').click(function (event) {
        if (!d || !d.ajax) return;

        tinymce.triggerSave();
        event.preventDefault();
        $(".d2g_doctor-form").toggleClass('loading');

        var myformData = new FormData($("#doctor_post")[0]);
        myformData.append('action', 'd2g_update_doc');

        $.ajax({
            type: "POST",
            data: myformData,
            url: d.ajax.url,
            processData: false,
            contentType: false,
            success: function (response) {
                $(".d2g_doctor-form").toggleClass('loading');
                console.log(response);
            },
            error: function (xhr, textStatus, errorThrown) {
                $(".d2g_doctor-form").toggleClass('loading');
                console.log(errorThrown);
            }
        });

        return false;
    });

    // Tab click inside doctor form (autosave then reload content)
    $('.tab').click(function (event) {
        if (!d || !d.ajax) return;

        tinymce.triggerSave();
        event.preventDefault();
        $(".d2g_doctor-form").toggleClass('loading');

        var myformData = new FormData($("#doctor_post")[0]);
        myformData.append('action', 'd2g_update_doc');

        $.ajax({
            type: "POST",
            data: myformData,
            url: d.ajax.url,
            processData: false,
            contentType: false,
            success: function (response) {
                $(".d2g_doctor-form").toggleClass('loading');
                console.log(response);
            },
            error: function (xhr, textStatus, errorThrown) {
                $(".d2g_doctor-form").toggleClass('loading');
                console.log(errorThrown);
            }
        });
    });

    // Delete profile image
    $('.del_img_link').click(function (event) {
        if (!d || !d.ajax) return;

        event.preventDefault();
        $(".d2g_doctor-form").toggleClass('loading');

        var data = {
            action: 'd2g_delete_profile_pic',
            _wpnonce: d.ajax.delete_pic,
            doc_id: $(this).attr('data-doc-id'),
            image: $(this).attr('data-image-id')
        };

        $.post(d.ajax.url, data, function (response) {
            $(".d2g_doctor-form").toggleClass('loading');
            if (response.success) {
                location.reload(true);
            }
        });

        return false;
    });

    // Unpublish doctor
    $('.unpublish_doctor').click(function (event) {
        if (!d || !d.ajax) return;

        tinymce.triggerSave();
        event.preventDefault();

        $('#post_status').val('draft');

        var myformData = new FormData($("#doctor_post")[0]);
        myformData.append('action', 'd2g_update_doc');

        $.ajax({
            type: "POST",
            data: myformData,
            url: d.ajax.url,
            processData: false,
            contentType: false,
            success: function (response) {
                console.log(response);
            },
            error: function (xhr, textStatus, errorThrown) {
                console.log(errorThrown);
            }
        });
    });

    // Publish doctor with validations
    $('.publish_doctor').click(function (event) {
        if (!d || !d.ajax || !d.msg) return;

        tinymce.triggerSave();
        event.preventDefault();
        $(".d2g_doctor-form").toggleClass('loading');

        $('#post_status').val('publish');

        var checker_message = '';
        var checker = false;

        // Required fields
        $('.required').each(function () {
            if ($(this).val() === "") {
                $(this).css('border-color', '#970808');
                checker = true;
                checker_message += d.msg.check1;
            }
        });

        // Required languages
        if ($("select[name='tax[doctor-language][]'] option:selected").length === 0) {
            checker = true;
            checker_message += d.msg.check2;
            $("select[name='tax[doctor-language][]']")
                .next()
                .find('.select2-selection')
                .css('border-color', '#970808');
        }

        if (checker === false) {
            var myformData = new FormData($("#doctor_post")[0]);
            myformData.append('action', 'd2g_update_doc');

            $.ajax({
                type: "POST",
                data: myformData,
                url: d.ajax.url,
                processData: false,
                contentType: false,
                success: function (response) {
                    $(".d2g_doctor-form").toggleClass('loading');
                    location.reload(true);
                },
                error: function (xhr, textStatus, errorThrown) {
                    $(".d2g_doctor-form").toggleClass('loading');
                    console.log(errorThrown);
                }
            });
        } else {
            $(".d2g_doctor-form").toggleClass('loading');
            alert(checker_message);
            return false;
        }
    });

    // Tabs (pm_tabs) at top of doctor form – adjust form action by role and tab
    $("ul.pm_tabs").find('span').on('click', function (event) {
        if (!d || !d.page || !d.user) return;

        event.preventDefault();

        var tabref = $(this).parent().attr('data-ref');
        var tabID = $(this).parent().attr('data-tab-id');

        var role = d.user.role || '';

        if (role.includes('administrator') || role.includes('editor')) {
            $('#doctor_post').attr('action', '?edit=' + d.page.edit_id + '&tab=' + tabID);
        } else {
            $('#doctor_post').attr('action', '?tab=' + tabID);
        }

        // Switch tab classes
        $("ul.pm_tabs").find('li').each(function () {
            if ($(this).data('ref') === tabref) {
                if (!$(this).hasClass('active')) {
                    $(this).addClass('active');
                }
            } else {
                $(this).removeClass('active');
            }
        });

        $("div.pm_d2g_tab_content").each(function () {
            if ($(this).hasClass(tabref)) {
                if (!$(this).hasClass('active')) {
                    $(this).removeClass('simple_hide');
                    $(this).addClass('active');
                }
            } else {
                if ($(this).hasClass('active')) {
                    $(this).removeClass('active');
                    $(this).addClass('simple_hide');
                } else if (!$(this).hasClass('simple_hide')) {
                    $(this).addClass('simple_hide');
                }
            }
        });
    });

    /* =========================================
       REGISTRATION FORM (front-end)
    ==========================================*/

    // Password strength indicator in registration form
    $('#pass1').keyup(function () {
        $('#result')
            .css('display', 'block')
            .html(checkStrength($('#pass1').val()));
    });

    // Registration submit validation
    $('#submit_registration').click(function (e) {
        e.preventDefault();

        // Bot check
        if ($('#tel_number').is(':checked')) {
            return false;
        }

        var checker = false;
        var email = $('#patient_email').val();
        var pass = $('#pass1').val();
        var rpass = $('#pass2').val();
        var checker_message = '';

        // These messages still come from d2gRegistrationVars
        $('.myrequired').each(function () {
            if ($(this).val() === "") {
                checker = true;
                checker_message = d2gRegistrationVars.msg_required;
            }
        });

        if (pass.length < 8) {
            checker = true;
            checker_message += ' ' + d2gRegistrationVars.msg_pass_short;
        }

        if (pass !== rpass) {
            checker = true;
            checker_message += ' ' + d2gRegistrationVars.msg_pass_match;
        }

        if (isEmail(email) === 'notOK') {
            checker = true;
            checker_message += ' ' + d2gRegistrationVars.msg_email_invalid;
        }

        if ($('#conf_privacy').is(':not(:checked)')) {
            checker = true;
            checker_message += ' ' + d2gRegistrationVars.msg_privacy;
        }

        if ($('#conf_terms').is(':not(:checked)')) {
            checker = true;
            checker_message += ' ' + d2gRegistrationVars.msg_terms;
        }

        if ($('#conf_disclaimer').is(':not(:checked)')) {
            checker = true;
            checker_message += ' ' + d2gRegistrationVars.msg_disclaimer;
        }

        if (checker) {
            $('#error').css('display', 'block').html(checker_message);
        } else {
            $('#custom-registration-form').submit();
        }

        return false;
    });

    /* =========================================
       PATIENT DASHBOARD: delete / cancel appointments
    ==========================================*/

    if (d && d.ajax && d.msg && d.mail && d.ajax.delete_nonce && d.ajax.mail_nonce) {

        // Loader helper for dashboard actions
        var loader = function () {
            $('#bg_loader,#loader').toggleClass('simple_hide');
        };

        // Delete appointment
        $(document).on('click', '.del_app', function (e) {
            e.preventDefault();

            var app_id = $(this).data('app-id');
            var wcc_user_id = $(this).data('user-id');

            loader();

            $.post(d.ajax.url, {
                action: 'delete_wcc_appointment',
                app_id: app_id,
                wcc_user_id: wcc_user_id,
                security: d.ajax.delete_nonce
            }, function (res) {

                loader();
                $('#app-' + app_id)
                    .html(res)
                    .css({ fontWeight: 700, color: '#6eb9b7' });
            });
        });

        // Show cancellation form prefilled
        $(document).on('click', '.prep_cancellation_email', function (e) {
            e.preventDefault();

            $('#cancellation_form_wrapper').removeClass('simple_hide');

            $('#app_date').val($(this).data('app-date'));
            $('#doc_name').val($(this).data('doc-name'));
            $('#doc_email').val($(this).data('doc-email'));
            $('#app_link').val($(this).data('app-link'));
        });

        // Send cancellation mails (patient + doctor)
        $(document).on('click', '#request_cancellation', function (e) {
            e.preventDefault();

            loader();

            var baseData = {
                action: 'send_ajax_d2g_email',
                app_date: $('#app_date').val(),
                app_link: $('#app_link').val(),
                bic: $('#bic').val(),
                iban: $('#iban').val(),
                comment: $('#comment').val(),
                nonce: d.ajax.mail_nonce
            };

            // Mail to patient
            $.post(d.ajax.url, Object.assign({}, baseData, {
                'e-mail': 'cancellation_patient',
                from_name: $('#doc_name').val(),
                from_email: d.mail.sender_email,
                to_name: $('#client_name').val(),
                to_email: $('#client_email').val(),
                title: d.mail.sender_name + ': ' + d.msg.cancel_title + ' (' + $('#doc_name').val() + ')'
            }), function (res) {

                $('#return1')
                    .show()
                    .html(res.message === 'mail_send_cancellation_patient'
                        ? d.msg.mail_patient_ok
                        : d.msg.mail_patient_err);
            });

            // Mail to doctor
            $.post(d.ajax.url, Object.assign({}, baseData, {
                'e-mail': 'cancellation_doctor',
                to_name: $('#doc_name').val(),
                to_email: $('#doc_email').val(),
                from_name: $('#client_name').val(),
                from_email: $('#client_email').val(),
                title: d.mail.sender_name + ': ' + d.msg.cancel_title + ' (' + $('#client_name').val() + ')'
            }), function (res) {

                loader();

                $('#return2')
                    .show()
                    .html(res.message === 'mail_send_cancellation_doctor'
                        ? d.msg.mail_doc_ok
                        : d.msg.mail_doc_err);
            });

            $('#cancellation_form_wrapper').addClass('simple_hide');
        });
    }

    /* =========================================
       WALK-IN REQUEST FORM
    ==========================================*/

    if (d && d.ajax && d.msg && d.recaptcha) {

        $(document).on('click', '.request_walkin', function (e) {
            e.preventDefault();

            var checker = false;
            var checker_message = '';

            // Required walk-in fields
            $('.required_walk').each(function () {
                if (!$(this).val()) {
                    $(this).css('border-color', '#970808');
                    checker = true;
                    checker_message = d.msg.check1;
                }
            });

            // Extra checks for guests
            if (!d.user || !d.user.is_logged_in) {

                if (!$('#conf_privacy_wf').is(':checked')) {
                    checker = true;
                    checker_message += d.msg.privacy;
                }

                if (!$('#conf_terms_wf').is(':checked')) {
                    checker = true;
                    checker_message += d.msg.terms;
                }

                if (!$('#conf_disclaimer_wf').is(':checked')) {
                    checker = true;
                    checker_message += d.msg.disclaimer;
                }
            }

            // Recaptcha check
            if (d.recaptcha.enabled && (!window.captchaCodeWalkin || window.captchaCodeWalkin.length === 0)) {
                checker = true;
                checker_message += d.msg.robot;
            }

            if (checker) {
                $('#walkin_error').html(checker_message).removeClass('simple_hide');
                return false;
            }

            $("#inloop").toggleClass('loading');

            var formData = new FormData($('#walkin_form')[0]);
            formData.append('action', 'create_wcc_walkin');

            $.ajax({
                type: 'POST',
                url: d.ajax.url,
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {

                    $("#inloop").toggleClass('loading');

                    if (res && res.data && res.data.redirect_url) {
                        window.location.href = res.data.redirect_url;
                    }

                    console.log(res);
                },
                error: function (xhr, status, err) {
                    $(".walkin_form_inner_wrapper").toggleClass('loading');
                    console.log(err);
                }
            });
        });
    }

	/* =========================================
       EMAIL CONSULTATION FORM
    ==========================================*/

    if (d && d.ajax && d.msg && d.recaptcha) {

        $(document).on('click', '.start_written_con', function (event) {
            event.preventDefault();

            var checker_message = '';
            var checker = false;

            // Required fields for written consultation
            $('.required_wc').each(function () {
                if ($(this).val() === "") {
                    $(this).css('border-color', '#970808');
                    checker = true;
                    checker_message = d.msg.check1;
                }
            });

            // Email format check
            if (isEmail($('#client_email_ec').val()) === 'notOK') {
                $('#client_email_ec').css('border-color', '#ff5000');
                checker = true;
                checker_message = checker_message + ' ' + d.msg.invalid_email;
            }

            // reCAPTCHA check (same pattern as walk-in)
            if (d.recaptcha.enabled && (typeof window.captchaCodeEmail === 'undefined' || window.captchaCodeEmail.length === 0)) {
                checker = true;
                checker_message += ' ' + d.msg.robot;
            }

            if (checker === false) {
                $("#written_consult").toggleClass('loading');

                var myformData = new FormData($("#written_con_form")[0]);
                myformData.append('action', 'create_wcc_written_cosnsult');

                $.ajax({
                    type: "POST",
                    data: myformData,
                    url: d.ajax.url,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        console.log(response);
                        $("#written_consult").toggleClass('loading');

                        if (response && response.data && response.data.redirect_url) {
                            window.location.href = response.data.redirect_url;
                        }
                    },
                    error: function (xhr, textStatus, errorThrown) {
                        $("#written_consult").toggleClass('loading');
                        console.log(errorThrown);
                    }
                });
            } else {
                $('#written_con_error').html(checker_message).toggleClass('simple_hide');
                return false;
            }

            return false;
        });
    }


});
