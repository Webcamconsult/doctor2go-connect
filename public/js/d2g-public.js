// Helper: email validation
function isEmail(email) {
    var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email) ? 'OK' : 'notOK';
}


function checkStrength(password) {
    if (typeof d2gPublicData === 'undefined') {
        return {
            check: 'NOK',
            msg: ''
        };
    }

    if (!password || password.length < 12) {
        return {
            check: 'NOK',
            msg: d2gPublicData.password.short
        };
    }

    var hasLower   = /[a-z]/.test(password);
    var hasUpper   = /[A-Z]/.test(password);
    var hasNumber  = /[0-9]/.test(password);
    var hasSpecial = /[^A-Za-z0-9]/.test(password);

    if (!hasLower || !hasUpper || !hasNumber || !hasSpecial) {
        return {
            check: 'NOK',
            msg: d2gPublicData.password.weak
        };
    }

    return {
        check: 'OK',
        msg: d2gPublicData.password.strong
    };
}

// Helper: compress images client side
function compressImage(file, maxWidth = 1024, quality = 0.7) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => {
            let { width, height } = img;
            
            if (width > maxWidth) {
                height = (height * maxWidth) / width;
                width = maxWidth;
            }
            
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);
            
            // Map original MIME type to canvas-supported output
            let mimeType = file.type;
            if (mimeType === 'image/png') {
                mimeType = 'image/png';  // PNG supports transparency
            } else if (mimeType === 'image/webp') {
                mimeType = 'image/webp';
            } else {
                mimeType = 'image/jpeg';  // Default fallback
            }
            
            canvas.toBlob(blob => {
                // Keep original extension or adapt
                let newName = file.name;
                if (mimeType === 'image/jpeg' && !newName.toLowerCase().endsWith('.jpg')) {
                    newName = newName.replace(/\.[^/.]+$/, '') + '_compressed.jpg';
                } else if (mimeType === 'image/png' && !newName.toLowerCase().endsWith('.png')) {
                    newName = newName.replace(/\.[^/.]+$/, '') + '_compressed.png';
                }
                
                const compressedFile = new File([blob], newName, {
                    type: mimeType,
                    lastModified: Date.now()
                });
                resolve(compressedFile);
            }, mimeType, quality);
        };
        img.onerror = reject;
        img.src = URL.createObjectURL(file);
    });
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
            '<input type="text" class="form-control"" id="d2g_exp_edu_start_date' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_start_date]" placeholder="' + s.start + '"/>' +
            '</div><div class="col-sm-6"><input type="text" class="form-control"" id="d2g_exp_edu_end_date' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_end_date]" placeholder="' + s.end + '"/>' +
            '</div></div></div>';

        if (type === 'edu') {
            row +=
                '<div class="col-sm-3"><input type="text" class="form-control"" id="d2g_exp_edu_study' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_study]" placeholder="' + s.study_area + '"/></div>' +
                '<div class="col-sm-3"><input type="text" class="form-control"" id="d2g_exp_edu_title' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_title]" placeholder="' + s.degree + '"/></div>' +
                '<div class="col-sm-3"><input type="text" class="form-control"" id="d2g_exp_edu_org' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_org]" placeholder="' + s.institution + '"/></div>' +
                '</div>';
        } else {
            row +=
                '<div class="col-sm-3"><input type="text" class="form-control"" id="d2g_exp_edu_expertise" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_expertise]" placeholder="' + s.expertise + '"/></div>' +
                '<div class="col-sm-3"><input type="text" class="form-control"" id="d2g_exp_edu_title" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_title]" placeholder="' + s.position + '"/></div>' +
                '<div class="col-sm-3"><input type="text" class="form-control"" id="d2g_exp_edu_org' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_exp_edu_org]" placeholder="' + s.organisation + '"/></div>' +
                '</div>';
        }

    } else {
        // Publication rows
        row =
            '<div class="row exp_edu ' + type + '_' + newRowID + '"><a class="remove_btn btn-add" href="#"><span class="icon-minus-circled"></span> </a>' +
            '<div class="col-sm-2"><input type="text" class="form-control"" id="d2g_pub_title' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_pub_title]" placeholder="' + s.title + '"/></div>' +
            '<div class="col-sm-2"><input type="text" class="form-control"" id="d2g_pub_link' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_pub_link]" placeholder="' + s.web_link + '"/></div>' +
            '<div class="col-sm-2"><input type="text" class="form-control"" id="d2g_pub_journal' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_pub_journal]" placeholder="' + s.journal + '"/></div>' +
            '<div class="col-sm-2"><input type="text" class="form-control"" id="d2g_pub_type' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_pub_type]" placeholder="' + s.type_publication + '"/></div>' +
            '<div class="col-sm-2"><input type="text" class="form-control"" id="d2g_pub_author' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_pub_author]" placeholder="' + s.author + '"/></div>' +
            '<div class="col-sm-2"><input type="text" class="form-control"" id="d2g_pub_date' + newRowID + '" value="" tabindex="1" size="40" name="meta[' + type + 's][' + newRowID + '][d2g_pub_date]" placeholder="' + s.publication_date + '"/></div>' +
            '</div>';
    }

    jQuery('.' + type + '_wrapper').append(row);

    // Bind remove button for newly added row
    jQuery('.' + type + '_wrapper .remove_btn').off('click').on('click', function () {
        jQuery(this).parent().remove();
        return false;
    });
}

