<?php

/**
 * Template: Single Book
 */

block_template_part('header');
?>
<main id="primary" class="site-main wp-block-group">
    <?php tt5c_the_breadcrumbs(); ?>
    <div class="entry-container wp-block-group alignwide">
        <article class="entry">
            <header class="entry-header">
                <?php
                // Featured image as LCP (eager + fetchpriority="high").
                if (has_post_thumbnail()) {
                    the_post_thumbnail('full', [
                        'loading'        => 'eager',
                        'fetchpriority'  => 'high',
                        'decoding'       => 'async',
                        'class'          => 'featured-image',
                    ]);
                }
                ?>
                <h1 class="entry-title has-xl-font-size"><?php the_title(); ?></h1>
                <div class="entry-meta">
                    <?php
                    $date_iso  = get_post_time(DATE_W3C, true);
                    $date_text = get_the_date('M j, Y');
                    ?>
                    <time class="published" datetime="<?php echo esc_attr($date_iso); ?>"><?php echo esc_html($date_text); ?></time>
                    <?php
                    $terms = get_the_terms(get_the_ID(), 'book-genre');
                    if (! is_wp_error($terms) && ! empty($terms)) :
                        echo '<span class="dot"> · </span>';
                        echo '<span class="genres">';
                        foreach ($terms as $index => $term) {
                            if ($index > 0) {
                                echo ', ';
                            }
                            echo '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>';
                        }
                        echo '</span>';
                    endif;
                    ?>
                </div>
            </header>
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>

        <section id="related-books" aria-labelledby="related-books-title" data-current-id="<?php echo esc_attr(get_the_ID()); ?>">
            <h2 id="related-books-title"><?php echo esc_html__('Related books', 'tt5c'); ?></h2>
            <div class="status" aria-live="polite"></div>
            <ul class="book-grid"></ul>
            <noscript>
                <a href="<?php echo esc_url(home_url('/library/')); ?>"><?php echo esc_html__('Browse all books', 'tt5c'); ?></a>
            </noscript>
        </section>
    </div>
    <?php
    // JSON-LD Book minimal markup.
    $book_ld = [
        '@context'      => 'https://schema.org',
        '@type'         => 'Book',
        'name'          => get_the_title(),
        'datePublished' => get_the_date('c'),
        'url'           => get_permalink(),
    ];
    if (has_post_thumbnail()) {
        $img = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
        if ($img) {
            $book_ld['image'] = $img[0];
        }
    }
    ?>
    <script type="application/ld+json">
        <?php echo wp_json_encode($book_ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
        ?>
    </script>
</main>
<?php 
block_template_part('footer');
