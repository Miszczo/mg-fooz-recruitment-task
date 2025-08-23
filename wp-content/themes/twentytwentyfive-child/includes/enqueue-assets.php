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
} );


