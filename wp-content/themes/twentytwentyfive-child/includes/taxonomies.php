<?php
/**
 * Register taxonomy: book-genre
 */

add_action( 'init', function () {
    $labels = [
        'name'                       => _x( 'Genres', 'taxonomy general name', 'tt5c' ),
        'singular_name'              => _x( 'Genre', 'taxonomy singular name', 'tt5c' ),
        'search_items'               => __( 'Search Genres', 'tt5c' ),
        'popular_items'              => __( 'Popular Genres', 'tt5c' ),
        'all_items'                  => __( 'All Genres', 'tt5c' ),
        'parent_item'                => __( 'Parent Genre', 'tt5c' ),
        'parent_item_colon'          => __( 'Parent Genre:', 'tt5c' ),
        'edit_item'                  => __( 'Edit Genre', 'tt5c' ),
        'view_item'                  => __( 'View Genre', 'tt5c' ),
        'update_item'                => __( 'Update Genre', 'tt5c' ),
        'add_new_item'               => __( 'Add New Genre', 'tt5c' ),
        'new_item_name'              => __( 'New Genre Name', 'tt5c' ),
        'separate_items_with_commas' => __( 'Separate genres with commas', 'tt5c' ),
        'add_or_remove_items'        => __( 'Add or remove genres', 'tt5c' ),
        'choose_from_most_used'      => __( 'Choose from the most used genres', 'tt5c' ),
        'not_found'                  => __( 'No genres found.', 'tt5c' ),
        'no_terms'                   => __( 'No genres', 'tt5c' ),
        'items_list_navigation'      => __( 'Genres list navigation', 'tt5c' ),
        'items_list'                 => __( 'Genres list', 'tt5c' ),
        'back_to_items'              => __( '← Back to Genres', 'tt5c' ),
        'menu_name'                  => __( 'Genres', 'tt5c' ),
    ];

    $args = [
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'book-genre' ],
        'show_in_rest'      => true,
    ];

    register_taxonomy( 'book-genre', [ 'library' ], $args );
} );

add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_tax( 'book-genre' ) ) {
		$q->set( 'posts_per_page', 5 );
		$q->set( 'orderby', 'date' );
		$q->set( 'order', 'DESC' );
		$q->set( 'post_type', 'library' );
	}
} );


