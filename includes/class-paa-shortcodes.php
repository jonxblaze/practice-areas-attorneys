<?php
/**
 * Front-end shortcodes:
 *   [attorney_card id="123"]      Profile card for one attorney.
 *   [practice_area_grid columns="3"]  Grid of all practice areas.
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
}
