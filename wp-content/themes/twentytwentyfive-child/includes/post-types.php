<?php
/**
 * Register custom post type: library (Books)
 */

add_action( 'init', function () {
    $labels = [
        'name'                     => _x( 'Books', 'Post type general name', 'tt5c' ),
        'singular_name'            => _x( 'Book', 'Post type singular name', 'tt5c' ),
        'menu_name'                => _x( 'Books', 'Admin Menu text', 'tt5c' ),
        'name_admin_bar'           => _x( 'Book', 'Add New on Toolbar', 'tt5c' ),
        'add_new'                  => _x( 'Add New', 'Book', 'tt5c' ),
        'add_new_item'             => __( 'Add New Book', 'tt5c' ),
        'new_item'                 => __( 'New Book', 'tt5c' ),
        'edit_item'                => __( 'Edit Book', 'tt5c' ),
        'view_item'                => __( 'View Book', 'tt5c' ),
        'all_items'                => __( 'All Books', 'tt5c' ),
        'search_items'             => __( 'Search Books', 'tt5c' ),
        'parent_item_colon'        => __( 'Parent Books:', 'tt5c' ),
        'not_found'                => __( 'No Books found.', 'tt5c' ),
        'not_found_in_trash'       => __( 'No Books found in Trash.', 'tt5c' ),
        'archives'                 => __( 'Book archives', 'tt5c' ),
        'attributes'               => __( 'Book attributes', 'tt5c' ),
        'insert_into_item'         => __( 'Insert into Book', 'tt5c' ),
        'uploaded_to_this_item'    => __( 'Uploaded to this Book', 'tt5c' ),
        'featured_image'           => _x( 'Featured image', 'Book', 'tt5c' ),
        'set_featured_image'       => _x( 'Set featured image', 'Book', 'tt5c' ),
        'remove_featured_image'    => _x( 'Remove featured image', 'Book', 'tt5c' ),
        'use_featured_image'       => _x( 'Use as featured image', 'Book', 'tt5c' ),
        'items_list'               => __( 'Books list', 'tt5c' ),
        'items_list_navigation'    => __( 'Books list navigation', 'tt5c' ),
        'filter_items_list'        => __( 'Filter Books list', 'tt5c' ),
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => [ 'slug' => 'library' ],
        'show_in_rest'       => true,
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-book-alt',
        'supports'           => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ],
        'capability_type'    => 'post',
    ];

    register_post_type( 'library', $args );
} );


