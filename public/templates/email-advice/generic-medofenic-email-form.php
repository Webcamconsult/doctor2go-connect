<?php if ( ! defined( 'ABSPATH' ) ) exit; 
global $d2g_profile_data, $currUser, $userMeta, $type;
//nice_dump($d2g_profile_data);
$site_key = get_option('d2gc_recaptcha_site_key');
$doctor_name = $d2g_profile_data->doctor->post_title;
//echo $doctor_name;
if (is_user_logged_in()) {
    $currUser = wp_get_current_user();
    $userMeta = get_user_meta($currUser->data->ID);
}
?>
<div id="written_consult" class="walkin_form_wrapper d2g_wrapper">
    <div class="alert alert-warning info_notes mb-3">
        <?php echo esc_html__( 'E-mail consultations are intended for non-urgent situations and may be used for medical guidance, interpretation of medical tests or documents, 
        and obtaining a medical opinion. For medical emergencies, please contact emergency services or seek direct medical care.', 'doctor2go-connect' ); ?>
    </div>
    <div class="alert alert-danger simple_hide" id="written_con_error"></div>
    <div class="walkin_form_inner_wrapper mb-s">
        <form id="written_con_form" method="post" action="" enctype="multipart/form-data">
            <?php wp_nonce_field( 'email_advice_form_action', 'email_advice_form_nonce' ); ?>
            <input type="hidden" name="wp_doc_id" value="<?php echo esc_html( $d2g_profile_data->doctor_profile_ID ); ?>"> 
            <div class="card mb-5">
                <div class="card-body">   
                    <!-- Complaint description -->
                    <legend class="fs-5 mb-3">
                        <strong><?php echo esc_html__('About your complaint', 'doctor2go-connect')?></strong>
                    </legend>
                    <div class="mb-3">
                        <label for="beschrijf_de_klacht" class="form-label">
                            <?php echo esc_html__('Describe the complaint', 'doctor2go-connect')?> *
                        </label>
                        <textarea id="beschrijf_de_klacht" name="complaint_description" class="form-control required_wc" rows="3" placeholder="<?php echo esc_attr__('For example: itchy red spots or bumps...', 'doctor2go-connect')?>"></textarea>
                    </div>
                    <!-- images -->
                    <fieldset class="mb-4">
                        <legend class="fs-5 mb-3">
                            <strong><?php echo esc_html__('Image uploads (optional), allowed files types: JPG, PNG, GIF ', 'doctor2go-connect')?></strong>
                        </legend>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="image_1" class="form-label"><?php echo esc_html__( 'Upload image 1', 'doctor2go-connect' ); ?></label>
                                <input class="form-control" type="file" name="image_1" id="image_1" accept="image/*,android/allowCamera">
                            </div>
                            <div class="col-md-4">
                                <label for="image_2" class="form-label"><?php echo esc_html__( 'Upload image 2', 'doctor2go-connect' ); ?></label>
                                <input class="form-control" type="file" name="image_2" id="image_2" accept="image/*,android/allowCamera">
                            </div>
                            <div class="col-md-4">
                                <label for="image_3" class="form-label"><?php echo esc_html__( 'Upload image 3', 'doctor2go-connect' ); ?></label>
                                <input class="form-control" type="file" name="image_3" id="image_3" accept="image/*,android/allowCamera">
                            </div>
                        </div> 
                    </fieldset>
                    <!-- files (pdf) -->
                    <fieldset class="mb-4">
                        <legend class="fs-5 mb-3">
                            <strong><?php echo esc_html__('PDF file uploads, max 1.5mb / per file (optional)', 'doctor2go-connect')?></strong>
                        </legend>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="file_1" class="form-label"><?php echo esc_html__( 'Upload file 1', 'doctor2go-connect' ); ?></label>
                                <input class="form-control" type="file" name="file_1" id="file_1" accept="application/pdf">
                            </div>
                            <div class="col-md-4">
                                <label for="file_2" class="form-label"><?php echo esc_html__( 'Upload file 2', 'doctor2go-connect' ); ?></label>
                                <input class="form-control" type="file" name="file_2" id="file_2" accept="application/pdf">
                            </div>
                            <div class="col-md-4">
                                <label for="file_3" class="form-label"><?php echo esc_html__( 'Upload file 3', 'doctor2go-connect' ); ?></label>
                                <input class="form-control" type="file" name="file_3" id="file_3" accept="application/pdf">
                            </div>
                        </div> 
                    </fieldset>
                    <!-- Complaint description -->
                    <legend class="fs-5 mb-3">
                        <strong><?php echo esc_html__('Known medical conditions and current treatments', 'doctor2go-connect')?></strong>
                    </legend>
                    <div class="mb-3">
                        <label for="history" class="form-label">
                            <?php echo esc_html__('Please mention any important known medical conditions and current treatments.', 'doctor2go-connect')?> *
                        </label>
                        <textarea id="history" name="medical_history" class="form-control required_wc" rows="3" placeholder="<?php echo esc_html__('For example: diabetes, high blood pressure, thyroid disorders, anticoagulant treatment, or any other important ongoing treatments.', 'doctor2go-connect')?>"></textarea>
                    </div>
                </div>
            </div>
            <div class="card mb-5">
                <div class="card-body">
                    <!-- Personal information -->
                    <legend class="fs-5 mb-3">
                        <strong><?php echo esc_html__('Personal information', 'doctor2go-connect')?></strong>
                    </legend>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <div>
                                <label for="first_name"><?php echo esc_html__( 'First name', 'doctor2go-connect' ); ?> *</label>
                                <input class="required_wc form-control" type="text" value="<?php echo esc_html( $userMeta['first_name'][0] ); ?>" name="first_name" id="first_name">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div>
                                <label for="last_name"><?php echo esc_html__( 'Last name', 'doctor2go-connect' ); ?> *</label>
                                <input class="required_wc form-control" type="text" value="<?php echo esc_html( $userMeta['last_name'][0] ); ?>" name="last_name" id="last_name">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div>
                                <label for="client_email"><?php echo esc_html__( 'E-mail', 'doctor2go-connect' ); ?> *</label>
                                <input class="required_wc form-control" type="text" value="<?php echo esc_html( $currUser->data->user_email ); ?>" name="client_email" id="client_email_ec">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <div>
                                <label class="form-label" for="option_bday"><?php echo esc_html__( 'Date of Birth: day/month/year  ', 'doctor2go-connect' ); ?></label>
                                <input class="form-control" type="date"  name="option_bday" id="option_bday" value="<?php echo esc_html( $userMeta['p_bday'][0] ); ?>">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div>
                                <label class="form-label" for="optie_aanhef"><?php echo esc_html__( 'Gender', 'doctor2go-connect' ); ?></label>
                                <select name="optie_aanhef" class="form-select" id="optie_aanhef">
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
            <div class="card mb-4">
                <div class="card-body">
                    <legend class="fs-5 mb-3">
                        <strong><?php echo esc_html__('Your consultation', 'doctor2go-connect') ?></strong>
                    </legend>
                    <p>
                        <?php
                        /* translators: %s is the doctor's name. */
                        echo esc_html(
                            sprintf(
                                __('You will receive %s’s written assessment within 2 working days by email.', 'doctor2go-connect'),
                                $doctor_name
                            )
                        );
                        ?>
                    </p>
                    <p class="mb-3 border-top border-bottom py-3 d-flex h4 align-items-center justify-content-between">
                        <span class="me-3"><strong><?php echo esc_html__('E-mail advice', 'doctor2go-connect'); ?></strong></span>
                        <span class="price_wrapper">
                            <strong><?php echo esc_html($d2g_profile_data->doctor_meta['written_con_currency'][0] . ' ' . $d2g_profile_data->doctor_meta['written_con_price'][0]); ?></strong><br>
                            <small class="text-muted"><?php echo esc_html__('(excl. VAT)', 'doctor2go-connect'); ?></small>
                        </span>
                    </p>
                    <?php if (get_option('d2gc_recaptcha_site_key')) { ?>
                        <div class="mb-3">
                            <!-- reCAPTCHA Widget -->
                            <div class="g-recaptcha mb-s" data-sitekey="<?php echo esc_attr($site_key); ?>"></div>
                            <div id="captcha_email"></div>
                        </div>
                    <?php } ?>
                    <div class="mb-3">
                        <?php d2gc_confirmation_checkboxes('_ea'); ?>
                    </div>
                    <div class="mb-3 d-flex align-items-center">
                        <input readonly type="hidden" name="written_con_type" value="<?php echo esc_attr($type); ?>">
                        <input readonly type="hidden" id="derma_pic_1" name="derma_pic_1" value="">
                        <input readonly type="hidden" id="derma_pic_2" name="derma_pic_2" value="">
                        <input readonly type="hidden" id="derma_pic_3" name="derma_pic_3" value="">
                        <button class="btn btn-primary wp-block-button__link start_written_con button" tabindex="6" id="save"><?php esc_html_e('continue and pay', 'doctor2go-connect'); ?></button>
                        <div id="loader" class="spinner-border text-primary ms-2" role="status" style="display:none;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <p><?php echo esc_html__('* required fields.', 'doctor2go-connect'); ?></p>
                </div>
            </div>
        </form>
    </div>
    <p><?php echo esc_html__( '* required fields.', 'doctor2go-connect' ); ?></p>
</div>