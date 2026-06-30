<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class D2gConnect_Worker {

    /**
     * Sync a single doctor profile with latest availability data
     * and apply the same operational meta to all linked translations.
     *
     * @param int $doctor_id
     * @return void
     */
    public function sync_single_doctor( $doctor_id ) {
        $doctor_id = absint( $doctor_id );

        if ( ! $doctor_id || 'd2g_doctor' !== get_post_type( $doctor_id ) ) {
            return;
        }

        $docKey = get_post_meta( $doctor_id, 'user_key', true );

        if ( ! $docKey ) {
            return;
        }

        $profile = new \D2G_ProfileData( get_post( $doctor_id ) );
        $availabilityDataJson = $profile->d2gc_get_availability_data( $docKey );

        if ( ! $availabilityDataJson ) {
            return;
        }

        $availabilityDataObj = json_decode( $availabilityDataJson );

        if ( empty( $availabilityDataObj ) || ! is_object( $availabilityDataObj ) ) {
            return;
        }

        $timecode = time();

        $user_has_inloop = ! empty( $availabilityDataObj->user_has_inloop );
        $user_is_active  = ! empty( $availabilityDataObj->user_is_active );
        $walk_in_check   = ( $user_has_inloop && $user_is_active ) ? 1 : 0;

        $meta_updates = array(
            'd2g_walk_in'        => $walk_in_check,
            'd2g_last_synced'    => current_time( 'mysql' ),
            'd2g_timecode'       => $timecode,
        );

        if ( empty( $availabilityDataObj->availabilities ) ) {
            $meta_updates['d2g_availability_check'] = 0;
            $meta_updates['d2g_first_availability'] = 0;
            $meta_updates['d2g_tariffs']            = 0;
        } else {
            $meta_updates['d2g_availability_check'] = 1;

            $first = $profile->d2gc_get_first_avialibility( $availabilityDataObj->availabilities, 'date' );
            $meta_updates['d2g_first_availability'] = $first ? wp_strip_all_tags( $first ) : 0;

            $tariffs   = $profile->d2gc_get_tariffs( $availabilityDataObj->availabilities );
            $tariffStr = d2gc_get_tariff_string( $tariffs );

            $meta_updates['d2g_tariffs'] = $tariffs ? $tariffStr : 0;
        }

        $doctor_ids = $this->get_doctor_translation_ids( $doctor_id );

        foreach ( $doctor_ids as $linked_doctor_id ) {
            foreach ( $meta_updates as $meta_key => $meta_value ) {
                update_post_meta( $linked_doctor_id, $meta_key, $meta_value );
            }
        }
    }

    /**
     * Get source doctor + all linked translation IDs.
     *
     * @param int $doctor_id
     * @return int[]
     */
    private function get_doctor_translation_ids( $doctor_id ) {
        $ids = array( absint( $doctor_id ) );

        if ( function_exists( 'pll_languages_list' ) && function_exists( 'pll_get_post' ) ) {
            $languages = pll_languages_list();

            if ( is_array( $languages ) ) {
                foreach ( $languages as $lang ) {
                    $translated_id = pll_get_post( $doctor_id, $lang );

                    if ( ! empty( $translated_id ) ) {
                        $ids[] = absint( $translated_id );
                    }
                }
            }
        }

        $ids = array_filter( array_unique( $ids ) );

        return $ids;
    }
}