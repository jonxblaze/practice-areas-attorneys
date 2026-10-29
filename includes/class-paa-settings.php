<?php
/**
 * Firm settings page (Settings → Practice Areas & Attorneys).
 *
 * Stores the firm details used by the LegalService schema and the
 * [firm_contact_card] shortcode, plus the inbox for consultation requests.
 *
 * @package PAA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PAA_Settings {

	const OPTION = 'paa_settings';

	/**
	 * Register the settings page and fields.
	 */
	public static function hooks() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Add the page under Settings.
	 */
	public static function add_page() {
		add_options_page(
			__( 'Practice Areas & Attorneys', 'paa' ),
			__( 'Practice Areas & Attorneys', 'paa' ),
			'manage_options',
			'paa-settings',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register the option and its fields.
	 */
	public static function register() {
		register_setting(
			'paa_settings',
			self::OPTION,
			array( 'sanitize_callback' => array( __CLASS__, 'sanitize' ) )
		);

		add_settings_section(
			'paa_firm',
			__( 'Firm details', 'paa' ),
			'__return_false',
			'paa-settings'
		);

		$fields = array(
			'firm_name'          => __( 'Firm name', 'paa' ),
			'firm_phone'         => __( 'Phone', 'paa' ),
			'firm_email'         => __( 'Email', 'paa' ),
			'firm_address'       => __( 'Address', 'paa' ),
			'firm_hours'         => __( 'Opening hours', 'paa' ),
			'consultation_email' => __( 'Consultation request inbox', 'paa' ),
		);

		foreach ( $fields as $key => $label ) {
			add_settings_field(
				'paa_' . $key,
				$label,
				array( __CLASS__, 'render_field' ),
				'paa-settings',
				'paa_firm',
				array( 'key' => $key )
			);
		}
	}

	/**
	 * Sanitize the option array.
	 *
	 * @param array $input Raw submitted values.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$clean = array();
		$text  = array( 'firm_name', 'firm_phone', 'firm_hours' );
		foreach ( $text as $key ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( wp_unslash( $input[ $key ] ) ) : '';
		}
		$clean['firm_address'] = isset( $input['firm_address'] ) ? sanitize_textarea_field( wp_unslash( $input['firm_address'] ) ) : '';
		$emails = array( 'firm_email', 'consultation_email' );
		foreach ( $emails as $key ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? sanitize_email( wp_unslash( $input[ $key ] ) ) : '';
		}
		return $clean;
	}

	/**
	 * Render one settings field.
	 *
	 * @param array $args Field arguments (key).
	 */
	public static function render_field( $args ) {
		$key   = $args['key'];
		$value = self::get( $key );
		if ( 'firm_address' === $key ) {
			?>
			<textarea name="<?php echo esc_attr( self::OPTION . '[' . $key . ']' ); ?>" rows="3" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
			<?php
		} else {
			?>
			<input type="text" name="<?php echo esc_attr( self::OPTION . '[' . $key . ']' ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
			<?php
		}
	}

	/**
	 * Render the settings page.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Practice Areas & Attorneys', 'paa' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'paa_settings' );
				do_settings_sections( 'paa-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Get one stored setting.
	 *
	 * @param string $key Setting key.
	 * @return string
	 */
	public static function get( $key ) {
		$options = get_option( self::OPTION, array() );
		return isset( $options[ $key ] ) ? $options[ $key ] : '';
	}
}
