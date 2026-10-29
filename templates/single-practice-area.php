<?php
/**
 * Single Practice Area template.
 *
 * Used only when the active theme does not provide its own
 * single-practice-area.php. Calls get_header()/get_footer() so it
 * inherits the theme chrome. Lists the attorneys assigned to this
 * practice area's shared practice terms.
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
	}
	?>
</main>

<?php
get_footer();
