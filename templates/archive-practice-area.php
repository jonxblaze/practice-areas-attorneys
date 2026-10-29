<?php
/**
 * Practice Area archive template.
 *
 * Used only when the active theme does not provide its own
 * archive-practice-area.php. Calls get_header()/get_footer() so it
 * inherits the theme chrome.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="paa-archive">
	<header class="paa-archive__header">
		<h1><?php esc_html_e( 'Practice Areas', 'paa' ); ?></h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="paa-grid paa-grid--3">
			<?php
			while ( have_posts() ) {
				the_post();
				$tagline = get_post_meta( get_the_ID(), '_paa_tagline', true );
				?>
				<article class="paa-grid__item">
					<?php if ( has_post_thumbnail() ) : ?>
						<a href="<?php the_permalink(); ?>">
							<?php the_post_thumbnail( 'medium', array( 'class' => 'paa-grid__thumb' ) ); ?>
						</a>
					<?php endif; ?>
					<h2 class="paa-grid__title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
					<?php if ( $tagline ) : ?>
						<p class="paa-grid__tagline"><?php echo esc_html( $tagline ); ?></p>
					<?php endif; ?>
					<p class="paa-grid__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
				</article>
				<?php
			}
			?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No practice areas found.', 'paa' ); ?></p>
	<?php endif; ?>
</main>

<?php
get_footer();
