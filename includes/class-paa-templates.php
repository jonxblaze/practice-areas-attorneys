<?php
/**
 * Loads the plugin's template files, deferring to the active theme:
 * if the theme provides single-attorney.php, archive-practice-area.php,
 * or single-practice-area.php, those win; otherwise the bundled
 * templates are used. Also outputs Attorney schema markup on
 * attorney profile pages.
 *
 * @package PAA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PAA_Templates {

	/**
	 * Wire up the template_include filter, the fallback stylesheet,
	 * and the schema output.
	 */
	public static function hooks() {
		add_filter( 'template_include', array( __CLASS__, 'template_include' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_fallback_styles' ) );
		add_action( 'wp_head', array( __CLASS__, 'output_attorney_schema' ) );
		add_action( 'wp_head', array( __CLASS__, 'output_firm_schema' ) );
		add_action( 'wp_head', array( __CLASS__, 'output_faq_schema' ) );
	}

	/**
	 * Load the bundled stylesheet on our CPT pages, but only when the
	 * theme isn't overriding the template (same condition as below).
	 */
	public static function enqueue_fallback_styles() {
		$fallback = false;

		if ( is_singular( PAA_Post_Types::ATTORNEY ) && ! locate_template( 'single-attorney.php' ) ) {
			$fallback = true;
		}

		if ( is_post_type_archive( PAA_Post_Types::PRACTICE_AREA )
			&& ! locate_template( 'archive-practice-area.php' )
		) {
			$fallback = true;
		}

		if ( is_singular( PAA_Post_Types::PRACTICE_AREA )
			&& ! locate_template( 'single-practice-area.php' )
		) {
			$fallback = true;
		}

		if ( is_singular( PAA_Post_Types::CASE_RESULT ) && ! locate_template( 'single-case-result.php' ) ) {
			$fallback = true;
		}

		if ( $fallback ) {
			wp_enqueue_style( 'paa-frontend' );
		}
	}

	/**
	 * Swap in a bundled template when the theme doesn't provide one.
	 *
	 * @param string $template The template WordPress resolved.
	 * @return string
	 */
	public static function template_include( $template ) {
		if ( is_singular( PAA_Post_Types::ATTORNEY ) ) {
			$theme_template = locate_template( 'single-attorney.php' );
			if ( ! $theme_template ) {
				return PAA_DIR . 'templates/single-attorney.php';
			}
		}

		if ( is_post_type_archive( PAA_Post_Types::PRACTICE_AREA ) ) {
			$theme_template = locate_template( 'archive-practice-area.php' );
			if ( ! $theme_template ) {
				return PAA_DIR . 'templates/archive-practice-area.php';
			}
		}

		if ( is_singular( PAA_Post_Types::PRACTICE_AREA ) ) {
			$theme_template = locate_template( 'single-practice-area.php' );
			if ( ! $theme_template ) {
				return PAA_DIR . 'templates/single-practice-area.php';
			}
		}

		if ( is_singular( PAA_Post_Types::CASE_RESULT ) ) {
			$theme_template = locate_template( 'single-case-result.php' );
			if ( ! $theme_template ) {
				return PAA_DIR . 'templates/single-case-result.php';
			}
		}

		return $template;
	}

	/**
	 * Output Schema.org Attorney markup on attorney profile pages,
	 * using only fields the plugin actually stores.
	 */
	public static function output_attorney_schema() {
		if ( ! is_singular( PAA_Post_Types::ATTORNEY ) ) {
			return;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		$schema = array(
			'@context' => 'https://schema.org',
			'@type'    => 'Attorney',
			'name'     => get_the_title( $post_id ),
			'url'      => get_permalink( $post_id ),
		);

		$job_title = get_post_meta( $post_id, '_paa_job_title', true );
		if ( $job_title ) {
			$schema['jobTitle'] = $job_title;
		}

		$phone = get_post_meta( $post_id, '_paa_phone', true );
		if ( $phone ) {
			$schema['telephone'] = $phone;
		}

		$email = get_post_meta( $post_id, '_paa_email', true );
		if ( $email ) {
			$schema['email'] = $email;
		}

		echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
	}

	/**
	 * Output Schema.org LegalService markup on the front page, from the
	 * firm details on the settings page. LegalService is the type
	 * schema.org recommends for firms (Attorney is deprecated there),
	 * so this complements the Attorney markup on profile pages.
	 */
	public static function output_firm_schema() {
		if ( ! is_front_page() ) {
			return;
		}

		$name = PAA_Settings::get( 'firm_name' );
		if ( '' === $name ) {
			return;
		}

		$schema = array(
			'@context' => 'https://schema.org',
			'@type'    => 'LegalService',
			'name'     => $name,
			'url'      => home_url( '/' ),
		);

		$phone = PAA_Settings::get( 'firm_phone' );
		if ( '' !== $phone ) {
			$schema['telephone'] = $phone;
		}

		$email = PAA_Settings::get( 'firm_email' );
		if ( '' !== $email ) {
			$schema['email'] = $email;
		}

		$address = PAA_Settings::get( 'firm_address' );
		if ( '' !== $address ) {
			$schema['address'] = $address;
		}

		$hours = PAA_Settings::get( 'firm_hours' );
		if ( '' !== $hours ) {
			$schema['openingHours'] = $hours;
		}

		echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
	}

	/**
	 * Output Schema.org FAQPage markup on single practice pages, from
	 * the FAQs assigned to that practice's shared practice terms.
	 */
	public static function output_faq_schema() {
		if ( ! is_singular( PAA_Post_Types::PRACTICE_AREA ) ) {
			return;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		$terms = get_the_terms( $post_id, PAA_Post_Types::TAX_PRACTICE );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return;
		}

		$faqs = new WP_Query(
			array(
				'post_type'      => PAA_Post_Types::FAQ,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'menu_order title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
				'tax_query'      => array(
					array(
						'taxonomy' => PAA_Post_Types::TAX_PRACTICE,
						'field'    => 'term_id',
						'terms'    => wp_list_pluck( $terms, 'term_id' ),
					),
				),
			)
		);

		if ( ! $faqs->have_posts() ) {
			return;
		}

		$questions = array();
		while ( $faqs->have_posts() ) {
			$faqs->the_post();
			$questions[] = array(
				'@type'          => 'Question',
				'name'           => get_the_title(),
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => wp_strip_all_tags( get_the_content() ),
				),
			);
		}
		wp_reset_postdata();

		echo '<script type="application/ld+json">' . wp_json_encode(
			array(
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'mainEntity' => $questions,
			)
		) . '</script>' . "\n";
	}
}
