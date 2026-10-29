<?php
/**
 * Admin fields for both post types, saved with nonce verification,
 * capability checks, and strict sanitization.
 *
 * @package PAA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PAA_Meta_Boxes {

	const NONCE_ACTION = 'paa_save_meta';
	const NONCE_NAME   = 'paa_meta_nonce';

	/**
	 * Field definitions per post type: meta key => sanitizer callback.
	 *
	 * @var array
	 */
	private static $fields = array(
		PAA_Post_Types::PRACTICE_AREA => array(
			'_paa_tagline' => 'sanitize_text_field',
		),
		PAA_Post_Types::ATTORNEY      => array(
			'_paa_job_title' => 'sanitize_text_field',
			'_paa_phone'     => 'sanitize_text_field',
			'_paa_email'     => 'sanitize_email',
			'_paa_bar'       => 'sanitize_text_field',
			'_paa_education' => 'sanitize_textarea_field',
			'_paa_experience' => 'sanitize_text_field',
			'_paa_languages' => 'sanitize_text_field',
		),
		PAA_Post_Types::TESTIMONIAL   => array(
			'_paa_testimonial_rating' => 'absint',
		),
		PAA_Post_Types::CASE_RESULT   => array(
			'_paa_case_outcome' => 'sanitize_text_field',
			'_paa_case_year'    => 'sanitize_text_field',
		),
	);

	/**
	 * Wire up meta box registration and saving.
	 */
	public static function hooks() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'register' ) );
		add_action( 'save_post', array( __CLASS__, 'save' ), 10, 2 );
	}

	/**
	 * Register the meta boxes.
	 */
	public static function register() {
		add_meta_box(
			'paa-practice-area',
			__( 'Practice Area Details', 'paa' ),
			array( __CLASS__, 'render_practice_area' ),
			PAA_Post_Types::PRACTICE_AREA,
			'side'
		);
		add_meta_box(
			'paa-attorney',
			__( 'Attorney Details', 'paa' ),
			array( __CLASS__, 'render_attorney' ),
			PAA_Post_Types::ATTORNEY,
			'normal',
			'high'
		);
		add_meta_box(
			'paa-testimonial',
			__( 'Testimonial Details', 'paa' ),
			array( __CLASS__, 'render_testimonial' ),
			PAA_Post_Types::TESTIMONIAL,
			'side'
		);
		add_meta_box(
			'paa-case-result',
			__( 'Case Result Details', 'paa' ),
			array( __CLASS__, 'render_case_result' ),
			PAA_Post_Types::CASE_RESULT,
			'side'
		);
	}

	/**
	 * Render the Practice Area box.
	 *
	 * @param WP_Post $post Current post.
	 */
	public static function render_practice_area( $post ) {
		self::nonce_field();
		$tagline = get_post_meta( $post->ID, '_paa_tagline', true );
		?>
		<p>
			<label for="paa-tagline"><?php esc_html_e( 'Tagline', 'paa' ); ?></label><br>
			<input type="text" id="paa-tagline" name="paa_tagline" class="widefat"
				value="<?php echo esc_attr( $tagline ); ?>"
				placeholder="<?php esc_attr_e( 'One line shown under the title', 'paa' ); ?>">
		</p>
		<?php
	}

	/**
	 * Render the Attorney box.
	 *
	 * @param WP_Post $post Current post.
	 */
	public static function render_attorney( $post ) {
		self::nonce_field();
		$meta = array(
			'job_title'  => get_post_meta( $post->ID, '_paa_job_title', true ),
			'phone'      => get_post_meta( $post->ID, '_paa_phone', true ),
			'email'      => get_post_meta( $post->ID, '_paa_email', true ),
			'bar'        => get_post_meta( $post->ID, '_paa_bar', true ),
			'education'  => get_post_meta( $post->ID, '_paa_education', true ),
			'experience' => get_post_meta( $post->ID, '_paa_experience', true ),
			'languages'  => get_post_meta( $post->ID, '_paa_languages', true ),
		);
		?>
		<table class="form-table">
			<tr>
				<th><label for="paa-job-title"><?php esc_html_e( 'Job title', 'paa' ); ?></label></th>
				<td><input type="text" id="paa-job-title" name="paa_job_title" class="regular-text"
					value="<?php echo esc_attr( $meta['job_title'] ); ?>"
					placeholder="<?php esc_attr_e( 'e.g. Founding Partner', 'paa' ); ?>"></td>
			</tr>
			<tr>
				<th><label for="paa-phone"><?php esc_html_e( 'Phone', 'paa' ); ?></label></th>
				<td><input type="tel" id="paa-phone" name="paa_phone" class="regular-text"
					value="<?php echo esc_attr( $meta['phone'] ); ?>"></td>
			</tr>
			<tr>
				<th><label for="paa-email"><?php esc_html_e( 'Email', 'paa' ); ?></label></th>
				<td><input type="email" id="paa-email" name="paa_email" class="regular-text"
					value="<?php echo esc_attr( $meta['email'] ); ?>"></td>
			</tr>
			<tr>
				<th><label for="paa-bar"><?php esc_html_e( 'Bar admissions', 'paa' ); ?></label></th>
				<td><input type="text" id="paa-bar" name="paa_bar" class="regular-text"
					value="<?php echo esc_attr( $meta['bar'] ); ?>"
					placeholder="<?php esc_attr_e( 'e.g. Texas, 2011', 'paa' ); ?>"></td>
			</tr>
			<tr>
				<th><label for="paa-education"><?php esc_html_e( 'Education', 'paa' ); ?></label></th>
				<td><textarea id="paa-education" name="paa_education" rows="3" class="large-text"
					placeholder="<?php esc_attr_e( 'e.g. J.D., University of Houston Law Center, 2010', 'paa' ); ?>"><?php echo esc_textarea( $meta['education'] ); ?></textarea></td>
			</tr>
			<tr>
				<th><label for="paa-experience"><?php esc_html_e( 'Experience', 'paa' ); ?></label></th>
				<td><input type="text" id="paa-experience" name="paa_experience" class="regular-text"
					value="<?php echo esc_attr( $meta['experience'] ); ?>"
					placeholder="<?php esc_attr_e( 'e.g. 15 years', 'paa' ); ?>"></td>
			</tr>
			<tr>
				<th><label for="paa-languages"><?php esc_html_e( 'Languages', 'paa' ); ?></label></th>
				<td><input type="text" id="paa-languages" name="paa_languages" class="regular-text"
					value="<?php echo esc_attr( $meta['languages'] ); ?>"
					placeholder="<?php esc_attr_e( 'e.g. English, Spanish', 'paa' ); ?>"></td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render the Testimonial box.
	 *
	 * @param WP_Post $post Current post.
	 */
	public static function render_testimonial( $post ) {
		self::nonce_field();
		$rating = (int) get_post_meta( $post->ID, '_paa_testimonial_rating', true );
		?>
		<p>
			<label for="paa-rating"><?php esc_html_e( 'Star rating', 'paa' ); ?></label><br>
			<select id="paa-rating" name="paa_testimonial_rating" class="widefat">
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
					<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $rating, $i ); ?>>
						<?php echo esc_html( sprintf( _n( '%d star', '%d stars', $i, 'paa' ), $i ) ); ?>
					</option>
				<?php endfor; ?>
			</select>
		</p>
		<?php
	}

	/**
	 * Render the Case Result box.
	 *
	 * @param WP_Post $post Current post.
	 */
	public static function render_case_result( $post ) {
		self::nonce_field();
		$outcome = get_post_meta( $post->ID, '_paa_case_outcome', true );
		$year    = get_post_meta( $post->ID, '_paa_case_year', true );
		?>
		<p>
			<label for="paa-outcome"><?php esc_html_e( 'Outcome', 'paa' ); ?></label><br>
			<input type="text" id="paa-outcome" name="paa_case_outcome" class="widefat"
				value="<?php echo esc_attr( $outcome ); ?>"
				placeholder="<?php esc_attr_e( 'e.g. $2.4M settlement', 'paa' ); ?>">
		</p>
		<p>
			<label for="paa-year"><?php esc_html_e( 'Year', 'paa' ); ?></label><br>
			<input type="text" id="paa-year" name="paa_case_year" class="widefat"
				value="<?php echo esc_attr( $year ); ?>"
				placeholder="<?php esc_attr_e( 'e.g. 2024', 'paa' ); ?>">
		</p>
		<?php
	}

	/**
	 * Print the nonce field shared by all boxes.
	 */
	private static function nonce_field() {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
	}

	/**
	 * Save the meta boxes.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public static function save( $post_id, $post ) {
		// Skip autosaves, revisions, and bulk/quick edits.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Verify the nonce.
		if ( ! isset( $_POST[ self::NONCE_NAME ] )
			|| ! wp_verify_nonce( sanitize_key( $_POST[ self::NONCE_NAME ] ), self::NONCE_ACTION )
		) {
			return;
		}

		// Only handle our post types.
		if ( ! isset( self::$fields[ $post->post_type ] ) ) {
			return;
		}

		// Capability check.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( self::$fields[ $post->post_type ] as $meta_key => $sanitizer ) {
			// Meta key "_paa_tagline" maps to input name "paa_tagline".
			$input_name = ltrim( $meta_key, '_' );

			if ( ! isset( $_POST[ $input_name ] ) ) {
				continue;
			}

			$value = call_user_func( $sanitizer, wp_unslash( $_POST[ $input_name ] ) );

			// Keep the star rating inside 1-5 even if the request was tampered with.
			if ( '_paa_testimonial_rating' === $meta_key ) {
				$value = min( 5, max( 1, (int) $value ) );
			}

			if ( '' === $value ) {
				delete_post_meta( $post_id, $meta_key );
			} else {
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
	}
}
