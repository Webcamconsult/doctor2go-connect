<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class D2gConnect_Cron {

    /** The recurring job that queues the availability sync of all doctors. */
    const HOOK = 'd2g_sync_doctors';

    /** Time (unix) the job last queued the doctors. */
    const LAST_RUN_OPTION = 'd2gc_sync_last_queued';

    public function queue_doctors() {

        update_option( self::LAST_RUN_OPTION, time(), false );

        $doctors = get_posts([
            'post_type'      => 'd2g_doctor',
            'posts_per_page' => -1,
            'fields'         => 'ids'
        ]);

        if (!$doctors) {
            return;
        }

        foreach ($doctors as $doctor_id) {
            wp_schedule_single_event( time(), 'd2g_sync_single_doctor', [$doctor_id] );
        }
    }

    public function add_cron_schedules($schedules) {
        $schedules['fifteen_minutes'] = [
            'interval' => 900,
            'display'  => 'Every 15 minutes'
        ];
        return $schedules;
    }

    /**
     * (init) Schedules the recurring job again when it is missing. It used to be scheduled only on plugin activation,
     * so once lost (cron error, database import, ...) the availability sync stopped without any notice.
     */
    public function ensure_scheduled() {
        if ( ! wp_next_scheduled( self::HOOK ) ) {
            wp_schedule_event( time(), 'fifteen_minutes', self::HOOK );
        }
    }

    /**
     * Status for the settings page (Doctors tab).
     *
     * @return array next_run, last_run (unix or 0), newest / oldest d2g_last_synced (site time, '' if none), doctors
     */
    public static function get_status() {
        global $wpdb;

        // d2g_last_synced is stored as site time (Y-m-d H:i:s); 0 = never
        $synced = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- status read on the settings page only
            "SELECT MAX(pm.meta_value) AS newest, MIN(pm.meta_value) AS oldest
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             INNER JOIN {$wpdb->postmeta} uk ON uk.post_id = pm.post_id AND uk.meta_key = 'user_key' AND uk.meta_value <> ''
             WHERE pm.meta_key = 'd2g_last_synced' AND pm.meta_value LIKE '2%'
               AND p.post_type = 'd2g_doctor' AND p.post_status = 'publish'"
        );

        return array(
            'next_run' => (int) wp_next_scheduled( self::HOOK ),
            'last_run' => (int) get_option( self::LAST_RUN_OPTION, 0 ),
            'newest'   => $synced && $synced->newest ? $synced->newest : '',
            'oldest'   => $synced && $synced->oldest ? $synced->oldest : '',
            'doctors'  => count( self::get_doctors_to_sync() ),
        );
    }

    /**
     * One doctor per translation group (the worker updates all translations), only doctors linked to WCC.
     *
     * @return int[]
     */
    public static function get_doctors_to_sync() {
        $ids = get_posts(
            array(
                'post_type'      => 'd2g_doctor',
                'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'lang'           => '', // all languages (Polylang)
                'meta_query'     => array(
                    array(
                        'key'     => 'user_key',
                        'value'   => '',
                        'compare' => '!=',
                    ),
                ),
            )
        );

        $covered = array();
        $doctors = array();
        foreach ( $ids as $id ) {
            if ( isset( $covered[ $id ] ) ) {
                continue;
            }
            $doctors[] = (int) $id;
            foreach ( D2GC_Translations::get_post_translation_ids( $id ) as $translation_id ) {
                $covered[ $translation_id ] = true;
            }
        }
        return $doctors;
    }

    /**
     * AJAX (settings page): admins only, valid nonce.
     */
    private static function check_admin_request() {
        check_ajax_referer( 'd2gc_availability_sync', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'doctor2go-connect' ) ), 403 );
        }
    }

    /**
     * AJAX (settings page): the doctors the manual sync goes through.
     */
    public function ajax_list() {
        self::check_admin_request();
        wp_send_json_success( array( 'doctors' => self::get_doctors_to_sync() ) );
    }

    /**
     * AJAX (settings page): sync a batch of max. 3 doctors right away (not via WP-Cron), same as the worker does.
     */
    public function ajax_run() {
        self::check_admin_request();

        $ids    = isset( $_POST['doctors'] ) ? array_slice( array_map( 'absint', (array) wp_unslash( $_POST['doctors'] ) ), 0, 3 ) : array();
        $worker = new D2gConnect_Worker();
        $failed = array();

        foreach ( $ids as $id ) {
            if ( 'd2g_doctor' !== get_post_type( $id ) ) {
                continue;
            }
            $start = time();
            $worker->sync_single_doctor( $id );
            wp_cache_delete( $id, 'post_meta' );
            // the worker sets d2g_timecode = time() once the doctor was synced
            if ( (int) get_post_meta( $id, 'd2g_timecode', true ) < $start ) {
                $failed[] = get_the_title( $id ) . ' (#' . $id . ')';
            }
        }

        wp_send_json_success(
            array(
                'done'   => count( $ids ),
                'failed' => $failed,
                'status' => self::get_status(),
            )
        );
    }
}
