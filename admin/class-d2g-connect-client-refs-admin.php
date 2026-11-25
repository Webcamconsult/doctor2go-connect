<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin pages Doctor2Go Connect → Client references and → Event Log. Administrators only.
 *
 * - Client references: the open rows of D2GC_Client_Refs (guest bookings waiting to be linked, active confirmation
 *   links), find them by e-mail address, delete one or all of an e-mail address. Tokens are never shown.
 * - Event Log: what happened, without personal data, with totals (e.g. how many guests later created an
 *   account). Kept one year.
 */
class D2GC_Client_Refs_Admin {

	const PAGE     = 'd2gc-client-refs';
	const LOG_PAGE = 'd2gc-client-ref-log';
	const ACTION   = 'd2gc_client_refs_action';
	const PER_PAGE = 50;

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 21 );
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handle' ) );
		// on the user: which guest bookings were linked to the account
		add_action( 'show_user_profile', array( __CLASS__, 'render_profile' ) );
		add_action( 'edit_user_profile', array( __CLASS__, 'render_profile' ) );
		add_filter( 'manage_users_columns', array( __CLASS__, 'users_column' ) );
		add_filter( 'manage_users_custom_column', array( __CLASS__, 'users_column_value' ), 10, 3 );
	}

	public static function menu() {
		add_submenu_page( 'd2g-connect-admin-display', __( 'Client references', 'doctor2go-connect' ), __( 'Client references', 'doctor2go-connect' ), 'manage_options', self::PAGE, array( __CLASS__, 'render' ) );
		add_submenu_page( 'd2g-connect-admin-display', __( 'Event Log', 'doctor2go-connect' ), __( 'Event Log', 'doctor2go-connect' ), 'manage_options', self::LOG_PAGE, array( __CLASS__, 'render_log' ) );
	}

	private static function url( $page, $args = array() ) {
		return add_query_arg( array_merge( array( 'page' => $page ), $args ), admin_url( 'admin.php' ) );
	}

	private static function action_url( $do, $args ) {
		return wp_nonce_url( add_query_arg( array_merge( array( 'action' => self::ACTION, 'do' => $do ), $args ), admin_url( 'admin-post.php' ) ), self::ACTION );
	}

	private static function type_labels() {
		return array(
			'video'        => __( 'Video consult', 'doctor2go-connect' ),
			'email_advice' => __( 'Email advice', 'doctor2go-connect' ),
			'walk_in'      => __( 'Walk-in', 'doctor2go-connect' ),
		);
	}

	private static function booked_by_labels() {
		return array(
			D2GC_Client_Refs::BOOKED_BY_GUEST => __( 'Guest', 'doctor2go-connect' ),
			D2GC_Client_Refs::BOOKED_BY_OWN   => __( 'Logged in', 'doctor2go-connect' ),
			D2GC_Client_Refs::BOOKED_BY_OTHER => __( 'Logged in, other e-mail', 'doctor2go-connect' ),
		);
	}

	private static function event_labels() {
		return array(
			D2GC_Client_Refs::EVENT_BOOKED          => __( 'Booked', 'doctor2go-connect' ),
			D2GC_Client_Refs::EVENT_LINKED          => __( 'Linked to account', 'doctor2go-connect' ),
			D2GC_Client_Refs::EVENT_EXPIRED         => __( 'Expired without account', 'doctor2go-connect' ),
			D2GC_Client_Refs::EVENT_DELETED_ACCOUNT => __( 'Deleted with account', 'doctor2go-connect' ),
			D2GC_Client_Refs::EVENT_DELETED_ADMIN   => __( 'Deleted by admin', 'doctor2go-connect' ),
			D2GC_Client_Refs::EVENT_ERASED          => __( 'Deleted on request', 'doctor2go-connect' ),
		);
	}

	private static function doctor_name( $post_id ) {
		$post_id = (int) $post_id;
		return $post_id ? get_the_title( $post_id ) : '–';
	}

	/** Date and time in the site's time zone, from a UTC datetime of the table. */
	private static function local_time( $utc ) {
		return $utc ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $utc . ' UTC' ) ) : '–';
	}

	/**
	 * (admin-post) delete, delete_email.
	 */
	public static function handle() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'doctor2go-connect' ), 403 );
		}
		check_admin_referer( self::ACTION );
		$do    = isset( $_GET['do'] ) ? sanitize_key( wp_unslash( $_GET['do'] ) ) : '';
		$id    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$email = isset( $_GET['email'] ) ? sanitize_email( wp_unslash( $_GET['email'] ) ) : '';
		$done  = 0;

		if ( 'delete' === $do && $id ) {
			$done = D2GC_Client_Refs::delete_row( $id );
		} elseif ( 'delete_email' === $do && is_email( $email ) ) {
			$done  = D2GC_Client_Refs::delete_email( $email );
			$email = '';
		}

		wp_safe_redirect( self::url( self::PAGE, array( 'done' => $do, 'count' => $done, 's' => $email ) ) );
		exit;
	}

	/* ---------------------------------------------------------------------------------------------------------
	 * Client references
	 * ------------------------------------------------------------------------------------------------------ */

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		D2GC_Client_Refs::maybe_install();
		global $wpdb;
		$table  = D2GC_Client_Refs::table();
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- search filter only
		$paged  = max( 1, isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$where  = '' !== $search ? $wpdb->prepare( 'WHERE email LIKE %s', '%' . $wpdb->esc_like( strtolower( $search ) ) . '%' ) : '';
		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- table name and the prepared $where
		$total   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table $where" );
		$rows    = $wpdb->get_results( $wpdb->prepare( "SELECT id, email, type, booked_by, doctor_post_id, booking_ref, user_id, ref_expires, expires_at, created_at FROM $table $where ORDER BY id DESC LIMIT %d OFFSET %d", self::PER_PAGE, ( $paged - 1 ) * self::PER_PAGE ) );
		$waiting = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE user_id = 0" );
		$active  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE ref_expires > %s", gmdate( 'Y-m-d H:i:s' ) ) );
		// phpcs:enable
		$types     = self::type_labels();
		$booked_by = self::booked_by_labels();
		$done      = isset( $_GET['done'] ) ? sanitize_key( wp_unslash( $_GET['done'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$count     = isset( $_GET['count'] ) ? absint( $_GET['count'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$now       = time();
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Client references', 'doctor2go-connect' ); ?></h1>
			<p>
				<?php
				/* translators: %d: number of days */
				printf( esc_html__( 'Bookings whose doctor2go.online client is kept here: guest bookings waiting until the owner of the e-mail address has a confirmed account (deleted automatically after %d days), and confirmation links that are still active. The access tokens are stored encrypted and are never shown.', 'doctor2go-connect' ), (int) D2GC_Client_Refs::retention_days() );
				?>
				<br><?php echo esc_html__( 'To delete everything about a person (deletion request), use Tools → Erase Personal Data.', 'doctor2go-connect' ); ?>
			</p>

			<?php if ( 'delete' === $done || 'delete_email' === $done ) { ?>
				<div class="notice notice-success is-dismissible"><p>
					<?php
					/* translators: %d: number of entries */
					printf( esc_html( _n( '%d entry deleted.', '%d entries deleted.', $count, 'doctor2go-connect' ) ), (int) $count );
					?>
				</p></div>
			<?php } ?>

			<ul class="subsubsub">
				<li><?php echo esc_html__( 'Waiting for linking', 'doctor2go-connect' ); ?> <span class="count">(<?php echo (int) $waiting; ?>)</span> |</li>
				<li><?php echo esc_html__( 'Appointment link active', 'doctor2go-connect' ); ?> <span class="count">(<?php echo (int) $active; ?>)</span> |</li>
				<li><a href="<?php echo esc_url( self::url( self::LOG_PAGE ) ); ?>"><?php echo esc_html__( 'Event Log', 'doctor2go-connect' ); ?></a></li>
			</ul>

			<form method="get" class="search-form" style="float:right;margin:8px 0">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE ); ?>">
				<label class="screen-reader-text" for="d2gc-client-ref-search"><?php echo esc_html__( 'Search by e-mail address', 'doctor2go-connect' ); ?></label>
				<input type="search" id="d2gc-client-ref-search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php echo esc_attr__( 'E-mail address', 'doctor2go-connect' ); ?>">
				<button class="button"><?php echo esc_html__( 'Search', 'doctor2go-connect' ); ?></button>
			</form>

			<?php if ( '' !== $search && is_email( $search ) && $total ) { ?>
				<p style="clear:both">
					<a class="button button-link-delete" href="<?php echo esc_url( self::action_url( 'delete_email', array( 'email' => $search ) ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete all entries of this e-mail address?', 'doctor2go-connect' ) ); ?>')">
						<?php
						/* translators: %d: number of entries */
						printf( esc_html( _n( 'Delete the %d entry of this e-mail address', 'Delete all %d entries of this e-mail address', $total, 'doctor2go-connect' ) ), (int) $total );
						?>
					</a>
				</p>
			<?php } ?>

			<table class="widefat striped" style="clear:both">
				<thead><tr>
					<th><?php echo esc_html__( 'Booked on', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'Booking', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'E-mail', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'Type', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'Booked by', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'Doctor', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'Status', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'Deleted on', 'doctor2go-connect' ); ?></th>
					<th></th>
				</tr></thead>
				<tbody>
				<?php if ( ! $rows ) { ?>
					<tr><td colspan="9"><?php echo esc_html__( 'No entries found.', 'doctor2go-connect' ); ?></td></tr>
				<?php } ?>
				<?php foreach ( $rows as $row ) { ?>
					<?php $ref_active = $row->ref_expires && strtotime( $row->ref_expires . ' UTC' ) > $now; ?>
					<tr>
						<td><?php echo esc_html( self::local_time( $row->created_at ) ); ?></td>
						<td><?php echo wp_kses_post( self::booking_link( $row->booking_ref ) ); ?></td>
						<td><a href="<?php echo esc_url( self::url( self::PAGE, array( 's' => $row->email ) ) ); ?>"><?php echo esc_html( $row->email ); ?></a></td>
						<td><?php echo esc_html( $types[ $row->type ] ?? $row->type ); ?></td>
						<td><?php echo esc_html( $booked_by[ $row->booked_by ] ?? $row->booked_by ); ?></td>
						<td><?php echo esc_html( self::doctor_name( $row->doctor_post_id ) ); ?></td>
						<td>
							<?php if ( (int) $row->user_id ) { ?>
								<?php echo esc_html__( 'Already in the account', 'doctor2go-connect' ); ?>
								<br><small><?php echo esc_html( sprintf( /* translators: %s: date and time */ __( 'kept only for the appointment link in the confirmation box, deleted on %s', 'doctor2go-connect' ), self::local_time( $row->expires_at ) ) ); ?></small>
							<?php } else { ?>
								<?php echo esc_html__( 'Waiting for linking', 'doctor2go-connect' ); ?>
								<?php if ( $ref_active ) { ?>
									<br><small><?php echo esc_html( sprintf( /* translators: %s: date and time */ __( 'appointment link in the confirmation box active until %s', 'doctor2go-connect' ), self::local_time( $row->ref_expires ) ) ); ?></small>
								<?php } ?>
							<?php } ?>
						</td>
						<td><?php echo esc_html( self::local_time( $row->expires_at ) ); ?></td>
						<td style="white-space:nowrap">
							<a class="submitdelete" href="<?php echo esc_url( self::action_url( 'delete', array( 'id' => (int) $row->id ) ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this entry? A guest can then no longer link this booking to an account.', 'doctor2go-connect' ) ); ?>')"><?php echo esc_html__( 'Delete', 'doctor2go-connect' ); ?></a>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>

			<?php self::pagination( $total, $paged, self::url( self::PAGE, array( 's' => $search ) ) ); ?>
		</div>
		<?php
	}

	/* ---------------------------------------------------------------------------------------------------------
	 * Log
	 * ------------------------------------------------------------------------------------------------------ */

	public static function render_log() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		D2GC_Client_Refs::maybe_install();
		global $wpdb;
		$table     = D2GC_Client_Refs::log_table();
		$events    = self::event_labels();
		$types     = self::type_labels();
		$booked_by = self::booked_by_labels();
		// a linked event has what made it link as its type (no booking type: that would read as if it were booked)
		$linked_by = array(
			D2GC_Client_Refs::LINKED_BY_REGISTRATION => __( 'Confirmed registration', 'doctor2go-connect' ),
			D2GC_Client_Refs::LINKED_BY_LOGIN        => __( 'Login', 'doctor2go-connect' ),
		);

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- filters only
		$f_event = isset( $_GET['event'] ) ? sanitize_key( wp_unslash( $_GET['event'] ) ) : '';
		$f_type  = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : '';
		$f_month = isset( $_GET['month'] ) && preg_match( '/^\d{4}-\d{2}$/', wp_unslash( $_GET['month'] ) ) ? wp_unslash( $_GET['month'] ) : '';
		$paged   = max( 1, isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1 );
		$f_book  = isset( $_GET['booking'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['booking'] ) ) ) : '';
		// phpcs:enable
		$f_event = isset( $events[ $f_event ] ) ? $f_event : '';
		$f_type  = isset( $types[ $f_type ] ) ? $f_type : '';
		$f_book  = preg_match( '/^B-[A-Z0-9]{6}$/', $f_book ) ? $f_book : '';

		$conditions = array();
		if ( $f_book ) {
			$conditions[] = $wpdb->prepare( 'booking_ref = %s', $f_book );
		}
		if ( $f_event ) {
			$conditions[] = $wpdb->prepare( 'event = %s', $f_event );
		}
		if ( $f_type ) {
			$conditions[] = $wpdb->prepare( 'type = %s', $f_type );
		}
		if ( $f_month ) {
			$conditions[] = $wpdb->prepare( 'created_on BETWEEN %s AND %s', $f_month . '-01', gmdate( 'Y-m-t', strtotime( $f_month . '-01' ) ) );
		}
		$where = $conditions ? 'WHERE ' . implode( ' AND ', $conditions ) : '';

		$month_start = gmdate( 'Y-m-01' );
		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- table name and prepared conditions
		$total      = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table $where" );
		$rows       = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table $where ORDER BY id DESC LIMIT %d OFFSET %d", self::PER_PAGE, ( $paged - 1 ) * self::PER_PAGE ) );
		$year       = $wpdb->get_results( "SELECT event, COUNT(*) AS n FROM $table GROUP BY event", OBJECT_K );
		$month      = $wpdb->get_results( $wpdb->prepare( "SELECT event, COUNT(*) AS n FROM $table WHERE created_on >= %s GROUP BY event", $month_start ), OBJECT_K );
		$guest_year = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE event = %s AND booked_by <> %s", D2GC_Client_Refs::EVENT_BOOKED, D2GC_Client_Refs::BOOKED_BY_OWN ) );
		$avg_days   = $wpdb->get_var( $wpdb->prepare( "SELECT AVG(days_to_link) FROM $table WHERE event = %s", D2GC_Client_Refs::EVENT_LINKED ) );
		$months     = $wpdb->get_col( "SELECT DISTINCT DATE_FORMAT(created_on, '%Y-%m') AS m FROM $table ORDER BY m DESC" );
		$open_guest = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . D2GC_Client_Refs::table() . ' WHERE user_id = 0' );
		// phpcs:enable
		$linked_year = (int) ( $year[ D2GC_Client_Refs::EVENT_LINKED ]->n ?? 0 );
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Event Log', 'doctor2go-connect' ); ?></h1>
			<p>
				<?php
				/* translators: %d: number of days */
				printf( esc_html__( 'What happened with the bookings, without personal data (no e-mail address, name or account). Entries are deleted automatically after %d days.', 'doctor2go-connect' ), (int) D2GC_Client_Refs::LOG_DAYS );
				?>
			</p>

			<table class="widefat" style="max-width:720px;margin:16px 0">
				<thead><tr>
					<th></th>
					<th><?php echo esc_html__( 'This month', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'Last 12 months', 'doctor2go-connect' ); ?></th>
				</tr></thead>
				<tbody>
				<?php foreach ( $events as $event => $label ) { ?>
					<tr>
						<td><?php echo esc_html( $label ); ?></td>
						<td><?php echo (int) ( $month[ $event ]->n ?? 0 ); ?></td>
						<td><?php echo (int) ( $year[ $event ]->n ?? 0 ); ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>

			<p>
				<strong><?php echo esc_html__( 'Guests who later created an account:', 'doctor2go-connect' ); ?></strong>
				<?php
				/* translators: 1: linked bookings, 2: guest bookings, 3: percentage */
				printf( esc_html__( '%1$d of %2$d guest bookings in the last 12 months (%3$s%%)', 'doctor2go-connect' ), (int) $linked_year, (int) $guest_year, esc_html( $guest_year ? number_format_i18n( 100 * $linked_year / $guest_year, 1 ) : '0' ) );
				?>
				<?php if ( null !== $avg_days ) { ?>
					<br><?php echo esc_html( sprintf( /* translators: %s: number of days */ __( 'On average linked %s days after booking.', 'doctor2go-connect' ), number_format_i18n( (float) $avg_days, 1 ) ) ); ?>
				<?php } ?>
				<br><?php echo esc_html( sprintf( /* translators: %d: number of bookings */ __( 'Guest bookings waiting for linking now: %d', 'doctor2go-connect' ), $open_guest ) ); ?>
			</p>

			<form method="get" style="margin:16px 0">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::LOG_PAGE ); ?>">
				<select name="event">
					<option value=""><?php echo esc_html__( 'All events', 'doctor2go-connect' ); ?></option>
					<?php foreach ( $events as $key => $label ) { ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $f_event, $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php } ?>
				</select>
				<select name="type">
					<option value=""><?php echo esc_html__( 'All types', 'doctor2go-connect' ); ?></option>
					<?php foreach ( $types as $key => $label ) { ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $f_type, $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php } ?>
				</select>
				<select name="month">
					<option value=""><?php echo esc_html__( 'All months', 'doctor2go-connect' ); ?></option>
					<?php foreach ( $months as $m ) { ?>
						<option value="<?php echo esc_attr( $m ); ?>" <?php selected( $f_month, $m ); ?>><?php echo esc_html( wp_date( 'F Y', strtotime( $m . '-01' ) ) ); ?></option>
					<?php } ?>
				</select>
				<input type="search" name="booking" value="<?php echo esc_attr( $f_book ); ?>" placeholder="<?php echo esc_attr__( 'Booking, e.g. B-7F3K9Q', 'doctor2go-connect' ); ?>">
				<button class="button"><?php echo esc_html__( 'Filter', 'doctor2go-connect' ); ?></button>
				<?php if ( $f_event || $f_type || $f_month || $f_book ) { ?>
					<a href="<?php echo esc_url( self::url( self::LOG_PAGE ) ); ?>"><?php echo esc_html__( 'Show all', 'doctor2go-connect' ); ?></a>
				<?php } ?>
			</form>

			<table class="widefat striped">
				<thead><tr>
					<th><?php echo esc_html__( 'Date', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'Booking', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'Event', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'Type', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'User type', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'Doctor', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'Days until linked', 'doctor2go-connect' ); ?></th>
				</tr></thead>
				<tbody>
				<?php if ( ! $rows ) { ?>
					<tr><td colspan="7"><?php echo esc_html__( 'No entries found.', 'doctor2go-connect' ); ?></td></tr>
				<?php } ?>
				<?php foreach ( $rows as $row ) { ?>
					<tr>
						<td><?php echo esc_html( wp_date( get_option( 'date_format' ), strtotime( $row->created_on . ' 12:00 UTC' ) ) ); ?></td>
						<td><?php echo wp_kses_post( self::booking_link( $row->booking_ref ) ); ?></td>
						<td><?php echo esc_html( $events[ $row->event ] ?? $row->event ); ?></td>
						<td><?php echo esc_html( D2GC_Client_Refs::EVENT_LINKED === $row->event ? ( $linked_by[ $row->type ] ?? '' ) : ( $types[ $row->type ] ?? $row->type ) ); ?></td>
						<td><?php echo esc_html( $booked_by[ $row->booked_by ] ?? $row->booked_by ); ?></td>
						<td><?php echo esc_html( self::doctor_name( $row->doctor_post_id ) ); ?></td>
						<td><?php echo null === $row->days_to_link ? '–' : (int) $row->days_to_link; ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>

			<?php self::pagination( $total, $paged, self::url( self::LOG_PAGE, array( 'event' => $f_event, 'type' => $f_type, 'month' => $f_month, 'booking' => $f_book ) ) ); ?>
		</div>
		<?php
	}

	/* ---------------------------------------------------------------------------------------------------------
	 * User profile and user list
	 * ------------------------------------------------------------------------------------------------------ */

	private static function guest_history( $user_id ) {
		$history = get_user_meta( (int) $user_id, D2GC_Client_Refs::META_GUEST_HISTORY, true );
		return is_array( $history ) ? $history : array();
	}

	private static function booking_link( $booking_ref ) {
		if ( '' === (string) $booking_ref ) {
			return '–';
		}
		return '<a href="' . esc_url( self::url( self::LOG_PAGE, array( 'booking' => $booking_ref ) ) ) . '">' . esc_html( $booking_ref ) . '</a>';
	}

	/** (show_user_profile, edit_user_profile) E-mail confirmation and the guest bookings before the account. */
	public static function render_profile( $user ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! D2GC_Email_Verification::is_verified( $user->ID ) ) {
			?>
			<h2><?php echo esc_html__( 'E-mail confirmation', 'doctor2go-connect' ); ?></h2>
			<p><?php echo esc_html__( 'Not confirmed yet: this person has not clicked the link in the registration mail. Until then appointments and medical records are not shown and guest bookings are not linked.', 'doctor2go-connect' ); ?></p>
			<?php
		}
		$history = self::guest_history( $user->ID );
		if ( ! $history ) {
			return;
		}
		$types     = self::type_labels();
		$linked_by = array(
			D2GC_Client_Refs::LINKED_BY_REGISTRATION => __( 'Confirmed registration', 'doctor2go-connect' ),
			D2GC_Client_Refs::LINKED_BY_LOGIN        => __( 'Login', 'doctor2go-connect' ),
		);
		?>
		<h2><?php echo esc_html__( 'Guest bookings before the account', 'doctor2go-connect' ); ?></h2>
		<p class="description"><?php echo esc_html__( 'Bookings this person made as a guest, with the same e-mail address, that were linked to this account.', 'doctor2go-connect' ); ?></p>
		<table class="widefat striped" style="max-width:900px">
			<thead><tr>
				<th><?php echo esc_html__( 'Booking', 'doctor2go-connect' ); ?></th>
				<th><?php echo esc_html__( 'Type', 'doctor2go-connect' ); ?></th>
				<th><?php echo esc_html__( 'Doctor', 'doctor2go-connect' ); ?></th>
				<th><?php echo esc_html__( 'Booked on', 'doctor2go-connect' ); ?></th>
				<th><?php echo esc_html__( 'Linked on', 'doctor2go-connect' ); ?></th>
				<th><?php echo esc_html__( 'Linked by', 'doctor2go-connect' ); ?></th>
			</tr></thead>
			<tbody>
			<?php foreach ( $history as $entry ) { ?>
				<tr>
					<td><?php echo wp_kses_post( self::booking_link( $entry['booking_ref'] ?? '' ) ); ?></td>
					<td><?php echo esc_html( $types[ $entry['type'] ?? '' ] ?? ( $entry['type'] ?? '' ) ); ?></td>
					<td><?php echo esc_html( self::doctor_name( $entry['doctor_post_id'] ?? 0 ) ); ?></td>
					<td><?php echo esc_html( self::local_time( $entry['booked_at'] ?? '' ) ); ?></td>
					<td><?php echo esc_html( self::local_time( $entry['linked_at'] ?? '' ) ); ?></td>
					<td><?php echo esc_html( $linked_by[ $entry['linked_by'] ?? '' ] ?? '' ); ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<?php
	}

	/** (manage_users_columns) */
	public static function users_column( $columns ) {
		$columns['d2gc_email_confirmed'] = __( 'E-mail confirmed', 'doctor2go-connect' );
		$columns['d2gc_guest_before']    = __( 'Guest before', 'doctor2go-connect' );
		return $columns;
	}

	/**
	 * (manage_users_custom_column) E-mail confirmed: yes/no (accounts from before the confirmation count as yes).
	 * Guest before: number and date of the guest bookings, empty when the account never was a guest.
	 */
	public static function users_column_value( $value, $column, $user_id ) {
		if ( 'd2gc_email_confirmed' === $column ) {
			return D2GC_Email_Verification::is_verified( $user_id )
				? esc_html__( 'Yes', 'doctor2go-connect' )
				: '<strong>' . esc_html__( 'No', 'doctor2go-connect' ) . '</strong>';
		}
		if ( 'd2gc_guest_before' !== $column ) {
			return $value;
		}
		$history = self::guest_history( $user_id );
		if ( ! $history ) {
			return '';
		}
		$first = min( array_map( function ( $entry ) { return (string) ( $entry['booked_at'] ?? '' ); }, $history ) );
		/* translators: 1: number of bookings, 2: date of the first one */
		return esc_html( sprintf( _n( '%1$d booking, since %2$s', '%1$d bookings, since %2$s', count( $history ), 'doctor2go-connect' ), count( $history ), wp_date( get_option( 'date_format' ), strtotime( $first . ' UTC' ) ) ) );
	}

	private static function pagination( $total, $paged, $base_url ) {
		$pages = (int) ceil( $total / self::PER_PAGE );
		if ( $pages > 1 ) {
			echo '<div class="tablenav"><div class="tablenav-pages">';
			echo wp_kses_post( paginate_links( array( 'base' => add_query_arg( 'paged', '%#%', $base_url ), 'format' => '', 'current' => $paged, 'total' => $pages ) ) );
			echo '</div></div>';
		}
	}
}
