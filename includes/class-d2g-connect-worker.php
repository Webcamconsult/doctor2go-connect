<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class D2gConnect_Worker {
    /* Sync a single doctor profile with the latest availability data from the API 
        * This function can be called from a WP Cron job or manually to update the doctor's profile data
        @param int $doctor_id The ID of the doctor post to sync
    */

    public function sync_single_doctor($doctor_id) {
        $docKey = get_post_meta($doctor_id, 'user_key', true);

        if (!$docKey) {
            return;
        }

        $profile = new \D2G_ProfileData(get_post($doctor_id));
        $availabilityDataJson = $profile->d2gc_get_availability_data($docKey);

        if (!$availabilityDataJson) {
            return;
        }

        $availabilityDataObj = json_decode($availabilityDataJson);
        $timecode = time();

        $user_has_inloop = !empty($availabilityDataObj->user_has_inloop);
        $user_is_active  = !empty($availabilityDataObj->user_is_active);
        $walk_in_check   = ($user_has_inloop && $user_is_active) ? 1 : 0;

        update_post_meta($doctor_id, 'd2g_walk_in', $walk_in_check);
        update_post_meta($doctor_id, 'd2g_last_synced', date('Y-m-d H:i:s'));
        update_post_meta($doctor_id, 'd2g_timecode', $timecode);

        if (empty($availabilityDataObj->availabilities)) {
            update_post_meta($doctor_id, 'd2g_availability_check', 0);
            update_post_meta($doctor_id, 'd2g_first_availability', 0);
            update_post_meta($doctor_id, 'd2g_tariffs', 0);
            return;
        }

        update_post_meta($doctor_id, 'd2g_availability_check', 1);

        $first = $profile->d2gc_get_first_avialibility($availabilityDataObj->availabilities, 'date');
        update_post_meta($doctor_id, 'd2g_first_availability', $first ? wp_strip_all_tags($first) : 0);

        $tariffs   = $profile->d2gc_get_tariffs($availabilityDataObj->availabilities);
        $tariffStr = d2gc_get_tariff_string($tariffs);

        update_post_meta($doctor_id, 'd2g_tariffs', $tariffs ? $tariffStr : 0);
    }
}