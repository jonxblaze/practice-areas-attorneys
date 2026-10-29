<?php
/**
 * Single Practice Area template.
 *
 * Used only when the active theme does not provide its own
 * single-practice-area.php. Calls get_header()/get_footer() so it
 * inherits the theme chrome. Lists the attorneys, case results, and
 * FAQs assigned to this practice area's shared practice terms.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="paa-single-practice">
	<?php
	while ( have_posts() ) {
		the_post();

		$tagline = get_post_meta( get_the_ID(), '_paa_tagline', true );
		?>
		<article <?php post_class( 'paa-practice' ); ?>>
			<h1 class="paa-practice__title"><?php the_title(); ?></h1>
			<?php if ( $tagline ) : ?>
				<p class="paa-practice__tagline"><?php echo esc_html( $tagline ); ?></p>
			<?php endif; ?>
			<div class="paa-practice__content">
				<?php the_content(); ?>
			</div>
		</article>

		<?php
		$terms = get_the_terms( get_the_ID(), PAA_Post_Types::TAX_PRACTICE );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$attorneys = new WP_Query(
				array(
					'post_type'      => PAA_Post_Types::ATTORNEY,
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

			if ( $attorneys->have_posts() ) {
				?>
				<section class="paa-practice-attorneys">
					<h2><?php esc_html_e( 'Attorneys in this practice area', 'paa' ); ?></h2>
					<ul>
						<?php
						while ( $attorneys->have_posts() ) {
							$attorneys->the_post();
							$job_title = get_post_meta( get_the_ID(), '_paa_job_title', true );
							?>
							<li>
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								<?php if ( $job_title ) : ?>
									<span class="paa-practice-attorneys__title"><?php echo esc_html( $job_title ); ?></span>
								<?php endif; ?>
							</li>
							<?php
						}
						wp_reset_postdata();
						?>
					</ul>
				</section>
				<?php
			}
		}

		if ( $terms && ! is_wp_error( $terms ) ) {
			$case_results = new WP_Query(
				array(
					'post_type'      => PAA_Post_Types::CASE_RESULT,
					'posts_per_page' => -1,
					'orderby'        => 'menu_order date',
					'order'          => 'DESC',
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

			if ( $case_results->have_posts() ) {
				?>
				<section class="paa-practice-results">
					<h2><?php esc_html_e( 'Results in this practice area', 'paa' ); ?></h2>
					<ul>
						<?php
						while ( $case_results->have_posts() ) {
							$case_results->the_post();
							$outcome = get_post_meta( get_the_ID(), '_paa_case_outcome', true );
							$year    = get_post_meta( get_the_ID(), '_paa_case_year', true );
							?>
							<li>
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								<?php if ( $outcome ) : ?>
									<span class="paa-practice-results__outcome"><?php echo esc_html( $outcome ); ?></span>
								<?php endif; ?>
								<?php if ( $year ) : ?>
									<span class="paa-practice-results__year"><?php echo esc_html( $year ); ?></span>
								<?php endif; ?>
							</li>
							<?php
						}
						wp_reset_postdata();
						?>
					</ul>
					<p class="paa-disclaimer"><?php esc_html_e( 'Past results do not guarantee similar outcomes.', 'paa' ); ?></p>
				</section>
				<?php
			}

			$faqs = new WP_Query(
				array(
					'post_type'      => PAA_Post_Types::FAQ,
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

			if ( $faqs->have_posts() ) {
				?>
				<section class="paa-practice-faqs">
					<h2><?php esc_html_e( 'Frequently asked questions', 'paa' ); ?></h2>
					<?php
					while ( $faqs->have_posts() ) {
						$faqs->the_post();
						?>
						<article class="paa-faq">
							<h3><?php the_title(); ?></h3>
							<div><?php the_content(); ?></div>
						</article>
						<?php
					}
					wp_reset_postdata();
					?>
				</section>
				<?php
			}
		}
		?>
		<?php
	}
	?>
</main>

<?php
get_footer();
