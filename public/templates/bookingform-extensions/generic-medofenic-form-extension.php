<?php if ( ! defined( 'ABSPATH' ) ) exit; 
global $d2g_profile_data, $currUser, $userMeta, $form_type;
if ( is_user_logged_in() ) {
    $currUser = wp_get_current_user();
    $userMeta = get_user_meta( $currUser->data->ID );
}
?>
<!-- Complaint description -->
<legend class="fs-5 mb-3">
    <strong><?php echo esc_html__('About your complaint', 'doctor2go-connect')?></strong>
</legend>
<p class="opener"><strong><?php echo esc_html__('Click here to provide / update your complaint information', 'doctor2go-connect')?></strong><span class="icon-down-open"></span></p>
<div id="complaint_form_wrapper" class="card <?php echo ($_GET['use_ai_info'] === '1') ? 'simple_hide' : 'show'; ?>">
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
                <input class="form-control" type="file" name="image_1" id="image_1" accept="image/*">
            </div>
            <div class="col-md-4">
                <label for="image_2" class="form-label"><?php echo esc_html__( 'Upload image 2', 'doctor2go-connect' ); ?></label>
                <input class="form-control" type="file" name="image_2" id="image_2" accept="image/*">
            </div>
            <div class="col-md-4">
                <label for="image_3" class="form-label"><?php echo esc_html__( 'Upload image 3', 'doctor2go-connect' ); ?></label>
                <input class="form-control" type="file" name="image_3" id="image_3" accept="image/*">
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