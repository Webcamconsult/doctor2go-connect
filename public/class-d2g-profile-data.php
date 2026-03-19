<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * D2G profile data class
 *
 * @package d2g-connect
 */

class D2G_ProfileData {
	public $doctor;
	public $doctor_meta;
	public $doctor_profile_ID;
	public $locations;
	public $docSlots;
	public $firstAvailibility;
	public $specialties;
	public $languages;
	public $countries;
	public $feat_pic;
	public $feat_pic_square;
	public $feat_pic_full;
	public $walk_in_check;
	public $exps;
	public $edus;
	public $pubs;
	public $tariffs;
	/**
	 * Constructor.
	 */
	function __construct( $post = null, $single = false ) {

		$this->doctor = is_null( $post ) ? get_post( get_the_ID() ) : $post;

		if ( is_object( $this->doctor ) && isset( $this->doctor->ID ) ) {
			$this->doctor_profile_ID = $this->doctor->ID;

			// get all meta data
			$this->doctor_meta = get_post_meta( $this->doctor->ID );
			// set meta data locations
			$this->locations = $this->get_locations( $this->doctor_meta );
			// get terms data
			$this->specialties = get_the_terms( $this->doctor_profile_ID, 'doctor-specialty' );
			$this->languages   = get_the_terms( $this->doctor_profile_ID, 'doctor-language' );
			$this->countries   = get_the_terms( $this->doctor_profile_ID, 'country-origin' );
			// get feat pic data
			if ( get_post_thumbnail_id( $this->doctor_profile_ID ) ) {
				$thumb_id              = get_post_thumbnail_id( $this->doctor_profile_ID );
				$this->feat_pic        = wp_get_attachment_image_src( $thumb_id, 'd2g-doc-pic' )[0];
				$this->feat_pic_square = wp_get_attachment_image_src( $thumb_id, 'd2g-doc-pic-square' )[0];
				$this->feat_pic_full   = wp_get_attachment_image_src( $thumb_id, 'full' )[0];
			} elseif ( get_option( 'd2g_placeholder' ) != '' ) {
					$this->feat_pic        = wp_get_attachment_image_src( get_option( 'd2g_placeholder' ), 'd2g-doc-pic' )[0];
					$this->feat_pic_square = wp_get_attachment_image_src( get_option( 'd2g_placeholder' ), 'd2g-doc-pic-square' )[0];
					$this->feat_pic_full   = wp_get_attachment_image_src( get_option( 'd2g_placeholder' ), 'full' )[0];
			} else {
				$this->feat_pic = plugin_dir_url( __FILE__ ) . 'images/doctor-placeholder.jpg';
			}

			// sets work experiences / educations / puublications as array in the profile data object
			$this->exps = $this->d2g_unserialzer_checker( $this->doctor_meta['exps'] );
			$this->edus = $this->d2g_unserialzer_checker( $this->doctor_meta['edus'] );
			$this->pubs = $this->d2g_unserialzer_checker( $this->doctor_meta['pubs'] );
		} else {
			if ( get_option( 'd2g_debug' ) == 1 ) {
				error_log( 'Warning: $this->doctor is not an object: ' . print_r( $this->doctor, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_print_r -- Debug logging.
			}
			$this->doctor_profile_ID = null; // fallback
		}

		return $this->doctor;
	}



	private function get_locations( $doctor_meta ) {
		if ( isset( $doctor_meta['locations_to_go'] ) ) {
			$this->locations = unserialize( $doctor_meta['locations_to_go'][0] );
		}
		return $this->locations;
	}

	private function d2g_unserialzer_checker( $meta_field = '' ) {
		if ( empty( $meta_field ) || ! isset( $meta_field[0] ) ) {
			return false;
		}

		// Unserialize safely
		$meta_values = @unserialize( $meta_field[0] );
		if ( ! is_array( $meta_values ) || ! isset( $meta_values[0] ) || ! is_array( $meta_values[0] ) ) {
			return false;
		}

		// Check if any value is non-empty
		$checker = false;
		foreach ( $meta_values[0] as $meta_value ) {
			if ( $meta_value !== '' ) {
				$checker = true;
				break; // stop early if one non-empty found
			}
		}

		return $checker ? $meta_values : false;
	}


	/**
	 * @param $docKey
	 * @return mixed
	 */
	public function d2g_get_availability_data( $docKey ) {
		$myTime   = new DateTime();
		$unixTime = $myTime->format( 'U' );
		$superKey = get_option( 'wcc_token' );
		$myHash   = hash( 'sha256', $unixTime . '_' . $docKey . '_' . $superKey );

		$url = get_option( 'api_url_short' ) . 'doclisting/availabilities';

		$body = array(
			'handshake' => array(
				'time'  => (string) $unixTime,
				'token' => $docKey,
				'hash'  => $myHash,
				'type'  => 'user',
			),
			'calendar'  => 'super',
			'nocache'  => microtime(true)
		);

		$args = array(
			'method'      => 'POST',
			'timeout'     => 30,
			'headers'     => array(
				'Content-Type' 	=> 'application/json',
				'Cache-Control' => 'no-cache',
        		'Pragma'        => 'no-cache'
			),
			'body'        => wp_json_encode( $body ),
			'data_format' => 'body'
		);

		$response = wp_remote_request( $url, $args );

		$response_body = wp_remote_retrieve_body( $response );
		$resp          = json_decode( $response_body );

		if ( isset( $resp->availabilities ) ) {
			return $response_body;
		} else {
			$respObj = (object) array(
				'availabilities'  => $resp,
				'user_has_inloop' => false,
				'user_is_active'  => false,

			);
			$respJson = json_encode( $respObj );
			return $respJson;
		}
	}


	public function get_first_avialibility( $docSlots ) {
		if ( $docSlots ) {
			$currUser  = wp_get_current_user();
			$user_meta = get_user_meta( $currUser->data->ID );

			$docSlotsArray = $docSlots;

			usort(
				$docSlotsArray,
				function ( $a, $b ) {
					return strcmp( $a->start, $b->start );
				}
			);

			$datetimeObj = DateTime::createFromFormat( 'Y-m-d\TH:i:s+', $docSlotsArray[0]->start );
			$timezone    = ( get_user_timezone() != '' ) ? get_user_timezone() : 'Europe/Amsterdam';

			if ( $user_meta['p_timezone'][0] ) {
				$timezone = $user_meta['p_timezone'][0];
			}

			$timeZoneChange = new DateTimeZone( $timezone );
			$datetimeObj->setTimezone( $timeZoneChange );

			$this->firstAvailibility = $datetimeObj->format( 'd/m/Y' ) . ' ' . esc_html__( ' at ', 'doctor2go-connect' ) . ' ' . $datetimeObj->format( 'H:i' ) . '  (' . explode( '/', $timezone )[1] . ') ' . '<span class=" icon-info"></span><div class="hidden_info simple_hide">' . esc_html__( 'First availibility', 'doctor2go-connect' ) . '</div>';
			return $this->firstAvailibility;

		}
	}


	public function get_tariffs( $docSlots ) {
		if ( $docSlots ) {
			$docSlotsArray = $docSlots;
			foreach ( $docSlotsArray as $row ) {
				if ( ! isset( $result[ $row->payment_price ] ) ) {
					$tarifsArray[ $row->payment_price ] = array( 'payment_currency' => $row->payment_currency );
				}
			}
			ksort( $tarifsArray );

			return $tarifsArray;
		} else {
			return false;
		}
	}
}
