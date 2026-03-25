<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class D2gConnect_Worker {

    public function sync_single_doctor($doctor_id) {

        $docKey = get_post_meta($doctor_id, 'user_key', true);

        if (!$docKey   ) {
            return;
        }

        $profile = new \D2G_ProfileData(get_post($doctor_id));

        $availabilityDataJson = $profile->d2g_get_availability_data($docKey);

        if (!$availabilityDataJson) {
            return;
        }

        $availabilityDataObj = json_decode($availabilityDataJson);
        if (empty($availabilityDataObj->availabilities)) {
            return;
        } 

        $timecode = time();
        update_post_meta( $doctor_id, 'd2g_availability_check', 1 );
		update_post_meta( $doctor_id, 'd2g_last_synced', date('Y-m-d H:i:s') );
        update_post_meta( $doctor_id, 'd2g_timecode', $timecode );

        $first = $profile->get_first_avialibility($availabilityDataObj->availabilities);
        if ($first) {
            update_post_meta($doctor_id, 'd2g_first_availability', wp_strip_all_tags($first));
        }

        $tariffs = $profile->get_tariffs($availabilityDataObj->availabilities);
        if ($tariffs) {
            update_post_meta($doctor_id, 'd2g_tariffs' ,$tariffs);
        }

        $walk_in_check = ( $availabilityDataObj->user_has_inloop == true && $availabilityDataObj->user_is_active ) ? true : false;
        update_post_meta( $doctor_id, 'd2g_walk_in', $walk_in_check );
    }
}