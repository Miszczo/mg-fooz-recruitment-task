<?php
/**
 * Template: Taxonomy book-genre (skeleton)
 */

get_header();
?>
<main id="primary" class="site-main">
    <header class="archive-header">
        <h1 class="archive-title"><?php single_term_title(); ?></h1>
        <?php if ( term_description() ) : ?>
            <div class="taxonomy-description"><?php echo wp_kses_post( term_description() ); ?></div>
        <?php endif; ?>
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


