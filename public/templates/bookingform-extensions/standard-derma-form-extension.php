<?php if ( ! defined( 'ABSPATH' ) ) exit; 
global $d2g_profile_data, $currUser, $userMeta, $form_type;
if ( is_user_logged_in() ) {
    $currUser = wp_get_current_user();
    $userMeta = get_user_meta( $currUser->data->ID );
}
?>
<!-- Over de huidaandoening -->

 <legend class="fs-5 mb-3">
    <strong><?php echo esc_html__('About your complaint (optional)', 'doctor2go-connect')?></strong>
</legend>
<p class="opener"><strong><?php echo esc_html__('Click here to provide / update your complaint information', 'doctor2go-connect')?></strong><span class="icon-down-open"></span></p> 
<div id="booking_complaint_form_wrapper" class=" <?php echo ($_GET['use_ai_info'] === '1') ? 'simple_hide' : 'show'; ?>">

        <fieldset class="mb-4">
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="image_1" class="form-label"><?php echo esc_html__( 'Upload image 1', 'doctor2go-connect' ); ?></label>
                    <input class="form-control" type="file" capture="user" name="image_1" id="booking_image_1" accept="image/*" placeholder="<?php echo esc_attr__( 'Choose image 1', 'doctor2go-connect' ); ?>">
                </div>
                <?php if ( wpmd_is_phone() ) { ?>
                    <p class="text-primary text-decoration-underline mb-3 opener">
                        <?php echo esc_html__('Upload / make more photos', 'ai-derma-plugin')?>
                    </p>
                    <div class="simple_hide">
                <?php } ?>
                <div class="col-md-4">
                    <label for="image_2" class="form-label"><?php echo esc_html__( 'Upload image 2', 'doctor2go-connect' ); ?></label>
                    <input class="form-control" type="file" capture="user" name="image_2" id="booking_image_2" accept="image/*" placeholder="<?php echo esc_attr__( 'Choose image 2', 'doctor2go-connect' ); ?>">
                </div>
                <div class="col-md-4">
                    <label for="image_3" class="form-label"><?php echo esc_html__( 'Upload image 3', 'doctor2go-connect' ); ?></label>
                    <input class="form-control" type="file" capture="user" name="image_3" id="booking_image_3" accept="image/*" placeholder="<?php echo esc_attr__( 'Choose image 3', 'doctor2go-connect' ); ?>">
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
                            <?php echo esc_html__('When did you first notice your skin problem?', 'doctor2go-connect')?> *
                        </label>
                        <input type="text" id="booking_opgemerkt" name="first_noticed" class="form-control required_wc" rows="2" placeholder="<?php echo esc_attr__('since...days,weeks,months, years', 'doctor2go-connect')?>">
                    </div>
                    <div class="col-md-6">
                        <label for="locatie" class="form-label">
                            <?php echo esc_html__('Location(s) on the body', 'doctor2go-connect')?> *
                        </label>
                        <input type="text" id="booking_locatie" name="location" class="form-control required_wc" placeholder="<?php echo esc_attr__('For example: left forearm', 'doctor2go-connect')?>">
                    </div>
                </div>
            <?php } ?>
                <!-- Beschrijving / eerste opgemerkt -->
                <div class="mb-3">
                    <label for="beschrijf_de_klacht" class="form-label">
                        <?php echo esc_html__('Describe your skin problem (color, size, growing, bleeding, itching, occasional or continuous) ', 'doctor2go-connect')?> *
                    </label>
                    <textarea id="booking_beschrijf_de_klacht" name="complaint_description" class="form-control required_wc" rows="3" placeholder="<?php echo esc_attr__('For example: itchy red spots or bumps...', 'doctor2go-connect')?>"></textarea>
                </div>
            <?php if($form_type == 'derma_email_advice'){?>
                <div class="mb-3">
                    <label for="treatment_history" class="form-label">
                        <?php echo esc_html__('Treatment history (what medications did you use for your skin problem and what was the result)', 'ai-derma-plugin')?>
                    </label>
                    <textarea id="booking_treatment_history" name="treatment_history" class="form-control" rows="3" placeholder="<?php echo esc_attr__('If applicable...', 'ai-derma-plugin')?>"></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="history" class="form-label">
                        <?php echo esc_html__('Medical history (other relevant medical conditions and medications)', 'ai-derma-plugin')?>
                    </label>
                    <textarea id="booking_history" name="medical_history" class="form-control" rows="3" placeholder="<?php echo esc_attr__('If applicable...', 'ai-derma-plugin')?>"></textarea>
                </div>

                

                <!-- deprecated fields, kept for backward compatibility -->
                <div class="simple_hide">
                    <!-- Locatie / veranderd -->
                    <div class="row g-3 mb-3">
                        
                        <div class="col-md-6">
                            <label for="veranderd" class="form-label">
                                <?php echo esc_html__('Has the spot changed? Choose one or more options.', 'doctor2go-connect')?>
                            </label>
                            <select id="booking_veranderd" name="has_changed[]" class="form-select" multiple placeholder="<?php echo esc_attr__('Select one or more options', 'doctor2go-connect'); ?>">
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
                                <input class="form-check-input" type="checkbox" id="booking_jeuk" name="itch_check" value="<?php echo esc_html__('Yes', 'doctor2go-connect'); ?>" role="switch">
                                <label class="form-check-label" for="jeuk">
                                    <?php echo esc_html__('Does the skin condition itch?', 'doctor2go-connect')?>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="booking_bloed" name="blood_check" value="<?php echo esc_html__('Yes', 'doctor2go-connect'); ?>" role="switch">
                                <label class="form-check-label" for="bloed">
                                    <?php echo esc_html__('Does the skin condition bleed?', 'doctor2go-connect')?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            
        </fieldset>

</div>