// Add this function at the top with other helpers
function initTooltips() {
    var isTouch = ('ontouchstart' in window) || navigator.maxTouchPoints > 0;

    var tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');

    tooltipTriggerList.forEach(function (el) {
        // Decide trigger based on device
        var triggerMode = isTouch ? 'click' : 'hover';

        // Always get or create with correct config
        bootstrap.Tooltip.getOrCreateInstance(el, {
            trigger: triggerMode,
            delay: isTouch ? { show: 0, hide: 0 } : { show: 200, hide: 100 },
            container: 'body'
        });
    });
}

// Unified document ready: all front-end behaviour
jQuery(document).ready(function ($) {

    // Guard: if localized data is missing, still let basic UI run
    var d = (typeof d2gPublicData !== 'undefined') ? d2gPublicData : null;

    /* =========================================
       GENERAL UI: popups, select2, toggles
    ==========================================*/

    // init once when DOM is ready
    initTooltips();

    // if you really need it for dynamic content, you can keep this,
    // but now it always disposes + recreates with correct options:
    var observer = new MutationObserver(function () {
        initTooltips();
    });
    observer.observe(document.body, { childList: true, subtree: true });

    // Popups (Bootstrap modal): a .d2g-modal-trigger link opens the element its href points to (#id).
    // The element is moved into the modal while open and moved back afterwards, so its event
    // handlers and entered form values stay intact.
    var $d2gModal = $('#d2gc_modal');
    var $d2gModalPlaceholder = null;

    function d2gReinitSelects($scope, $dropdownParent) {
        $scope.find('select').each(function () {
            var $select = $(this);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.select2($dropdownParent ? { dropdownParent: $dropdownParent } : {});
        });
    }

    $(document).on('click', '.d2g-modal-trigger', function (event) {
        var target = $(this).attr('href');
        if (!target || target.charAt(0) !== '#' || target.length < 2 || !$(target).length) return;

        event.preventDefault();
        var $content = $(target);

        // Without Bootstrap JS (disabled in the settings and not loaded by the theme): show the content in place
        if (!$d2gModal.length || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            $content.addClass('d2g-in-modal').css('display', 'block');
            $('body').scrollTo($content, { duration: 'fast', offset: -120 });
            return;
        }

        if ($d2gModalPlaceholder) return; // a popup is already open

        // the content is hidden on the page (.simple_hide etc.), the inline display makes it visible inside the popup
        $d2gModalPlaceholder = $('<span class="d2g-modal-placeholder" hidden></span>').insertBefore($content);
        $content.addClass('d2g-in-modal').css('display', 'block').appendTo($d2gModal.find('.modal-body'));
        bootstrap.Modal.getOrCreateInstance($d2gModal[0]).show();
    });

    $d2gModal.on('shown.bs.modal', function () {
        d2gReinitSelects($d2gModal.find('.modal-body'), $d2gModal);
    });

    $d2gModal.on('hidden.bs.modal', function () {
        if (!$d2gModalPlaceholder) return;

        var $content = $d2gModal.find('.modal-body').children().removeClass('d2g-in-modal').css('display', '');
        $d2gModalPlaceholder.replaceWith($content);
        $d2gModalPlaceholder = null;
        d2gReinitSelects($content);
    });

    //eye toggle for password field
    $('.js-toggle-password').on('click', function () {
        var $button = $(this);
        var $input = $button.siblings('.js-password-field');
        var $icon = $button.find('i');

        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('bi-eye').addClass('bi-eye-slash');
            $button.attr('aria-label', 'Hide password');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('bi-eye-slash').addClass('bi-eye');
            $button.attr('aria-label', 'Show password');
        }
    });

    // Graphical selects
    $(".d2g_wrapper").find('select').select2();
    $("#content").find('select').select2();

    // Simple opener toggle
    $('.opener').click(function () {
        $(this).next().slideToggle('slow');
        $(this).toggleClass('active');
        $(this).find('span').toggleClass('d2cif-Arrow-small-down');
        $(this).find('span').toggleClass('d2cif-Arrow-small-up');
    });

    // Scroll to anchor links
    $('.scroll_to').click(function () {
        var goal = $(this).attr('href');

        $('body').scrollTo(goal, { duration: 'fast'});

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
                $('body').scrollTo('#' + scroll_to, { duration: 'fast', offset: -50 });
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
            $mainMenuBarAnchor = $('#doctor_wrapper_v1');

        $window.scroll(function () {
            if (!$mainMenuBarAnchor.length) return;

            var window_top = $window.scrollTop();
            var div_top = $mainMenuBarAnchor.offset().top - 85;
            var div_height = $mainMenuBarAnchor.height();
            var side_height = $('.sidebar').height();

            if (window_top > div_top) {
                $('body').addClass('fixed_state');

                var width = document.getElementById('doctor_wrapper_v1').offsetWidth;
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

    // Doctor form busy state: block all actions and show a Bootstrap spinner in the clicked button.
    // Stays active until the page reloads, so slow saves (e.g. image uploads) can not be triggered twice.
    var docFormActions = '.save_doctor, .publish_doctor, .unpublish_doctor, .del_img_link, #docTab .nav-link';

    function setDoctorFormBusy(busy, $clicked) {
        var $form = $(".d2g_doctor-form");

        $form.toggleClass('loading', busy).attr('aria-busy', busy ? 'true' : 'false');
        $form.find(docFormActions).prop('disabled', busy).toggleClass('disabled', busy);
        $form.find('.d2g-btn-spinner').remove();

        if (busy && $clicked && $clicked.length) {
            $clicked.prepend('<span class="spinner-border spinner-border-sm me-2 d2g-btn-spinner" role="status" aria-hidden="true"></span>');
        }
    }

    function isDoctorFormBusy() {
        return $(".d2g_doctor-form").attr('aria-busy') === 'true';
    }

    // Save doctor (AJAX)
    $('.save_doctor').click(function (event) {
        if (!d || !d.ajax) return;

        event.preventDefault();
        if (isDoctorFormBusy()) return false;

        tinymce.triggerSave();
        setDoctorFormBusy(true, $(this));

        var myformData = new FormData($("#doctor_post")[0]);
        myformData.append('action', 'd2gc_update_doc');

        $.ajax({
            type: "POST",
            data: myformData,
            url: d.ajax.url,
            processData: false,
            contentType: false,
            success: function (response) {
                location.reload(true);
            },
            error: function (xhr, textStatus, errorThrown) {
                setDoctorFormBusy(false);
                console.log(errorThrown);
            }
        });

        return false;
    });

    // Tab click inside doctor form (autosave then reload content)
    $('#docTab').find('.nav-link').click(function (event) {
        if (!d || !d.ajax) return;

        event.preventDefault();
        if (isDoctorFormBusy()) return false;

        tinymce.triggerSave();
        setDoctorFormBusy(true);

        var myformData = new FormData($("#doctor_post")[0]);
        myformData.append('action', 'd2gc_update_doc');

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
            },
            complete: function () {
                setDoctorFormBusy(false);
            }
        });
    });

    // Delete profile image
    $('.del_img_link').click(function (event) {
        if (!d || !d.ajax) return;

        event.preventDefault();
        if (isDoctorFormBusy()) return false;

        setDoctorFormBusy(true, $(this));

        var data = {
            action: 'd2gc_delete_profile_pic',
            _wpnonce: d.ajax.delete_pic,
            doc_id: $(this).attr('data-doc-id'),
            image: $(this).attr('data-image-id')
        };

        $.post(d.ajax.url, data, function (response) {
            if (response.success) {
                location.reload(true);
            } else {
                setDoctorFormBusy(false);
            }
        }).fail(function () {
            setDoctorFormBusy(false);
        });

        return false;
    });

    // Unpublish doctor
    $('.unpublish_doctor').click(function (event) {
        if (!d || !d.ajax) return;

        event.preventDefault();
        if (isDoctorFormBusy()) return false;

        tinymce.triggerSave();
        setDoctorFormBusy(true, $(this));

        $('#post_status').val('draft');

        var myformData = new FormData($("#doctor_post")[0]);
        myformData.append('action', 'd2gc_update_doc');

        $.ajax({
            type: "POST",
            data: myformData,
            url: d.ajax.url,
            processData: false,
            contentType: false,
            success: function (response) {
                location.reload(true);
            },
            error: function (xhr, textStatus, errorThrown) {
                setDoctorFormBusy(false);
                console.log(errorThrown);
            }
        });
    });

    // Publish doctor with validations
    $('.publish_doctor').click(function (event) {
        if (!d || !d.ajax || !d.msg) return;

        event.preventDefault();
        if (isDoctorFormBusy()) return false;

        tinymce.triggerSave();
        setDoctorFormBusy(true, $(this));

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
            myformData.append('action', 'd2gc_update_doc');

            $.ajax({
                type: "POST",
                data: myformData,
                url: d.ajax.url,
                processData: false,
                contentType: false,
                success: function (response) {
                    location.reload(true);
                },
                error: function (xhr, textStatus, errorThrown) {
                    setDoctorFormBusy(false);
                    console.log(errorThrown);
                }
            });
        } else {
            setDoctorFormBusy(false);
            $('#post_status').val('draft');
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
    $('.pass1').keyup(function () {
        $('#result').css('display', 'block').html(checkStrength($('.pass1').val()).msg);
        if (checkStrength($('.pass1').val()).check === 'OK') {
            $('#result').removeClass('alert-danger').addClass('alert-success');
        } else {
            $('#result').removeClass('alert-success').addClass('alert-danger');
        }
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
        // Delete appointment
        $(document).on('click', '.del_app', function (e) {
            e.preventDefault();

            
            var app_id      = $(this).data('app-id');
            var $btn        = $(this);
            // Turn on loader in this button
            $btn.addClass('btn-loading').addClass('disabled').attr('aria-disabled', 'true');

            // the server checks that the appointment belongs to this patient (logged in, or client_token from the e-mail link)
            $.post(d.ajax.url, {
                action: 'd2gc_delete_wcc_appointment',
                app_id: app_id,
                client_token: $(this).data('client-token') || '',
                security: d.ajax.delete_nonce
            }, function (res) {

                // Usually the whole row is replaced after success
                $('#app-' + app_id).html('<div class="alert alert-success w-100 m-0">' + res + '<div>');
                $('#app-' + app_id).parent().find('.payment_needed').addClass('simple_hide');

            }).fail(function () {

                // On error, restore the button so user can retry
                $btn.removeClass('disabled').removeAttr('aria-disabled');
                $btn.find('.del-label').css('opacity', 1);
                $btn.find('.del-spinner').hide();
            });
        });
    }

    $('.prep_cancellation_email').click(function(e){
        e.preventDefault();

        // the form itself is opened in the popup via the .d2g-modal-trigger class
        // the appointment id is what the server uses to find doctor, recipients and date (see d2gc_send_ajax_d2g_email)
        $('#cancellation_form_wrapper').data('app-id', $(this).data('app-id')).data('client-token', $(this).data('client-token') || '');
        $('#app_date').val($(this).data('app-date'));
        $('#doc_name').val($(this).data('doc-name'));
        $('#doc_name_visible').html($(this).data('doc-name'));
        $('#doc_email').val($(this).data('doc-email'));
        $('#app_link').val($(this).data('app-link'));
    });


    // Send cancellation mails (patient + doctor)
    $('#request_cancellation').click(function (e) {
        e.preventDefault();

        var $btn        = $(this);
        // Turn on loader in this button
        $btn.addClass('btn-loading').addClass('disabled').attr('aria-disabled', 'true');

        

        // recipients, date, link and subject are determined on the server from app_id,
        // the other fields below are only kept for the display in this form
        var baseData = {
            action: 'd2gc_send_ajax_d2g_email',
            app_id: $('#cancellation_form_wrapper').data('app-id'),
            client_token: $('#cancellation_form_wrapper').data('client-token') || '',
            client_name: $('#client_name').val(),
            comment: $('#comment').val(),
            nonce: d.ajax.mail_nonce
        };

        // Mail to patient
        $.post(d.ajax.url, Object.assign({}, baseData, {
            'e-mail': 'cancellation_patient'
        }), function (res) {
            $('#return1').show().html(res.message === 'mail_send_cancellation_patient'? d.msg.mail_patient_ok: d.msg.mail_patient_err);
            if(d.msg.mail_patient_ok){
                $('#return1').addClass('alert alert-success');
            } else {
                $('#return1').addClass('alert alert-danger');
            }
        });

        // Mail to doctor
        $.post(d.ajax.url, Object.assign({}, baseData, {
            'e-mail': 'cancellation_doctor'
        }), function (res) {
            $('#return2').show().html(res.message === 'mail_send_cancellation_doctor'? d.msg.mail_doc_ok: d.msg.mail_doc_err);
            if(d.msg.mail_doc_ok){
                $('#return2').addClass('alert alert-success');
            }else {
                $('#return2').addClass('alert alert-danger');
            }
            $btn.removeClass('disabled').removeAttr('aria-disabled');
            $btn.find('.del-label').css('opacity', 1);
            $btn.find('.del-spinner').hide();
            $('#cancellation_form').addClass('simple_hide');
        });

        
    });

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
            if (!$('#conf_all_wf').is(':checked')) {
                checker = true;
                checker_message += ' ' + d.msg.privacy;
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
            formData.append('action', 'd2gc_create_wcc_walkin');

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
    ========================================== */

    if (d && d.ajax && d.msg && d.recaptcha) {

        // Shows an error in #written_con_error and re-activates the submit button so the
        // user can retry - either after fixing invalid fields, or after a failed request.
        function showWrittenConError($btn, message) {
            $btn.prop('disabled', false);
            $('#written_con_error')
                .html(message || 'Something went wrong while sending your request. Please try again.')
                .removeClass('simple_hide');
            $('body').scrollTo('#written_con_error', { duration: 'fast', offset: -180 });
        }

        $(document).on('click', '.start_written_con', function (event) {
            event.preventDefault();

            var $btn = $(this);
            $btn.prop('disabled', true); // deactivate the button

            var checker_message = '';
            var checker = false;

            // Required fields for written consultation
            $('#written_con_form').find('.required_wc').each(function () {
                if ($(this).val() === '') {
                    $(this).css('border-color', '#970808');
                    checker = true;
                    checker_message = d.msg.check1 + '<br>';
                }
            });

            // Email format check
            if (isEmail($('#client_email_ec').val()) === 'notOK') {
                $('#client_email_ec').css('border-color', '#ff5000');
                checker = true;
                checker_message = checker_message + d.msg.invalid_email + '<br>';
            }

            // reCAPTCHA check
            if (d.recaptcha.enabled && (typeof window.captchaCodeEmail === 'undefined' || window.captchaCodeEmail.length === 0)) {
                checker = true;
                checker_message += d.msg.robot;
            }

            // Legal checkbox
            if (!$('#conf_all_ea').is(':checked')) {
                checker = true;
                checker_message += d.msg.privacy + '<br>';
            }

            // File size check only for PDFs/files, not images
            const MAX_FILE_SIZE = 1.5 * 1024 * 1024;
            const pdfInputs = ['file_1', 'file_2', 'file_3'];
            const imageInputs = ['image_upload_1', 'image_upload_2', 'image_upload_3'];

            pdfInputs.forEach(id => {
                const el = document.getElementById(id);
                if (!el || !el.files || !el.files[0]) return;

                const file = el.files[0];
                if (file.size > MAX_FILE_SIZE) {
                    checker = true;
                    $('#' + id).css('border-color', '#970808');
                    checker_message += (file.name || id) + ' is larger than 1.5 MB.<br>';
                }
            });

            if (checker === false) {

                const imagePromises = imageInputs.map((id, index) => {
                    const input = document.getElementById(id);
                    const file = input && input.files ? input.files[0] : null;

                    if (!file || !file.type || !file.type.startsWith('image/')) {
                        return Promise.resolve($('#derma_pic_' + (index + 1)).val() || '');
                    }

                    return compressImage(file)
                        .then(compressedFile => {
                            if (!compressedFile) {
                                return '';
                            }

                            return new Promise((resolve, reject) => {
                                const reader = new FileReader();
                                reader.onload = function () {
                                    resolve(reader.result || '');
                                };
                                reader.onerror = function (err) {
                                    reject(err);
                                };
                                reader.readAsDataURL(compressedFile);
                            });
                        })
                        .then(base64String => {
                            $('#derma_pic_' + (index + 1)).val(base64String || '');
                            return base64String || '';
                        });
                });

                const pdfPromises = pdfInputs.map(id => {
                    const el = document.getElementById(id);
                    if (!el || !el.files || !el.files[0]) return Promise.resolve(null);

                    const file = el.files[0];
                    if (file.type !== 'application/pdf') return Promise.resolve(null);

                    return new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = function () {
                            resolve(reader.result);
                        };
                        reader.onerror = function (err) {
                            reject(err);
                        };
                        reader.readAsDataURL(file);
                    });
                });

                Promise.all(imagePromises)
                    .then(function (imageBase64s) {
                        return Promise.all(pdfPromises).then(function (pdfBase64s) {
                            return {
                                imageBase64s: imageBase64s,
                                pdfBase64s: pdfBase64s
                            };
                        });
                    })
                    .then(function (result) {
                        var imageBase64s = result.imageBase64s;
                        var pdfBase64s = result.pdfBase64s;

                        var myformData = new FormData($('#written_con_form')[0]);
                        myformData.append('action', 'd2gc_create_wcc_written_cosnsult');

                        // Make sure hidden derma fields are explicitly sent
                        imageBase64s.forEach(function (b64, index) {
                            myformData.set('derma_pic_' + (index + 1), b64 || $('#derma_pic_' + (index + 1)).val() || '');
                        });

                        // Remove raw image upload fields from FormData, since backend fallback uses derma_pic_X
                        imageInputs.forEach(function (id) {
                            myformData.delete(id);
                        });

                        // Attach PDF base64 data
                        pdfInputs.forEach(function (id, index) {
                            const b64 = pdfBase64s[index];
                            if (b64) {
                                myformData.set(id + '_base64', b64);
                            }
                        });

                        $.ajax({
                            type: 'POST',
                            data: myformData,
                            url: d.ajax.url,
                            processData: false,
                            contentType: false,
                            beforeSend: function () {
                                $('#loader').show();
                                $('#written_con_error').addClass('simple_hide');
                            },
                            success: function (response) {
                                console.log(response);
                                if (response && response.data && response.data.redirect_url) {
                                    window.location.href = response.data.redirect_url;
                                    // Leave the button disabled: we're navigating away.
                                    return;
                                }

                                // The backend responds with a bare "0"/no redirect_url on failure
                                // (nonce/recaptcha/validation failures all just return false server-side),
                                // so treat "no redirect_url" as an error rather than doing nothing.
                                var serverMessage = response && response.data && response.data.message;
                                showWrittenConError($btn, serverMessage);
                            },
                            error: function (xhr, textStatus, errorThrown) {
                                console.log(errorThrown);
                                showWrittenConError($btn);
                            },
                            complete: function () {
                                $('#loader').hide();
                            }
                        });
                    })
                    .catch(function (error) {
                        console.error('Image/PDF handling failed:', error);
                        $('#loader').hide();
                        showWrittenConError($btn, 'An error occurred while processing the files.');
                    });

            } else {
                showWrittenConError($btn, checker_message);
            }

            return false;
        });
    }


});


document.addEventListener('DOMContentLoaded', () => {
  const updateBtn = document.querySelector('.update_info');
  const complaintWrapper = document.getElementById('complaint_form_wrapper');

  if (!updateBtn || !complaintWrapper) {
    return;
  }

  updateBtn.addEventListener('click', function () {
    complaintWrapper.classList.remove('simple_hide');
    complaintWrapper.classList.add('show');
  });
});

(function () {
    const boundInputs = new WeakSet();

    function findFirstExistingId(ids) {
        for (const id of ids) {
            const el = document.getElementById(id);
            if (el) return el;
        }
        return null;
    }

    function getHiddenFieldForInput(inputId) {
        const map = {
            image_upload_1: ['derma_pic_1', 'dermapic1'],
            image_upload_2: ['derma_pic_2', 'dermapic2'],
            image_upload_3: ['derma_pic_3', 'dermapic3'],

            imageupload1: ['derma_pic_1', 'dermapic1'],
            imageupload2: ['derma_pic_2', 'dermapic2'],
            imageupload3: ['derma_pic_3', 'dermapic3'],

            booking_image_1: ['bookingdermapic1'],
            booking_image_2: ['bookingdermapic2'],
            booking_image_3: ['bookingdermapic3'],

            bookingimage1: ['bookingdermapic1'],
            bookingimage2: ['bookingdermapic2'],
            bookingimage3: ['bookingdermapic3']
        };

        const hiddenIds = map[inputId] || [];
        return findFirstExistingId(hiddenIds);
    }

    function clearPreview(input, preview) {
        if (!preview) return;

        const oldObjectUrl = preview.dataset.objectUrl;
        if (oldObjectUrl) {
            URL.revokeObjectURL(oldObjectUrl);
            delete preview.dataset.objectUrl;
        }

        preview.innerHTML = '';

        if (input && input.id) {
            const hiddenField = getHiddenFieldForInput(input.id);
            if (hiddenField) {
                hiddenField.value = '';
            }
        }
    }

    function renderPreviewWithRemove(input, preview, src, isObjectUrl) {
        if (!preview || !src) return;

        clearPreview(null, preview);

        preview.innerHTML = `
            <div class="position-relative w-100" style="aspect-ratio:1/1; overflow:hidden; border-radius:12px; background:#f2f2f2;">
                <img src="${src}" alt="Preview" style="width:100%; height:100%; object-fit:cover; display:block;">
                <button
                    type="button"
                    class="remove-btn"
                    title="Verwijder foto"
                    style="position:absolute; top:8px; right:8px; width:32px; height:32px; border:none; border-radius:50%; background:#fff; color:#000; font-size:20px; line-height:1; cursor:pointer; box-shadow:0 1px 4px rgba(0,0,0,.2);"
                >×</button>
            </div>
        `;

        if (isObjectUrl) {
            preview.dataset.objectUrl = src;
        }

        const removeBtn = preview.querySelector('.remove-btn');
        if (!removeBtn) return;

        removeBtn.addEventListener('click', function () {
            if (input) {
                input.value = '';
            }
            clearPreview(input, preview);
        });
    }

    function setupPreview(inputIds, previewIds) {
        const input = findFirstExistingId(inputIds);
        const preview = findFirstExistingId(previewIds);

        if (!input || !preview) return;
        if (boundInputs.has(input)) return;

        boundInputs.add(input);

        input.addEventListener('change', function () {
            const file = this.files && this.files[0] ? this.files[0] : null;

            if (file && file.size > 10 * 1024 * 1024) {
                alert(d2gPublicData.msg.img_to_big);
                this.value = '';
                clearPreview(this, preview);
                return;
            }

            if (!file) {
                clearPreview(this, preview);
                return;
            }

            const imgUrl = URL.createObjectURL(file);
            renderPreviewWithRemove(this, preview, imgUrl, true);

            const hiddenField = getHiddenFieldForInput(this.id);
            if (hiddenField) {
                hiddenField.value = '';
            }
        });
    }

    function initUploadPreviews() {
        setupPreview(
            ['image_upload_1', 'imageupload1'],
            ['image_placeholder_1', 'imageplaceholder1']
        );
        setupPreview(
            ['image_upload_2', 'imageupload2'],
            ['image_placeholder_2', 'imageplaceholder2']
        );
        setupPreview(
            ['image_upload_3', 'imageupload3'],
            ['image_placeholder_3', 'imageplaceholder3']
        );

        setupPreview(
            ['booking_image_1', 'bookingimage1'],
            ['booking_image_placeholder_1', 'bookingimageplaceholder1']
        );
        setupPreview(
            ['booking_image_2', 'bookingimage2'],
            ['booking_image_placeholder_2', 'bookingimageplaceholder2']
        );
        setupPreview(
            ['booking_image_3', 'bookingimage3'],
            ['booking_image_placeholder_3', 'bookingimageplaceholder3']
        );
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUploadPreviews);
    } else {
        initUploadPreviews();
    }
})();

