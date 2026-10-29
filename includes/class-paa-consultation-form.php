<?php
/**
 * Consultation request form: [consultation_form].
 *
 * Borrows the best ideas from the top-reviewed law-firm plugins and fixes
 * their flaws: intake questions with a practice-area dropdown and an
 * other-parties field for conflict checks (Easy Appointments), a required
 * no-attorney-client-relationship disclaimer checkbox (Clio Grow Form),
 * and saved entries (Forminator) — but unlike Forminator the entries live
 * as private posts, never as public files. Spam is filtered with a honeypot
 * (Contact Form 7 ships with no spam protection by default). Submissions
 * are registered with WordPress's privacy exporter and eraser, which only
 * Forminator and Site Reviews did in the tested field.
 *
 * @package PAA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PAA_Consultation_Form {

	/**
	 * Register the shortcode, the form handler, and the privacy tools.
	 */
	public static function hooks() {
		add_shortcode( 'consultation_form', array( __CLASS__, 'render' ) );
		add_action( 'admin_post_paa_consultation', array( __CLASS__, 'submit' ) );
		add_action( 'admin_post_nopriv_paa_consultation', array( __CLASS__, 'submit' ) );
		add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'register_exporter' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'register_eraser' ) );
	}

	/**
	 * Render the form, plus a notice after a redirect back from submit().
	 *
	 * @return string
	 */
	public static function render() {
		wp_enqueue_style( 'paa-frontend' );

		$notice = '';
		if ( isset( $_GET['paa_cf'] ) ) {
			$status = sanitize_key( wp_unslash( $_GET['paa_cf'] ) );
			if ( 'sent' === $status ) {
				$notice = '<p class="paa-notice paa-notice--success">' . esc_html__( 'Thank you. Your request has been sent and the firm will be in touch.', 'paa' ) . '</p>';
			} elseif ( 'error' === $status ) {
				$notice = '<p class="paa-notice paa-notice--error">' . esc_html__( 'Please check the form: name, a valid email address, and the disclaimer checkbox are required.', 'paa' ) . '</p>';
			}
		}

		$terms = get_terms(
			array(
				'taxonomy'   => PAA_Post_Types::TAX_PRACTICE,
				'hide_empty' => false,
			)
		);

		ob_start();
		echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- notice escaped above.
		?>
		<form class="paa-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'paa_consultation_submit', 'paa_cf_nonce' ); ?>
			<input type="hidden" name="action" value="paa_consultation">
			<p class="paa-hp" aria-hidden="true">
				<label><?php esc_html_e( 'Website', 'paa' ); ?>
					<input type="text" name="paa_website" tabindex="-1" autocomplete="off">
				</label>
			</p>
			<p>
				<label for="paa-cf-name"><?php esc_html_e( 'Your name *', 'paa' ); ?></label>
				<input id="paa-cf-name" type="text" name="paa_cf_name" required>
			</p>
			<p>
				<label for="paa-cf-email"><?php esc_html_e( 'Email *', 'paa' ); ?></label>
				<input id="paa-cf-email" type="email" name="paa_cf_email" required>
			</p>
			<p>
				<label for="paa-cf-phone"><?php esc_html_e( 'Phone', 'paa' ); ?></label>
				<input id="paa-cf-phone" type="tel" name="paa_cf_phone">
			</p>
			<p>
				<label for="paa-cf-practice"><?php esc_html_e( 'Practice area', 'paa' ); ?></label>
				<select id="paa-cf-practice" name="paa_cf_practice">
					<option value=""><?php esc_html_e( 'Select a practice area', 'paa' ); ?></option>
					<?php
					if ( $terms && ! is_wp_error( $terms ) ) {
						foreach ( $terms as $term ) {
							?>
							<option value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></option>
							<?php
						}
					}
					?>
				</select>
			</p>
			<p>
				<label for="paa-cf-parties"><?php esc_html_e( 'Names of the other parties (for a conflict check)', 'paa' ); ?></label>
				<input id="paa-cf-parties" type="text" name="paa_cf_parties">
			</p>
			<p>
				<label for="paa-cf-matter"><?php esc_html_e( 'Briefly describe your matter', 'paa' ); ?></label>
				<textarea id="paa-cf-matter" name="paa_cf_matter" rows="5"></textarea>
			</p>
			<p>
				<label>
					<input type="checkbox" name="paa_cf_consent" value="1" required>
					<?php esc_html_e( 'I understand that sending this form does not create an attorney-client relationship.', 'paa' ); ?>
				</label>
			</p>
			<p>
				<button type="submit"><?php esc_html_e( 'Request a Consultation', 'paa' ); ?></button>
			</p>
		</form>
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle a submission: validate, save as a private post, email the firm,
	 * redirect back with a status flag.
	 */
	public static function submit() {
		$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );
		$fail     = function () use ( $redirect ) {
			wp_safe_redirect( add_query_arg( 'paa_cf', 'error', $redirect ) );
			exit;
		};

		if ( ! isset( $_POST['paa_cf_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['paa_cf_nonce'] ) ), 'paa_consultation_submit' )
		) {
			$fail();
		}

		// Honeypot filled: a bot. Show success so the bot learns nothing,
		// but save and send nothing.
		if ( isset( $_POST['paa_website'] ) && '' !== sanitize_text_field( wp_unslash( $_POST['paa_website'] ) ) ) {
			wp_safe_redirect( add_query_arg( 'paa_cf', 'sent', $redirect ) );
			exit;
		}

		$name    = isset( $_POST['paa_cf_name'] ) ? sanitize_text_field( wp_unslash( $_POST['paa_cf_name'] ) ) : '';
		$email   = isset( $_POST['paa_cf_email'] ) ? sanitize_email( wp_unslash( $_POST['paa_cf_email'] ) ) : '';
		$phone   = isset( $_POST['paa_cf_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['paa_cf_phone'] ) ) : '';
		$practice = isset( $_POST['paa_cf_practice'] ) ? sanitize_key( wp_unslash( $_POST['paa_cf_practice'] ) ) : '';
		$parties = isset( $_POST['paa_cf_parties'] ) ? sanitize_text_field( wp_unslash( $_POST['paa_cf_parties'] ) ) : '';
		$matter  = isset( $_POST['paa_cf_matter'] ) ? sanitize_textarea_field( wp_unslash( $_POST['paa_cf_matter'] ) ) : '';
		$consent = isset( $_POST['paa_cf_consent'] ) && '1' === $_POST['paa_cf_consent'];

		if ( '' === $name || ! is_email( $email ) || ! $consent ) {
			$fail();
		}

		$practice_name = '';
		if ( '' !== $practice ) {
			$term = get_term_by( 'slug', $practice, PAA_Post_Types::TAX_PRACTICE );
			if ( ! $term ) {
				$fail();
			}
			$practice_name = $term->name;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => PAA_Post_Types::CONSULTATION,
				'post_title'   => sprintf(
					/* translators: %s: name of the person requesting a consultation. */
					__( 'Consultation request from %s', 'paa' ),
					$name
				),
				'post_content' => $matter,
				'post_status'  => 'private',
				'meta_input'   => array(
					'_paa_cf_email'    => $email,
					'_paa_cf_phone'    => $phone,
					'_paa_cf_practice' => $practice_name,
					'_paa_cf_parties'  => $parties,
					'_paa_cf_consent'  => '1',
				),
			)
		);

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			$fail();
		}

		$to = PAA_Settings::get( 'consultation_email' );
		if ( ! is_email( $to ) ) {
			$to = get_option( 'admin_email' );
		}

		$lines = array(
			/* translators: %s: name of the person requesting a consultation. */
			sprintf( __( 'Name: %s', 'paa' ), $name ),
			sprintf( __( 'Email: %s', 'paa' ), $email ),
		);
		if ( '' !== $phone ) {
			$lines[] = sprintf( __( 'Phone: %s', 'paa' ), $phone );
		}
		if ( '' !== $practice_name ) {
			$lines[] = sprintf( __( 'Practice area: %s', 'paa' ), $practice_name );
		}
		if ( '' !== $parties ) {
			$lines[] = sprintf( __( 'Other parties: %s', 'paa' ), $parties );
		}
		if ( '' !== $matter ) {
			$lines[] = sprintf( __( 'Matter: %s', 'paa' ), $matter );
		}

		// The request is already saved as a private post, so a mail failure
		// still ends in success: the firm can find it in the admin.
		wp_mail(
			$to,
			sprintf(
				/* translators: %s: name of the person requesting a consultation. */
				__( 'New consultation request from %s', 'paa' ),
				$name
			),
			implode( "\n", $lines )
		);

		wp_safe_redirect( add_query_arg( 'paa_cf', 'sent', $redirect ) );
		exit;
	}

	/**
	 * Find a requester's submissions for a privacy export.
	 *
	 * @param string $email_address Requester's email.
	 * @return array
	 */
	public static function export_personal_data( $email_address ) {
		$query = new WP_Query(
			array(
				'post_type'      => PAA_Post_Types::CONSULTATION,
				'post_status'    => 'private',
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'   => '_paa_cf_email',
						'value' => $email_address,
					),
				),
			)
		);

		$data = array();
		foreach ( $query->posts as $post ) {
			$data[] = array(
				'group_id'    => 'paa-consultation-requests',
				'group_label' => __( 'Consultation requests', 'paa' ),
				'item_id'     => 'paa-consultation-' . $post->ID,
				'data'        => array(
					array(
						'name'  => __( 'Name', 'paa' ),
						'value' => get_the_title( $post ),
					),
					array(
						'name'  => __( 'Practice area', 'paa' ),
						'value' => get_post_meta( $post->ID, '_paa_cf_practice', true ),
					),
					array(
						'name'  => __( 'Matter', 'paa' ),
						'value' => $post->post_content,
					),
				),
			);
		}

		return array(
			'data' => $data,
			'done' => true,
		);
	}

	/**
	 * Anonymize a requester's submissions for a privacy erasure.
	 *
	 * @param string $email_address Requester's email.
	 * @return array
	 */
	public static function erase_personal_data( $email_address ) {
		$query = new WP_Query(
			array(
				'post_type'      => PAA_Post_Types::CONSULTATION,
				'post_status'    => 'private',
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'   => '_paa_cf_email',
						'value' => $email_address,
					),
				),
			)
		);

		$removed = 0;
		foreach ( $query->posts as $post ) {
			wp_update_post(
				array(
					'ID'           => $post->ID,
					'post_title'   => __( 'Anonymous', 'paa' ),
					'post_content' => '',
				)
			);
			delete_post_meta( $post->ID, '_paa_cf_email' );
			delete_post_meta( $post->ID, '_paa_cf_phone' );
			delete_post_meta( $post->ID, '_paa_cf_parties' );
			$removed++;
		}

		return array(
			'items_removed'  => $removed,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}

	/**
	 * Register the exporter with WordPress's privacy tools.
	 *
	 * @param array $exporters Registered exporters.
	 * @return array
	 */
	public static function register_exporter( $exporters ) {
		$exporters['paa-consultation-requests'] = array(
			'exporter_friendly_name' => __( 'Practice Areas & Attorneys consultation requests', 'paa' ),
			'callback'               => array( __CLASS__, 'export_personal_data' ),
		);
		return $exporters;
	}

	/**
	 * Register the eraser with WordPress's privacy tools.
	 *
	 * @param array $erasers Registered erasers.
	 * @return array
	 */
	public static function register_eraser( $erasers ) {
		$erasers['paa-consultation-requests'] = array(
			'eraser_friendly_name' => __( 'Practice Areas & Attorneys consultation requests', 'paa' ),
			'callback'             => array( __CLASS__, 'erase_personal_data' ),
		);
		return $erasers;
	}
}
