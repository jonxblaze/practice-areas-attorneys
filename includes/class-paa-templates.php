<?php
/**
 * Loads the plugin's template files, deferring to the active theme:
 * if the theme provides single-attorney.php or archive-practice-area.php,
 * those win; otherwise the bundled templates are used.
 *
 * @package PAA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PAA_Templates {

	/**
	 * Wire up the template_include filter and the fallback stylesheet.
	 */
	public static function hooks() {
		add_filter( 'template_include', array( __CLASS__, 'template_include' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_fallback_styles' ) );
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

		return $template;
	}
}
