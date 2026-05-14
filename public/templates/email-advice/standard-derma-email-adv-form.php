<?php if ( ! defined( 'ABSPATH' ) ) exit; 
global $d2g_profile_data, $currUser, $userMeta, $type;
$site_key = get_option( 'd2gc_recaptcha_site_key' );
if ( is_user_logged_in() ) {
    $currUser = wp_get_current_user();
    $userMeta = get_user_meta( $currUser->data->ID );
}
?>
<div id="written_consult" class="walkin_form_wrapper d2g_wrapper">
    <h3 class="section_title"><?php echo esc_html__( 'E-mail advice', 'doctor2go-connect' ); ?></h3>
    <span class="price_wrapper">
        <p style="margin-bottom: 2px;"><?php echo esc_html__( 'Consultation fee:', 'doctor2go-connect' ); ?></p>
        <strong><?php echo esc_html( $d2g_profile_data->doctor_meta['written_con_currency'][0] . ' ' . $d2g_profile_data->doctor_meta['written_con_price'][0] ); ?></strong>
    </span>
    <?php if ( function_exists( 'd2gc_ai_summary' ) && $_GET['use_ai_info'] === '1' ) {?>
        <div class="alert alert-light info_notes mb-5 ">
            <p><strong><?php echo esc_html__( 'Receive professional advice about your free derma AI inquiry from this certified dermatologist by email within two working days', 'doctor2go-connect' ); ?></strong></p>
            <ol class="mb-3">
                <li><span><?php echo esc_html__( 'You only need to provide your personal details, the complaint description, and pictures will be used from the derma AI form. If you would like to update any information, please do so before proceeding. To do so click on the button update information.', 'doctor2go-connect' ); ?></span></li>
                <li><span><?php echo esc_html__( 'Click pay and continue, you will be redirected to the payment page.', 'doctor2go-connect' ); ?></span></li>
                <li><span><?php echo esc_html__( 'After payment, you will receive your assessment within 2 working days.', 'doctor2go-connect' ); ?></span></li>
            </ol>
            <a class="btn btn-primary update_info scroll_to" href="#personal_info">
                <?php echo esc_html__( 'Update Information', 'doctor2go-connect' ); ?>
            </a>
        </div>
    <?php } else { ?>
        <div class="alert alert-light info_notes mb-5 ">
            <p><strong><?php echo esc_html__( 'Obtain a professional assessment from a certified dermatologist by email within two working days through a straightforward three-step process.', 'doctor2go-connect' ); ?></strong></p>
            <ol>
                <li><span><?php echo esc_html__( 'Enter your personal information and describe your complaint', 'doctor2go-connect' ); ?></span></li>
                <li><span><?php echo esc_html__( 'Click pay and continue, you will be redirected to the payment page.', 'doctor2go-connect' ); ?></span></li>
                <li><span><?php echo esc_html__( 'After payment, you will receive your assessment within 2 working days.', 'doctor2go-connect' ); ?></span></li>
            </ol>
        </div>
    <?php }?>
    
    
    <div class="alert alert-danger simple_hide" id="written_con_error"></div>
    <div class="walkin_form_inner_wrapper mb-s">
        <form id="written_con_form" method="post" action="" enctype="multipart/form-data">
            <?php wp_nonce_field( 'email_advice_form_action', 'email_advice_form_nonce' ); ?>
            <input type="hidden" name="wp_doc_id" value="<?php echo esc_html( $d2g_profile_data->doctor_profile_ID ); ?>"> 
            <div class="row mb-3 simple_hide">
                <div class="col-sm-12">
                    <div>
                        <input id="type_small" class="required_wc form-control" type="radio" value="short" name="type" checked>
                        <label for="type_small"><?php echo esc_html__( 'Short Questionnaire – for simple or minor skin issues', 'doctor2go-connect' ); ?></label>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div>
                        <input id="type_default" class="required_wc form-control" type="radio" value="default" name="type">
                        <label class="form-label" for="type_default"><?php echo esc_html__( 'Extended Questionnaire – for complex or multiple skin concerns', 'doctor2go-connect' ); ?></label> 
                    </div>  
                </div>
            </div>
            <div id="personal_info" class="card mb-5">
                <div class="card-body">
                    <legend class="fs-5 mb-3">
                        <strong><?php echo esc_html__('Personal information', 'doctor2go-connect')?></strong>
                    </legend>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <div>
                                <label for="first_name"><?php echo esc_html__( 'First name', 'doctor2go-connect' ); ?> *</label>
                                <input class="required_wc form-control" type="text" value="<?php echo esc_html( $userMeta['first_name'][0] ); ?>" name="first_name" id="first_name" placeholder="<?php echo esc_attr__( 'Enter your first name', 'doctor2go-connect' ); ?>">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div>
                                <label for="last_name"><?php echo esc_html__( 'Last name', 'doctor2go-connect' ); ?> *</label>
                                <input class="required_wc form-control" type="text" value="<?php echo esc_html( $userMeta['last_name'][0] ); ?>" name="last_name" id="last_name" placeholder="<?php echo esc_attr__( 'Enter your last name', 'doctor2go-connect' ); ?>">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div>
                                <label for="client_email"><?php echo esc_html__( 'E-mail', 'doctor2go-connect' ); ?> *</label>
                                <input class="required_wc form-control" type="text" value="<?php echo esc_html( $currUser->data->user_email ); ?>" name="client_email" id="client_email_ec" placeholder="<?php echo esc_attr__( 'Enter your email address', 'doctor2go-connect' ); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <div>
                                <label class="form-label" for="option_bday"><?php echo esc_html__( 'Date of Birth: day/month/year  ', 'doctor2go-connect' ); ?></label>
                                <input class="form-control" type="date" name="option_bday" id="option_bday" value="<?php echo esc_html( $userMeta['p_bday'][0] ); ?>" placeholder="<?php echo esc_attr__( 'Select your date of birth', 'doctor2go-connect' ); ?>">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div>
                                <label class="form-label" for="optie_aanhef"><?php echo esc_html__( 'Gender', 'doctor2go-connect' ); ?></label>
                                <select name="optie_aanhef" class="form-select" id="optie_aanhef" placeholder="<?php echo esc_attr__( 'Select your gender', 'doctor2go-connect' ); ?>">
                                    <option <?php echo ( '0' == $userMeta['p_gender'][0] ) ? 'selected' : ''; ?> value="0"><?php echo esc_html__( 'make a choice', 'doctor2go-connect' ); ?></option>
                                    <option <?php echo ( 'male' == $userMeta['p_gender'][0] ) ? 'selected' : ''; ?> value="<?php echo esc_html__( 'male', 'doctor2go-connect' ); ?>"><?php echo esc_html__( 'male', 'doctor2go-connect' ); ?></option>
                                    <option <?php echo ( 'female' == $userMeta['p_gender'][0] ) ? 'selected' : ''; ?> value="<?php echo esc_html__( 'female', 'doctor2go-connect' ); ?>"><?php echo esc_html__( 'female', 'doctor2go-connect' ); ?></option>
                                    <option <?php echo ( 'other' == $userMeta['p_gender'][0] ) ? 'selected' : ''; ?> value="<?php echo esc_html__( 'other', 'doctor2go-connect' ); ?>"><?php echo esc_html__( 'other', 'doctor2go-connect' ); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            
                        </div>  
                    </div>
                </div>
            </div>
            <!-- Over de huidaandoening -->
             <div id="complaint_form_wrapper" class="card <?php echo ($_GET['use_ai_info'] === '1') ? 'simple_hide' : 'show'; ?>">
                <div class="card-body">
                    <fieldset class="mb-4">
                        <legend class="fs-5 mb-3">
                            <strong><?php echo esc_html__('About your complaint', 'doctor2go-connect')?></strong>
                        </legend>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="image_1" class="form-label"><?php echo esc_html__( 'Upload image 1', 'doctor2go-connect' ); ?></label>
                                <input class="form-control" type="file" name="image_1" id="image_1" accept="image/*" placeholder="<?php echo esc_attr__( 'Choose image 1', 'doctor2go-connect' ); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="image_2" class="form-label"><?php echo esc_html__( 'Upload image 2', 'doctor2go-connect' ); ?></label>
                                <input class="form-control" type="file" name="image_2" id="image_2" accept="image/*" placeholder="<?php echo esc_attr__( 'Choose image 2', 'doctor2go-connect' ); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="image_3" class="form-label"><?php echo esc_html__( 'Upload image 3', 'doctor2go-connect' ); ?></label>
                                <input class="form-control" type="file" name="image_3" id="image_3" accept="image/*" placeholder="<?php echo esc_attr__( 'Choose image 3', 'doctor2go-connect' ); ?>">
                            </div>
                        </div>
                        
                        <!-- Beschrijving / eerste opgemerkt -->
                        <div class="mb-3">
                            <label for="beschrijf_de_klacht" class="form-label">
                                <?php echo esc_html__('Describe the complaint', 'doctor2go-connect')?> *
                            </label>
                            <textarea id="beschrijf_de_klacht" name="complaint_description" class="form-control required_wc" rows="3" placeholder="<?php echo esc_attr__('For example: itchy red spots or bumps...', 'doctor2go-connect')?>"></textarea>
                        </div>
                        <?php if($type == 'derma_email_advice'){?>
                            <div class="mb-3">
                                <label for="opgemerkt" class="form-label">
                                    <?php echo esc_html__('When did you first notice this spot?', 'doctor2go-connect')?> *
                                </label>
                                <textarea id="opgemerkt" name="first_noticed" class="form-control required_wc" rows="2" placeholder="<?php echo esc_attr__('For example: 2 weeks ago...', 'doctor2go-connect')?>"></textarea>
                            </div>

                            <!-- Locatie / veranderd -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="locatie" class="form-label">
                                        <?php echo esc_html__('Location on the body', 'doctor2go-connect')?> *
                                    </label>
                                    <input type="text" id="locatie" name="location" class="form-control required_wc" placeholder="<?php echo esc_attr__('For example: left forearm', 'doctor2go-connect')?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="veranderd" class="form-label">
                                        <?php echo esc_html__('Has the spot changed? Choose one or more options.', 'doctor2go-connect')?>
                                    </label>
                                    <select id="veranderd" name="has_changed[]" class="form-select" multiple placeholder="<?php echo esc_attr__('Select one or more options', 'doctor2go-connect'); ?>">
                                        <option value="<?php echo esc_attr__('no', 'doctor2go-connect'); ?>">
                                            <?php echo esc_html__('No', 'doctor2go-connect'); ?>
                                        </option>
                                        <option value="<?php echo esc_attr__('yes, in size', 'doctor2go-connect'); ?>">
                                            <?php echo esc_html__('Yes, in size', 'doctor2go-connect'); ?>
                                        </option>
                                        <option value="<?php echo esc_attr__('yes, in color', 'doctor2go-connect'); ?>">
                                            <?php echo esc_html__('Yes, in color', 'doctor2go-connect'); ?>
                                        </option>
                                        <option value="<?php echo esc_attr__('yes, in shape', 'doctor2go-connect'); ?>">
                                            <?php echo esc_html__('Yes, in shape', 'doctor2go-connect'); ?>
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Symptomen switches -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="jeuk" name="itch_check" value="<?php echo esc_html__('Yes', 'doctor2go-connect'); ?>" role="switch">
                                        <label class="form-check-label" for="jeuk">
                                            <?php echo esc_html__('Does the skin condition itch?', 'doctor2go-connect')?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="bloed" name="blood_check" value="<?php echo esc_html__('Yes', 'doctor2go-connect'); ?>" role="switch">
                                        <label class="form-check-label" for="bloed">
                                            <?php echo esc_html__('Does the skin condition bleed?', 'doctor2go-connect')?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="history" class="form-label">
                                    <?php echo esc_html__('Medical history (skin cancer)', 'doctor2go-connect')?>
                                </label>
                                <textarea id="history" name="medical_history" class="form-control" rows="3" placeholder="<?php echo esc_attr__('If applicable...', 'doctor2go-connect')?>"></textarea>
                            </div>
                        <?php } ?>
                        
                    </fieldset>
                </div>
             </div>
            <div class="mb-3">
                <!-- reCAPTCHA Widget -->
                <?php if ( get_option( 'd2gc_recaptcha_site_key' ) ) { ?>
                    <div class="g-recaptcha mb-s" data-sitekey="<?php echo esc_attr( $site_key ); ?>"></div>
                    <div id="captcha_email"></div>
                <?php } ?>
            </div>
            <?php if ( ! is_user_logged_in() ) { ?>
                <?php d2gc_confirmation_checkboxes( '_ea' ); ?>
            <?php } ?>
            <div class="mb-4 d-flex align-items-center">
                <input readonly type="hidden" name="written_con_type" value="<?php echo esc_attr( $type ); ?>">
                <input readonly type="hidden" id="derma_pic_1" name="derma_pic_1" value="">
                <input readonly type="hidden" id="derma_pic_2" name="derma_pic_2" value="">
                <input readonly type="hidden" id="derma_pic_3" name="derma_pic_3" value="">
                <button class="btn btn-primary wp-block-button__link start_written_con button" tabindex="6" id="save"><?php esc_html_e( 'continue and pay', 'doctor2go-connect' ); ?></button>
                <div id="loader" class="spinner-border text-primary ms-2" role="status" style="display:none;">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </form>
    </div>
    <p><?php echo esc_html__( '* required fields.', 'doctor2go-connect' ); ?></p>
</div>