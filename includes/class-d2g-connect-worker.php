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

        $availabilityDataJson = $profile->d2gc_get_availability_data($docKey);

        if (!$availabilityDataJson) {
            return;
        }

        $availabilityDataObj = json_decode($availabilityDataJson);
        $timecode = time();

        if (empty($availabilityDataObj->availabilities)) {
            update_post_meta( $doctor_id, 'd2g_availability_check', 0 );
            update_post_meta( $doctor_id, 'd2g_first_availability', 0 );
            update_post_meta( $doctor_id, 'd2g_walk_in', 0 ); 
            update_post_meta( $doctor_id, 'd2g_tariffs', 0 );
            update_post_meta( $doctor_id, 'd2g_last_synced', date('Y-m-d H:i:s') );
            update_post_meta( $doctor_id, 'd2g_timecode', $timecode );
            return;
        } 

        update_post_meta( $doctor_id, 'd2g_availability_check', 1 );
		update_post_meta( $doctor_id, 'd2g_last_synced', date('Y-m-d H:i:s') );
        update_post_meta( $doctor_id, 'd2g_timecode', $timecode );

        $first = $profile->d2gc_get_first_avialibility($availabilityDataObj->availabilities, 'date');
        if ($first) {
            update_post_meta($doctor_id, 'd2g_first_availability', wp_strip_all_tags($first));
        }

        $tariffs            = $profile->d2gc_get_tariffs($availabilityDataObj->availabilities);
        $tariffStr          = d2gc_get_tariff_string( $tariffs );

        if ($tariffs) {
            update_post_meta($doctor_id, 'd2g_tariffs' ,$tariffStr);
        }

        $walk_in_check = ( $availabilityDataObj->user_has_inloop == true && $availabilityDataObj->user_is_active ) ? 1 : 0;
        update_post_meta( $doctor_id, 'd2g_walk_in', $walk_in_check );
    }
}