<?php
/**
 * Single Attorney template.
 *
 * Used only when the active theme does not provide its own
 * single-attorney.php. Calls get_header()/get_footer() so it
 * inherits the theme chrome.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="paa-single-attorney">
	<?php
	while ( have_posts() ) {
		the_post();

		$job_title = get_post_meta( get_the_ID(), '_paa_job_title', true );
		$phone     = get_post_meta( get_the_ID(), '_paa_phone', true );
		$email     = get_post_meta( get_the_ID(), '_paa_email', true );
		$bar       = get_post_meta( get_the_ID(), '_paa_bar', true );
		$practices = get_the_term_list( get_the_ID(), PAA_Post_Types::TAX_PRACTICE, '', ', ' );
		?>
		<article <?php post_class( 'paa-profile' ); ?>>
			<div class="paa-profile__media">
				<?php
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'large', array( 'class' => 'paa-profile__photo' ) );
				}
				?>
			</div>
			<div class="paa-profile__content">
				<h1 class="paa-profile__name"><?php the_title(); ?></h1>
				<?php if ( $job_title ) : ?>
					<p class="paa-profile__title"><?php echo esc_html( $job_title ); ?></p>
				<?php endif; ?>
				<?php if ( $practices && ! is_wp_error( $practices ) ) : ?>
					<p class="paa-profile__practices"><?php echo wp_kses_post( $practices ); ?></p>
				<?php endif; ?>
				<div class="paa-profile__contact">
					<?php if ( $phone ) : ?>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^+\d]/', '', $phone ) ); ?>">
							<?php echo esc_html( $phone ); ?>
						</a>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>">
							<?php echo esc_html( antispambot( $email ) ); ?>
						</a>
					<?php endif; ?>
				</div>
				<?php if ( $bar ) : ?>
					<p class="paa-profile__bar">
						<?php esc_html_e( 'Bar admissions: ', 'paa' ); ?><?php echo esc_html( $bar ); ?>
					</p>
				<?php endif; ?>
				<div class="paa-profile__bio">
					<?php the_content(); ?>
				</div>
			</div>
		</article>
		<?php
	}
	?>
</main>

<?php
get_footer();
