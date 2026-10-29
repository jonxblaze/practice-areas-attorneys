<?php
/**
 * Front-end shortcodes:
 *   [attorney_card id="123"]                Profile card for one attorney.
 *   [practice_area_grid columns="3"]        Grid of all practice areas.
 *   [attorney_directory practice="slug"]    Filterable directory of attorneys.
 *   [consultation_cta url="/contact/"]     Consultation button + click-to-call.
 *
 * @package PAA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PAA_Shortcodes {

	/**
	 * Register shortcodes and the front-end stylesheet.
	 */
	public static function hooks() {
		add_shortcode( 'attorney_card', array( __CLASS__, 'attorney_card' ) );
		add_shortcode( 'practice_area_grid', array( __CLASS__, 'practice_area_grid' ) );
		add_shortcode( 'attorney_directory', array( __CLASS__, 'attorney_directory' ) );
		add_shortcode( 'consultation_cta', array( __CLASS__, 'consultation_cta' ) );
		add_shortcode( 'testimonials', array( __CLASS__, 'testimonials' ) );
		add_shortcode( 'firm_contact_card', array( __CLASS__, 'firm_contact_card' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Register (but only enqueue when a shortcode actually renders) the CSS.
	 */
	public static function enqueue_assets() {
		wp_register_style(
			'paa-frontend',
			PAA_URL . 'assets/css/paa-frontend.css',
			array(),
			PAA_VERSION
		);
	}

	/**
	 * Render one attorney profile card.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function attorney_card( $atts ) {
		$atts = shortcode_atts(
			array( 'id' => 0 ),
			$atts,
			'attorney_card'
		);

		$post_id = absint( $atts['id'] );
		if ( ! $post_id || PAA_Post_Types::ATTORNEY !== get_post_type( $post_id ) ) {
			return '';
		}

		wp_enqueue_style( 'paa-frontend' );

		return self::render_attorney_card( $post_id );
	}

	/**
	 * Shared card markup used by [attorney_card] and [attorney_directory].
	 *
	 * @param int $post_id Attorney post ID (already validated by the caller).
	 * @return string
	 */
	private static function render_attorney_card( $post_id ) {
		$name      = get_the_title( $post_id );
		$job_title = get_post_meta( $post_id, '_paa_job_title', true );
		$phone     = get_post_meta( $post_id, '_paa_phone', true );
		$email     = get_post_meta( $post_id, '_paa_email', true );
		$bar       = get_post_meta( $post_id, '_paa_bar', true );
		$photo     = get_the_post_thumbnail( $post_id, 'medium', array( 'class' => 'paa-card__photo' ) );
		$practices = get_the_term_list( $post_id, PAA_Post_Types::TAX_PRACTICE, '', ', ' );

		ob_start();
		?>
		<article class="paa-card">
			<?php echo $photo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-generated thumbnail markup. ?>
			<div class="paa-card__body">
				<h3 class="paa-card__name"><?php echo esc_html( $name ); ?></h3>
				<?php if ( $job_title ) : ?>
					<p class="paa-card__title"><?php echo esc_html( $job_title ); ?></p>
				<?php endif; ?>
				<?php if ( $practices && ! is_wp_error( $practices ) ) : ?>
					<p class="paa-card__practices"><?php echo wp_kses_post( $practices ); ?></p>
				<?php endif; ?>
				<?php if ( $phone ) : ?>
					<p class="paa-card__phone">
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^+\d]/', '', $phone ) ); ?>">
							<?php echo esc_html( $phone ); ?>
						</a>
					</p>
				<?php endif; ?>
				<?php if ( $email ) : ?>
					<p class="paa-card__email">
						<a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>">
							<?php echo esc_html( antispambot( $email ) ); ?>
						</a>
					</p>
				<?php endif; ?>
				<?php if ( $bar ) : ?>
					<p class="paa-card__bar"><?php echo esc_html( $bar ); ?></p>
				<?php endif; ?>
			</div>
		</article>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render a grid of all practice areas.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function practice_area_grid( $atts ) {
		$atts = shortcode_atts(
			array( 'columns' => 3 ),
			$atts,
			'practice_area_grid'
		);

		$query = new WP_Query(
			array(
				'post_type'      => PAA_Post_Types::PRACTICE_AREA,
				'posts_per_page' => -1,
				'orderby'        => 'menu_order title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);

		if ( ! $query->have_posts() ) {
			return '';
		}

		wp_enqueue_style( 'paa-frontend' );

		$columns = min( 4, max( 1, absint( $atts['columns'] ) ) );

		ob_start();
		?>
		<div class="paa-grid paa-grid--<?php echo esc_attr( $columns ); ?>">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				$tagline = get_post_meta( get_the_ID(), '_paa_tagline', true );
				?>
				<article class="paa-grid__item">
					<?php if ( has_post_thumbnail() ) : ?>
						<a href="<?php the_permalink(); ?>">
							<?php the_post_thumbnail( 'medium', array( 'class' => 'paa-grid__thumb' ) ); ?>
						</a>
					<?php endif; ?>
					<h3 class="paa-grid__title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h3>
					<?php if ( $tagline ) : ?>
						<p class="paa-grid__tagline"><?php echo esc_html( $tagline ); ?></p>
					<?php endif; ?>
					<p class="paa-grid__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
				</article>
				<?php
			}
			wp_reset_postdata();
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render a filterable directory of attorneys.
	 *
	 * The practice filter comes from the shortcode attribute, or from the
	 * ?paa_practice=slug query var set by the filter links below.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function attorney_directory( $atts ) {
		$atts = shortcode_atts(
			array(
				'practice' => '',
				'columns'  => 3,
			),
			$atts,
			'attorney_directory'
		);

		$filter = sanitize_key( $atts['practice'] );
		if ( '' === $filter && isset( $_GET['paa_practice'] ) ) {
			$filter = sanitize_key( wp_unslash( $_GET['paa_practice'] ) );
		}

		$query_args = array(
			'post_type'      => PAA_Post_Types::ATTORNEY,
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		);
		if ( '' !== $filter ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => PAA_Post_Types::TAX_PRACTICE,
					'field'    => 'slug',
					'terms'    => $filter,
				),
			);
		}

		$query = new WP_Query( $query_args );

		wp_enqueue_style( 'paa-frontend' );

		$columns = min( 4, max( 1, absint( $atts['columns'] ) ) );

		ob_start();
		?>
		<nav class="paa-filter" aria-label="<?php esc_attr_e( 'Filter attorneys by practice', 'paa' ); ?>">
			<a href="<?php echo esc_url( remove_query_arg( 'paa_practice' ) ); ?>"
				<?php echo '' === $filter ? 'class="is-active" aria-current="page"' : ''; ?>>
				<?php esc_html_e( 'All', 'paa' ); ?>
			</a>
			<?php
			$terms = get_terms(
				array(
					'taxonomy'   => PAA_Post_Types::TAX_PRACTICE,
					'hide_empty' => true,
				)
			);
			if ( $terms && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$active = $filter === $term->slug ? 'class="is-active" aria-current="page"' : '';
					?>
					<a href="<?php echo esc_url( add_query_arg( 'paa_practice', $term->slug ) ); ?>"
						<?php echo $active; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static attribute strings built above. ?>>
						<?php echo esc_html( $term->name ); ?>
					</a>
					<?php
				}
			}
			?>
		</nav>
		<?php
		if ( ! $query->have_posts() ) {
			?>
			<p><?php esc_html_e( 'No attorneys found.', 'paa' ); ?></p>
			<?php
			return ob_get_clean();
		}
		?>
		<div class="paa-grid paa-grid--<?php echo esc_attr( $columns ); ?>">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				echo self::render_attorney_card( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- card markup escaped internally.
			}
			wp_reset_postdata();
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render a consultation call-to-action: a button plus optional click-to-call.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function consultation_cta( $atts ) {
		$atts = shortcode_atts(
			array(
				'url'   => '',
				'label' => __( 'Request a Free Consultation', 'paa' ),
				'phone' => '',
			),
			$atts,
			'consultation_cta'
		);

		$url   = esc_url( $atts['url'] );
		$phone = sanitize_text_field( $atts['phone'] );
		if ( '' === $url && '' === $phone ) {
			return '';
		}

		wp_enqueue_style( 'paa-frontend' );

		ob_start();
		?>
		<div class="paa-cta">
			<?php if ( '' !== $url ) : ?>
				<a class="paa-cta__button" href="<?php echo $url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>">
					<?php echo esc_html( $atts['label'] ); ?>
				</a>
			<?php endif; ?>
			<?php if ( '' !== $phone ) : ?>
				<a class="paa-cta__phone" href="tel:<?php echo esc_attr( preg_replace( '/[^+\d]/', '', $phone ) ); ?>">
					<?php echo esc_html( $phone ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render a grid of testimonials with star ratings and Review schema.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function testimonials( $atts ) {
		$atts = shortcode_atts(
			array(
				'columns' => 3,
				'count'   => 0,
			),
			$atts,
			'testimonials'
		);

		$query = new WP_Query(
			array(
				'post_type'      => PAA_Post_Types::TESTIMONIAL,
				'post_status'    => 'publish',
				'posts_per_page' => absint( $atts['count'] ) ? absint( $atts['count'] ) : -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => true,
			)
		);

		if ( ! $query->have_posts() ) {
			return '';
		}

		wp_enqueue_style( 'paa-frontend' );

		$columns = min( 4, max( 1, absint( $atts['columns'] ) ) );
		$reviews = array();

		ob_start();
		?>
		<div class="paa-grid paa-grid--<?php echo esc_attr( $columns ); ?>">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				$rating = (int) get_post_meta( get_the_ID(), '_paa_testimonial_rating', true );
				$body   = get_the_content();
				if ( $rating >= 1 && $rating <= 5 ) {
					$reviews[] = array(
						'@type'        => 'Review',
						'author'       => array(
							'@type' => 'Person',
							'name'  => get_the_title(),
						),
						'reviewBody'   => wp_strip_all_tags( $body ),
						'reviewRating' => array(
							'@type'       => 'Rating',
							'ratingValue' => $rating,
							'bestRating'  => 5,
						),
					);
				}
				?>
				<article class="paa-grid__item paa-testimonial">
					<?php if ( $rating >= 1 && $rating <= 5 ) : ?>
						<p class="paa-stars" aria-label="<?php echo esc_attr( sprintf( __( 'Rated %d out of 5', 'paa' ), $rating ) ); ?>">
							<?php echo esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) ); ?>
						</p>
					<?php endif; ?>
					<div class="paa-testimonial__body"><?php the_content(); ?></div>
					<p class="paa-testimonial__author">&mdash; <?php the_title(); ?></p>
				</article>
				<?php
			}
			wp_reset_postdata();
			?>
		</div>
		<?php
		if ( $reviews ) {
			echo '<script type="application/ld+json">' . wp_json_encode(
				array(
					'@context' => 'https://schema.org',
					'@graph'   => $reviews,
				)
			) . '</script>' . "\n";
		}
		return ob_get_clean();
	}

	/**
	 * Render the firm contact card from the settings page.
	 *
	 * @return string
	 */
	public static function firm_contact_card() {
		$name    = PAA_Settings::get( 'firm_name' );
		$phone   = PAA_Settings::get( 'firm_phone' );
		$email   = PAA_Settings::get( 'firm_email' );
		$address = PAA_Settings::get( 'firm_address' );
		$hours   = PAA_Settings::get( 'firm_hours' );

		if ( '' === $name && '' === $phone && '' === $email && '' === $address && '' === $hours ) {
			return '';
		}

		wp_enqueue_style( 'paa-frontend' );

		ob_start();
		?>
		<div class="paa-contact-card">
			<?php if ( '' !== $name ) : ?>
				<h3 class="paa-contact-card__name"><?php echo esc_html( $name ); ?></h3>
			<?php endif; ?>
			<?php if ( '' !== $address ) : ?>
				<p class="paa-contact-card__address"><?php echo esc_html( $address ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $phone ) : ?>
				<p class="paa-contact-card__phone">
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^+\d]/', '', $phone ) ); ?>">
						<?php echo esc_html( $phone ); ?>
					</a>
				</p>
			<?php endif; ?>
			<?php if ( '' !== $email ) : ?>
				<p class="paa-contact-card__email">
					<a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>">
						<?php echo esc_html( antispambot( $email ) ); ?>
					</a>
				</p>
			<?php endif; ?>
			<?php if ( '' !== $hours ) : ?>
				<p class="paa-contact-card__hours"><?php echo esc_html( $hours ); ?></p>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
