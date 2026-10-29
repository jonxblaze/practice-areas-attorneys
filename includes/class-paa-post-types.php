<?php
/**
 * Registers the Practice Area and Attorney custom post types,
 * plus the shared Practice taxonomy linking attorneys to practice areas.
 *
 * @package PAA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PAA_Post_Types {

	const PRACTICE_AREA = 'practice_area';
	const ATTORNEY      = 'attorney';
	const TESTIMONIAL   = 'testimonial';
	const CONSULTATION  = 'consultation_request';
	const CASE_RESULT   = 'case_result';
	const FAQ           = 'paa_faq';
	const TAX_PRACTICE  = 'paa_practice';

	/**
	 * Wire up registration on init.
	 */
	public static function hooks() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Register post types and taxonomy.
	 */
	public static function register() {
		register_post_type(
			self::PRACTICE_AREA,
			array(
				'labels'       => array(
					'name'          => __( 'Practice Areas', 'paa' ),
					'singular_name' => __( 'Practice Area', 'paa' ),
					'add_new_item'  => __( 'Add New Practice Area', 'paa' ),
				),
				'public'       => true,
				'has_archive'  => true,
				'rewrite'      => array( 'slug' => 'practice-areas' ),
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
				'menu_icon'    => 'dashicons-briefcase',
			)
		);

		register_post_type(
			self::ATTORNEY,
			array(
				'labels'       => array(
					'name'          => __( 'Attorneys', 'paa' ),
					'singular_name' => __( 'Attorney', 'paa' ),
					'add_new_item'  => __( 'Add New Attorney', 'paa' ),
				),
				'public'       => true,
				'has_archive'  => false,
				'rewrite'      => array( 'slug' => 'attorneys' ),
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
				'menu_icon'    => 'dashicons-businessperson',
			)
		);

		register_taxonomy(
			self::TAX_PRACTICE,
			array( self::ATTORNEY, self::PRACTICE_AREA, self::CASE_RESULT, self::FAQ ),
			array(
				'labels'       => array(
					'name'          => __( 'Practices', 'paa' ),
					'singular_name' => __( 'Practice', 'paa' ),
				),
				'hierarchical' => true,
				'public'       => true,
				'rewrite'      => array( 'slug' => 'practice' ),
				'show_in_rest' => true,
				'show_admin_column' => true,
			)
		);

		register_post_type(
			self::TESTIMONIAL,
			array(
				'labels'       => array(
					'name'          => __( 'Testimonials', 'paa' ),
					'singular_name' => __( 'Testimonial', 'paa' ),
					'add_new_item'  => __( 'Add New Testimonial', 'paa' ),
				),
				'public'       => true,
				'has_archive'  => false,
				'rewrite'      => array( 'slug' => 'testimonials' ),
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor' ),
				'menu_icon'    => 'dashicons-star-filled',
			)
		);

		register_post_type(
			self::CONSULTATION,
			array(
				'labels'       => array(
					'name'          => __( 'Consultation Requests', 'paa' ),
					'singular_name' => __( 'Consultation Request', 'paa' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'edit.php?post_type=' . self::ATTORNEY,
				'supports'     => array( 'title', 'editor' ),
			)
		);

		register_post_type(
			self::CASE_RESULT,
			array(
				'labels'       => array(
					'name'          => __( 'Case Results', 'paa' ),
					'singular_name' => __( 'Case Result', 'paa' ),
					'add_new_item'  => __( 'Add New Case Result', 'paa' ),
				),
				'public'       => true,
				'has_archive'  => true,
				'rewrite'      => array( 'slug' => 'case-results' ),
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'page-attributes' ),
				'menu_icon'    => 'dashicons-awards',
			)
		);

		register_post_type(
			self::FAQ,
			array(
				'labels'       => array(
					'name'          => __( 'FAQs', 'paa' ),
					'singular_name' => __( 'FAQ', 'paa' ),
					'add_new_item'  => __( 'Add New FAQ', 'paa' ),
				),
				'public'       => true,
				'has_archive'  => false,
				'rewrite'      => array( 'slug' => 'faqs' ),
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'page-attributes' ),
				'menu_icon'    => 'dashicons-editor-help',
			)
		);
	}
}
