<?php
/**
 * Enqueue theme assets (CSS/JS) – footer only.
 */

add_action( 'wp_enqueue_scripts', function () {
    $css = TT5C_DIR . 'assets/css/main.css';
    $js  = TT5C_DIR . 'assets/js/scripts.js';

    wp_enqueue_style(
        'tt5c-main',
        TT5C_URI . 'assets/css/main.css',
        [],
        file_exists( $css ) ? filemtime( $css ) : null
    );

    wp_enqueue_script(
        'tt5c-scripts',
        TT5C_URI . 'assets/js/scripts.js',
        [],
        file_exists( $js ) ? filemtime( $js ) : null,
        true
    );

    // Localize messages and current post id for scripts.
    $current_id = is_singular( 'library' ) ? get_the_ID() : 0;
    wp_localize_script( 'tt5c-scripts', 'TT5C', [
        'i18n' => [
            'loading'      => __( 'Loading related books…', 'tt5c' ),
            'noResults'    => __( 'No related books found.', 'tt5c' ),
            'loadFailed'   => __( 'Failed to load related books.', 'tt5c' ),
            'browseAll'    => __( 'Browse all books', 'tt5c' ),
        ],
        'currentPostId' => $current_id,
        'restBase'      => esc_url_raw( home_url( '/' ) ),
    ] );
} );

// Editor-only assets for custom blocks (FAQ Accordion)
add_action( 'enqueue_block_editor_assets', function () {
    $editor_js = TT5C_DIR . 'blocks/faq-accordion/index.js';
    if ( file_exists( $editor_js ) ) {
        wp_enqueue_script(
            'tt5c-faq-blocks-editor',
            TT5C_URI . 'blocks/faq-accordion/index.js',
            [ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-block-editor' ],
            filemtime( $editor_js ),
            true
        );
    }
} );


