<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin page Doctor2Go Connect → Leads: follow the leads (status, attempts, errors), find them by e-mail address,
 * delete them (e.g. when someone asks) and send failed ones again. Administrators only.
 */
class D2GC_Leads_Admin {

	const PAGE     = 'd2gc-leads';
	const ACTION   = 'd2gc_leads_action';
	const PER_PAGE = 50;

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handle' ) );
	}

	public static function menu() {
		add_submenu_page( 'd2g-connect-admin-display', __( 'Leads', 'doctor2go-connect' ), __( 'Leads', 'doctor2go-connect' ), 'manage_options', self::PAGE, array( __CLASS__, 'render' ) );
	}

	private static function url( $args = array() ) {
		return add_query_arg( array_merge( array( 'page' => self::PAGE ), $args ), admin_url( 'admin.php' ) );
	}

	private static function action_url( $do, $args ) {
		return wp_nonce_url( add_query_arg( array_merge( array( 'action' => self::ACTION, 'do' => $do ), $args ), admin_url( 'admin-post.php' ) ), self::ACTION );
	}

	/**
	 * (admin-post) delete, delete_email, retry.
	 */
	public static function handle() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'doctor2go-connect' ), 403 );
		}
		check_admin_referer( self::ACTION );
		global $wpdb;
		$table   = D2GC_Leads::table();
		$do      = isset( $_GET['do'] ) ? sanitize_key( wp_unslash( $_GET['do'] ) ) : '';
		$lead_id = isset( $_GET['lead'] ) ? sanitize_text_field( wp_unslash( $_GET['lead'] ) ) : '';
		$email   = isset( $_GET['email'] ) ? sanitize_email( wp_unslash( $_GET['email'] ) ) : '';
		$done    = 0;

		if ( 'delete' === $do && $lead_id ) {
			$done = (int) $wpdb->delete( $table, array( 'lead_id' => $lead_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		} elseif ( 'delete_email' === $do && is_email( $email ) ) {
			$done = (int) $wpdb->delete( $table, array( 'email' => $email ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$email = '';
		} elseif ( 'retry' === $do && $lead_id ) {
			$wpdb->update( $table, array( 'status' => 'pending', 'attempts' => 0 ), array( 'lead_id' => $lead_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$result = D2GC_Leads::attempt( $lead_id );
			$done   = 'sent' === $result['status'] ? 1 : 0;
		}

		wp_safe_redirect( self::url( array( 'done' => $do, 'count' => $done, 's' => $email ) ) );
		exit;
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		D2GC_Leads::maybe_install();
		global $wpdb;
		$table  = D2GC_Leads::table();
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- search filter only
		$paged  = max( 1, isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$where  = '' !== $search ? $wpdb->prepare( 'WHERE email LIKE %s', '%' . $wpdb->esc_like( $search ) . '%' ) : '';
		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- table name and the prepared $where
		$total  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table $where" );
		$rows   = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table $where ORDER BY id DESC LIMIT %d OFFSET %d", self::PER_PAGE, ( $paged - 1 ) * self::PER_PAGE ) );
		$counts = $wpdb->get_results( "SELECT status, COUNT(*) AS n FROM $table GROUP BY status", OBJECT_K );
		// phpcs:enable
		$days    = max( 1, (int) apply_filters( 'd2gc_lead_retention_days', 30 ) );
		$labels  = array(
			'sent'    => __( 'Sent', 'doctor2go-connect' ),
			'pending' => __( 'Waiting for retry', 'doctor2go-connect' ),
			'failed'  => __( 'Failed', 'doctor2go-connect' ),
		);
		$done    = isset( $_GET['done'] ) ? sanitize_key( wp_unslash( $_GET['done'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$count   = isset( $_GET['count'] ) ? absint( $_GET['count'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Leads', 'doctor2go-connect' ); ?></h1>
			<p>
				<?php
				/* translators: %d: number of days */
				printf( esc_html__( 'E-mail addresses that visitors left to receive a link from doctor2go.online. Leads are deleted automatically after %d days.', 'doctor2go-connect' ), (int) $days );
				?>
				<br><?php echo esc_html__( 'Deleting a lead here does not delete it at doctor2go.online; for a deletion request, also ask doctor2go.online.', 'doctor2go-connect' ); ?>
			</p>

			<?php if ( 'delete' === $done || 'delete_email' === $done ) { ?>
				<div class="notice notice-success is-dismissible"><p>
					<?php
					/* translators: %d: number of leads */
					printf( esc_html( _n( '%d lead deleted.', '%d leads deleted.', $count, 'doctor2go-connect' ) ), (int) $count );
					?>
				</p></div>
			<?php } elseif ( 'retry' === $done ) { ?>
				<div class="notice <?php echo $count ? 'notice-success' : 'notice-error'; ?> is-dismissible"><p><?php echo $count ? esc_html__( 'The lead has been sent.', 'doctor2go-connect' ) : esc_html__( 'Sending failed again, see the error in the list.', 'doctor2go-connect' ); ?></p></div>
			<?php } ?>

			<ul class="subsubsub">
				<?php foreach ( $labels as $status => $label ) { ?>
					<li><?php echo esc_html( $label ); ?> <span class="count">(<?php echo (int) ( $counts[ $status ]->n ?? 0 ); ?>)</span><?php echo 'failed' !== $status ? ' |' : ''; ?></li>
				<?php } ?>
			</ul>

			<form method="get" class="search-form" style="float:right;margin:8px 0">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE ); ?>">
				<label class="screen-reader-text" for="d2gc-lead-search"><?php echo esc_html__( 'Search by e-mail address', 'doctor2go-connect' ); ?></label>
				<input type="search" id="d2gc-lead-search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php echo esc_attr__( 'E-mail address', 'doctor2go-connect' ); ?>">
				<button class="button"><?php echo esc_html__( 'Search', 'doctor2go-connect' ); ?></button>
			</form>

			<?php if ( '' !== $search && is_email( $search ) && $total ) { ?>
				<p style="clear:both">
					<a class="button button-link-delete" href="<?php echo esc_url( self::action_url( 'delete_email', array( 'email' => $search ) ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete all leads of this e-mail address?', 'doctor2go-connect' ) ); ?>')">
						<?php
						/* translators: %d: number of leads */
						printf( esc_html( _n( 'Delete the %d lead of this e-mail address', 'Delete all %d leads of this e-mail address', $total, 'doctor2go-connect' ) ), (int) $total );
						?>
					</a>
				</p>
			<?php } ?>

			<table class="widefat striped" style="clear:both">
				<thead><tr>
					<th><?php echo esc_html__( 'Date', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'E-mail', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'Source', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'Status', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'Attempts', 'doctor2go-connect' ); ?></th>
					<th><?php echo esc_html__( 'Last error', 'doctor2go-connect' ); ?></th>
					<th></th>
				</tr></thead>
				<tbody>
				<?php if ( ! $rows ) { ?>
					<tr><td colspan="7"><?php echo esc_html__( 'No leads found.', 'doctor2go-connect' ); ?></td></tr>
				<?php } ?>
				<?php foreach ( $rows as $row ) { ?>
					<tr>
						<td><?php echo esc_html( $row->created_at ); ?></td>
						<td><a href="<?php echo esc_url( self::url( array( 's' => $row->email ) ) ); ?>"><?php echo esc_html( $row->email ); ?></a></td>
						<td><?php echo esc_html( $row->source_type . ' / ' . $row->intent ); ?></td>
						<td>
							<?php echo esc_html( $labels[ $row->status ] ?? $row->status ); ?>
							<?php if ( 'pending' === $row->status && $row->next_attempt ) { ?>
								<br><small><?php echo esc_html( sprintf( /* translators: %s: date and time */ __( 'next: %s', 'doctor2go-connect' ), $row->next_attempt ) ); ?></small>
							<?php } ?>
						</td>
						<td><?php echo (int) $row->attempts; ?></td>
						<td><small><?php echo esc_html( (string) $row->last_error ); ?></small></td>
						<td style="white-space:nowrap">
							<?php if ( 'sent' !== $row->status ) { ?>
								<a href="<?php echo esc_url( self::action_url( 'retry', array( 'lead' => $row->lead_id ) ) ); ?>"><?php echo esc_html__( 'Send again', 'doctor2go-connect' ); ?></a> |
							<?php } ?>
							<a class="submitdelete" href="<?php echo esc_url( self::action_url( 'delete', array( 'lead' => $row->lead_id ) ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this lead?', 'doctor2go-connect' ) ); ?>')"><?php echo esc_html__( 'Delete', 'doctor2go-connect' ); ?></a>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>

			<?php
			$pages = (int) ceil( $total / self::PER_PAGE );
			if ( $pages > 1 ) {
				echo '<div class="tablenav"><div class="tablenav-pages">';
				echo wp_kses_post( paginate_links( array( 'base' => add_query_arg( 'paged', '%#%', self::url( array( 's' => $search ) ) ), 'format' => '', 'current' => $paged, 'total' => $pages ) ) );
				echo '</div></div>';
			}
			?>
		</div>
		<?php
	}
}
