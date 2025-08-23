<?php
/**
 * Theme bootstrap for Twenty Twenty-Five Child – Library & FAQ
 */

// Define theme paths.
define( 'TT5C_DIR', trailingslashit( get_stylesheet_directory() ) );
define( 'TT5C_URI', trailingslashit( get_stylesheet_directory_uri() ) );

// Theme supports.
add_action( 'after_setup_theme', function () {
    add_theme_support( 'post-thumbnails' );
} );

// Include files from includes/.
require_once TT5C_DIR . 'includes/enqueue-assets.php';
require_once TT5C_DIR . 'includes/post-types.php';
require_once TT5C_DIR . 'includes/taxonomies.php';
require_once TT5C_DIR . 'includes/rest-related-books.php';


