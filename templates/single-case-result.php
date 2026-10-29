<?php
/**
 * Single Case Result template.
 *
 * Used only when the active theme does not provide its own
 * single-case-result.php. Calls get_header()/get_footer() so it
 * inherits the theme chrome. Shows the outcome, year, practices,
 * and the prior-results disclaimer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="paa-single-case-result">
	<?php
	while ( have_posts() ) {
		the_post();

		$outcome   = get_post_meta( get_the_ID(), '_paa_case_outcome', true );
		$year      = get_post_meta( get_the_ID(), '_paa_case_year', true );
		$practices = get_the_term_list( get_the_ID(), PAA_Post_Types::TAX_PRACTICE, '', ', ' );
		?>
		<article <?php post_class( 'paa-case-result' ); ?>>
			<h1 class="paa-case-result__title"><?php the_title(); ?></h1>
			<?php if ( $outcome ) : ?>
				<p class="paa-case-result__outcome"><?php echo esc_html( $outcome ); ?></p>
			<?php endif; ?>
			<?php if ( $year ) : ?>
				<p class="paa-case-result__year"><?php echo esc_html( $year ); ?></p>
			<?php endif; ?>
			<?php if ( $practices && ! is_wp_error( $practices ) ) : ?>
				<p class="paa-case-result__practices"><?php echo wp_kses_post( $practices ); ?></p>
			<?php endif; ?>
			<div class="paa-case-result__content">
				<?php the_content(); ?>
			</div>
			<p class="paa-disclaimer"><?php esc_html_e( 'Past results do not guarantee similar outcomes.', 'paa' ); ?></p>
		</article>
		<?php
	}
	?>
</main>

<?php
get_footer();
