<?php if ( ! defined( 'ABSPATH' ) ) exit; 
global $d2g_profile_data, $currUser, $userMeta, $form_type;
if ( is_user_logged_in() ) {
    $currUser = wp_get_current_user();
    $userMeta = get_user_meta( $currUser->data->ID );
}
?>
<!-- Over de huidaandoening -->
<div class="card mb-5 ">
    <div class="card-body"> 
        <legend class="fs-5 mb-3">
            <strong><?php echo esc_html__('About your complaint (optional)', 'doctor2go-connect')?></strong>
        </legend>
         <?php if ( isset( $_GET['use_ai_info'] ) && '1' === $_GET['use_ai_info'] ) { ?>
            <div class="opener"><strong><?php echo esc_html__( 'Edit your information', 'doctor2go-connect' ); ?></strong><span class="icon-down-open text-small"></span><p class="text-small"><?php echo esc_html__( 'Pre-filled from your AI check.', 'doctor2go-connect' ); ?></p></div>
        <?php } else { ?>
            <p class="opener"><strong><?php echo esc_html__('Click here to provide / update your complaint information', 'doctor2go-connect')?></strong><span class="icon-down-open"></span></p> 
        <?php } ?>
        <div id="booking_complaint_form_wrapper" class=" <?php echo ($_GET['use_ai_info'] === '1') ? 'simple_hide' : 'show'; ?>">
            <fieldset class="mb-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="booking_image_1" class="form-label btn btn-outline-primary">
                                <strong><?php echo ($form_type == 'derma_email_advice') ? esc_html__('Photo of the skin condition (above)', 'doctor2go-connect') : esc_html__('Photo of the condition (above)', 'doctor2go-connect') ; ?></strong>
                            </label>
                            <div class="image_placeholder border rounded mb-2 p-2" id="booking_image_placeholder_1"></div>
                            <input class="form-control simple_hide" type="file" name="booking_image_1" id="booking_image_1" accept="image/*" placeholder="<?php echo esc_attr__( 'Choose image 1', 'doctor2go-connect' ); ?>">
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
                            <label for="booking_image_2" class="form-label btn btn-outline-primary">
                                <strong><?php echo ($form_type == 'derma_email_advice') ? esc_html__('Photo of the skin condition (side)', 'doctor2go-connect') : esc_html__('Photo of the condition (side)', 'doctor2go-connect') ; ?></strong>
                            </label>
                            <div class="image_placeholder border rounded mb-2 p-2" id="booking_image_placeholder_2"></div>
                            <input class="form-control simple_hide" type="file" name="booking_image_2" id="booking_image_2" accept="image/*" placeholder="<?php echo esc_attr__( 'Choose image 2', 'doctor2go-connect' ); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="booking_image_3" class="form-label btn btn-outline-primary">
                                <strong><?php echo ($form_type == 'derma_email_advice') ? esc_html__('Photo of the skin condition (extra)', 'doctor2go-connect') : esc_html__('Photo of the condition (extra)', 'doctor2go-connect') ; ?></strong>
                            </label>
                            <div class="image_placeholder border rounded mb-2 p-2" id="booking_image_placeholder_3"></div>
                            <input class="form-control simple_hide" type="file" name="booking_image_3" id="booking_image_3" accept="image/*" placeholder="<?php echo esc_attr__( 'Choose image 3', 'doctor2go-connect' ); ?>">
                        </div>
                    </div>
                    <?php if ( wpmd_is_phone() ) { ?>
                        </div>
                    <?php } ?>
                </div>
                
                
                <!-- extra velden voor derma sites -->
                <?php if($form_type == 'derma_email_advice'){?>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="opgemerkt" class="form-label">
                                <?php echo ($form_type == 'derma_email_advice') ? esc_html__('When did you first notice your skin problem?', 'doctor2go-connect') : esc_html__('When did you first notice your complaint?', 'doctor2go-connect') ; ?> *
                            </label>
                            <input type="text" id="booking_opgemerkt" name="first_noticed" class="form-control myrequired" rows="2" placeholder="<?php echo esc_attr__('since...days,weeks,months, years', 'doctor2go-connect')?>">
                        </div>
                        <div class="col-md-6">
                            <label for="locatie" class="form-label">
                                <?php echo ($form_type == 'derma_email_advice') ? esc_html__('Location(s) on the body', 'doctor2go-connect') : esc_html__('Location(s) of your complaint', 'doctor2go-connect') ; ?> *
                            </label>
                            <input type="text" id="booking_locatie" name="location" class="form-control myrequired" placeholder="<?php echo esc_attr__('For example: left forearm', 'doctor2go-connect')?>">
                        </div>
                    </div>
                    <input type="hidden" id="booking_ai_info" name="booking_ai_info" value="">
                <?php } ?>
                    <!-- Beschrijving / eerste opgemerkt -->
                    <div class="mb-3">
                        <label for="beschrijf_de_klacht" class="form-label">
                            <?php echo ($form_type == 'derma_email_advice') ? esc_html__('Describe your skin problem (color, size, growing, bleeding, itching, occasional or continuous) ', 'doctor2go-connect') : esc_html__('Describe your complaint (how it looks like, what you feel, level of pain, occasional or continuous) ', 'doctor2go-connect')?> *
                        </label>
                        <textarea id="booking_beschrijf_de_klacht" name="complaint_description" class="form-control myrequired" rows="3" placeholder="<?php echo esc_attr__('For example: itchy red spots or bumps...', 'doctor2go-connect')?>"></textarea>
                    </div>
                <?php if($form_type == 'derma_email_advice'){?>
                    <div class="mb-3">
                        <label for="treatment_history" class="form-label">
                            <?php echo ($form_type == 'derma_email_advice') ? esc_html__('Treatment history (what medications did you use for your skin problem and what was the result)', 'ai-derma-plugin') : esc_html__('Treatment history (what medications did you use for your complaint and what was the result)', 'ai-derma-plugin')?>
                        </label>
                        <textarea id="booking_treatment_history" name="treatment_history" class="form-control" rows="3" placeholder="<?php echo esc_attr__('If applicable...', 'ai-derma-plugin')?>"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="history" class="form-label">
                            <?php echo ($form_type == 'derma_email_advice') ? esc_html__('Medical history (other relevant medical conditions and medications)', 'ai-derma-plugin') : esc_html__('Medical history (other relevant medical conditions and medications)', 'ai-derma-plugin')?>
                        </label>
                        <textarea id="booking_history" name="medical_history" class="form-control" rows="3" placeholder="<?php echo esc_attr__('If applicable...', 'ai-derma-plugin')?>"></textarea>
                    </div>
                <?php } ?>
            </fieldset>
            <input readonly type="hidden" id="bookingdermapic1" name="bookingdermapic1" value="">
            <input readonly type="hidden" id="bookingdermapic2" name="bookingdermapic2" value="">
            <input readonly type="hidden" id="bookingdermapic3" name="bookingdermapic3" value="">
        </div>
    </div>
</div>