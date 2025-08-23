<?php
/**
 * Template: Archive library (skeleton)
 */

get_header();
?>
<main id="primary" class="site-main">
    <header class="archive-header">
        <h1 class="archive-title"><?php echo esc_html__( 'Library', 'tt5c' ); ?></h1>
    </header>

    <div class="book-grid">
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <article class="card">
                <a href="<?php the_permalink(); ?>" class="stretched-link"><?php the_title(); ?></a>
            </article>
        <?php endwhile; endif; ?>
    </div>

    <?php the_posts_pagination( [ 'screen_reader_text' => __( 'Pagination', 'tt5c' ) ] ); ?>
</main>
<?php get_footer();


