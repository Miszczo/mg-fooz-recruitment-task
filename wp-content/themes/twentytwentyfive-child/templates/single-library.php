<?php
/**
 * Template: Single Book (skeleton)
 */

get_header();
?>
<main id="primary" class="site-main">
    <article class="entry">
        <header class="entry-header">
            <h1 class="entry-title"><?php the_title(); ?></h1>
        </header>
        <div class="entry-content">
            <?php the_content(); ?>
        </div>
    </article>

    <section id="related-books" aria-labelledby="related-books-title" data-current-id="<?php echo esc_attr( get_the_ID() ); ?>">
        <h2 id="related-books-title"><?php echo esc_html__( 'Related books', 'tt5c' ); ?></h2>
        <div class="status" aria-live="polite"></div>
        <ul class="book-grid"></ul>
        <noscript>
            <a href="<?php echo esc_url( home_url( '/library/' ) ); ?>"><?php echo esc_html__( 'Browse all books', 'tt5c' ); ?></a>
        </noscript>
    </section>
</main>
<?php get_footer();


