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
			array( self::ATTORNEY, self::PRACTICE_AREA ),
			array(
				'labels'       => array(
					'name'          => __( 'Practices', 'paa' ),
					'singular_name' => __( 'Practice', 'paa' ),
				),
				'hierarchical' => true,
				'public'       => true,
				'rewrite'      => array( 'slug' => 'practice' ),
				'show_in_rest' => true,
			)
		);
	}
}
