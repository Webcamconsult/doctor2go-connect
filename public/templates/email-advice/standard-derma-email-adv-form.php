<?php if ( ! defined( 'ABSPATH' ) ) exit; 
global $d2g_profile_data, $currUser, $userMeta, $type;
//nice_dump($d2g_profile_data);
$site_key = get_option( 'd2gc_recaptcha_site_key' );
$doctor_name = $d2g_profile_data->doctor->post_title;
//echo $doctor_name;
if ( is_user_logged_in() ) {
    $currUser = wp_get_current_user();
    $userMeta = get_user_meta( $currUser->data->ID );
}
?>
<div id="written_consult" class="walkin_form_wrapper d2g_wrapper">
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
                                <label class="form-label" for="option_bday"><?php echo esc_html__( 'Date of Birth: month/year  ', 'doctor2go-connect' ); ?></label>
                                <input class="form-control" max="2012-12-31" type="month" name="option_bday" id="option_bday" value="<?php echo esc_html( $userMeta['p_bday'][0] ); ?>" placeholder="<?php echo esc_attr__( 'Select your date of birth', 'doctor2go-connect' ); ?>">
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
            
             <div class="card mb-5 ">
                <div class="card-body">
                    <legend class="fs-5 mb-3">
                        <strong><?php echo esc_html__('About your complaint', 'doctor2go-connect')?></strong>
                    </legend>
                     <?php if ( isset( $_GET['use_ai_info'] ) && '1' === $_GET['use_ai_info'] ) { ?>
                        <div class="opener"><strong><?php echo esc_html__( 'Edit your information', 'doctor2go-connect' ); ?></strong><span class="icon-down-open text-small"></span><p class="text-small"><?php echo esc_html__( 'Pre-filled from your AI check.', 'doctor2go-connect' ); ?></p></div>
                    <?php } ?>
                    <fieldset id="complaint_form_wrapper" class="mb-4 <?php echo ($_GET['use_ai_info'] === '1') ? 'simple_hide' : 'show'; ?>">
                        
                        <div class="row mb-3">
                            <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="image_upload_1" class="form-label btn btn-outline-primary">
                                        <strong><?php echo ($type == 'derma_email_advice') ? esc_html__('Photo of the skin condition (above)', 'doctor2go-connect') : esc_html__('Photo of the condition (above)', 'doctor2go-connect') ; ?></strong>
                                    </label>
                                    <div class="image_placeholder border rounded mb-2 p-2" id="image_placeholder_1"></div>
                                    <input type="file" class="form-control simple_hide" id="image_upload_1" name="skin_photo[]" accept="image/*">
                                </div>
                            </div>
                            <?php if ( wpmd_is_phone() ) { ?>
                                <p class="text-primary text-decoration-underline mb-3 opener">
                                    <?php echo esc_html__('Upload / make more photos', 'ai-derma-plugin')?>
                                </p>
                                <div class="simple_hide">
                            <?php } ?>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="image_upload_2" class="form-label btn btn-outline-primary">
                                        <strong><?php echo ($type == 'derma_email_advice') ? esc_html__('Photo of the skin condition (side)', 'doctor2go-connect') : esc_html__('Photo of the condition (side)', 'doctor2go-connect') ; ?></strong>
                                    </label>
                                    <div class="image_placeholder border rounded mb-2 p-2" id="image_placeholder_2"></div>
                                    <input type="file" class="form-control simple_hide " id="image_upload_2" name="skin_photo[]" accept="image/*">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="image_upload_3" class="form-label btn btn-outline-primary">
                                        <strong><?php echo ($type == 'derma_email_advice') ? esc_html__('Photo of the skin condition (extra)', 'doctor2go-connect') : esc_html__('Photo of the condition (extra)', 'doctor2go-connect') ; ?></strong>
                                    </label>
                                    <div class="image_placeholder border rounded mb-2 p-2" id="image_placeholder_3"></div>
                                    <input type="file" class="form-control simple_hide " id="image_upload_3" name="skin_photo[]" accept="image/*">
                                </div>
                            </div>
                            <?php if ( wpmd_is_phone() ) { ?>
                                </div>
                            <?php } ?>
                        </div>
                        
                        
                        <?php if($type == 'derma_email_advice'){?>
                            <!-- only DERMA -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="opgemerkt" class="form-label">
                                        <?php echo ($type == 'derma_email_advice') ? esc_html__('When did you first notice your skin problem', 'doctor2go-connect') : esc_html__('When did you first notice your complaint', 'doctor2go-connect') ; ?> *
                                    </label>
                                    <input type="text" id="opgemerkt" name="first_noticed" class="form-control required_wc" rows="2" placeholder="<?php echo esc_attr__('since...days,weeks,months, years', 'doctor2go-connect')?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="locatie" class="form-label">
                                        <?php echo ($type == 'derma_email_advice') ? esc_html__('Location(s) on the body', 'doctor2go-connect') : esc_html__('Location(s) of your complaint', 'doctor2go-connect') ; ?> *
                                    </label>
                                    <input type="text" id="locatie" name="location" class="form-control required_wc" placeholder="<?php echo esc_attr__('For example: left forearm', 'doctor2go-connect')?>">
                                </div>
                            </div>
                            <input type="hidden" id="email_ai_info" name="email_ai_info" value="">
                        <?php } ?>

                        <!-- Beschrijving / eerste opgemerkt -->
                        <div class="mb-3">
                            <label for="beschrijf_de_klacht" class="form-label">
                                <?php echo ($type == 'derma_email_advice') ? esc_html__('Describe your skin problem (color, size, growing, bleeding, itching, occasional or continuous) ', 'doctor2go-connect'): esc_html__('Describe your complaint (how it looks like, what you feel, level of pain,  occasional or continuous) ', 'doctor2go-connect')?> *
                            </label>
                            <textarea id="beschrijf_de_klacht" name="complaint_description" class="form-control required_wc" rows="3" placeholder="<?php echo esc_attr__('For example: itchy red spots or bumps...', 'doctor2go-connect')?>"></textarea>
                        </div>

                        <?php if($type == 'derma_email_advice'){?>
                            <!-- only DERMA -->
                             <div class="mb-3">
                                <label for="treatment_history" class="form-label">
                                    <?php echo ($type == 'derma_email_advice') ? esc_html__('Treatment history (what medications did you use for your skin problem and what was the result)', 'ai-derma-plugin') : esc_html__('Treatment history (what medications did you use for your complaint and what was the result)', 'ai-derma-plugin')?>
                                </label>
                                <textarea id="treatment_history" name="treatment_history" class="form-control" rows="3" placeholder="<?php echo esc_attr__('If applicable...', 'ai-derma-plugin')?>"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="history" class="form-label">
                                    <?php echo ($type == 'derma_email_advice') ? esc_html__('Medical history (other relevant medical conditions and medications)', 'ai-derma-plugin') : esc_html__('Medical history (other relevant medical conditions and medications)', 'ai-derma-plugin')?>
                                </label>
                                <textarea id="history" name="medical_history" class="form-control" rows="3" placeholder="<?php echo esc_attr__('If applicable...', 'ai-derma-plugin')?>"></textarea>
                            </div>
                        <?php } ?>
                        
                    </fieldset>
                </div>
             </div>
             <div class="card mb-4">
                <div class="card-body">
                    
                    <legend class="fs-5 mb-3">
                        <strong><?php echo esc_html__('Your consultation', 'doctor2go-connect')?></strong>
                    </legend>
                    <p>
                        <?php
                        /* translators: %s is the doctor's name. */
                        echo esc_html(
                            sprintf(
                                __( 'You will receive %s’s written assessment within 2 working days by email.', 'doctor2go-connect' ),
                                $doctor_name
                            )
                        );
                        ?>
                    </p>
                    <p class="mb-3 border-top border-bottom py-3 d-flex h4 align-items-center justify-content-between">
                        <span class="me-3"><strong><?php echo esc_html__( 'E-mail advice', 'doctor2go-connect' ); ?></strong></span>
                        <span class="price_wrapper">
                            <strong><?php echo esc_html( $d2g_profile_data->doctor_meta['written_con_currency'][0] . ' ' . $d2g_profile_data->doctor_meta['written_con_price'][0] ); ?></strong><br>
                            <small class="text-muted">(excl. VAT)</small>
                        </span>
                    </p>
                    <?php if ( get_option( 'd2gc_recaptcha_site_key' ) ) { ?>
                        <div class="mb-3">
                            <!-- reCAPTCHA Widget -->
                            <div class="g-recaptcha mb-s" data-sitekey="<?php echo esc_attr( $site_key ); ?>"></div>
                            <div id="captcha_email"></div>
                        </div>
                    <?php } ?>
                    <div class="mb-3">
                        <?php d2gc_confirmation_checkboxes( '_ea' ); ?>
                    </div>
                    <div class="mb-3 d-flex align-items-center">
                        <input readonly type="hidden" name="written_con_type" value="<?php echo esc_attr( $type ); ?>">
                        <input readonly type="hidden" id="derma_pic_1" name="derma_pic_1" value="">
                        <input readonly type="hidden" id="derma_pic_2" name="derma_pic_2" value="">
                        <input readonly type="hidden" id="derma_pic_3" name="derma_pic_3" value="">
                        <button class="btn btn-primary wp-block-button__link start_written_con button" tabindex="6" id="save"><?php esc_html_e( 'continue and pay', 'doctor2go-connect' ); ?></button>
                        <div id="loader" class="spinner-border text-primary ms-2" role="status" style="display:none;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <p><?php echo esc_html__( '* required fields.', 'doctor2go-connect' ); ?></p>
                </div>
             </div>
            
            
            
        </form>
    </div>
    
</div>