<?php
/**
 * REST: Related Books endpoint
 */

add_action( 'rest_api_init', function () {
    register_rest_route( 'fooz/v1', '/related-books', [
        'methods'  => 'GET',
        'callback' => function ( WP_REST_Request $request ) {
            $exclude = (int) $request->get_param( 'exclude' );
            $limit   = (int) $request->get_param( 'limit' );

            if ( $exclude <= 0 ) {
                return new WP_Error( 'invalid_exclude', __( 'Parameter "exclude" must be a positive integer.', 'tt5c' ), [ 'status' => 400 ] );
            }

            if ( $limit <= 0 ) {
                $limit = 20;
            }
            if ( $limit > 20 ) {
                $limit = 20;
            }

            $query = new WP_Query( [
                'post_type'           => 'library',
                'post_status'         => 'publish',
                'posts_per_page'      => $limit,
                'orderby'             => 'date',
                'order'               => 'DESC',
                'post__not_in'        => [ $exclude ],
                'ignore_sticky_posts' => true,
                'no_found_rows'       => true,
            ] );

            $items = [];

            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id   = get_the_ID();
                $title     = get_the_title();
                $date_iso  = get_post_time( DATE_W3C, true );
                $permalink = get_permalink();

                $terms = get_the_terms( $post_id, 'book-genre' );
                $genres = [];
                if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                    foreach ( $terms as $term ) {
                        $genres[] = [
                            'id'   => (int) $term->term_id,
                            'name' => $term->name,
                            'slug' => $term->slug,
                            'url'  => get_term_link( $term ),
                        ];
                    }
                }

                $words = defined( 'TT5C_EXCERPT_WORDS' ) ? (int) TT5C_EXCERPT_WORDS : 36;
                $raw_excerpt = has_excerpt( $post_id ) ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_the_content( null, false, $post_id ) ), $words );
                $excerpt     = preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $raw_excerpt ) );

                $thumb_url    = get_the_post_thumbnail_url( $post_id, 'book_card' );
                $thumb_id     = get_post_thumbnail_id( $post_id );
                $thumb_srcset = $thumb_id ? wp_get_attachment_image_srcset( $thumb_id, 'book_card' ) : '';
                $thumb_sizes  = $thumb_id ? wp_get_attachment_image_sizes( $thumb_id, 'book_card' ) : '';
                if ( ! $thumb_url ) {
                    $thumb_url = '';
                }

                $items[] = [
                    'id'           => (int) $post_id,
                    'title'        => $title,
                    'date'         => $date_iso,
                    'genres'       => $genres,
                    'excerpt'      => $excerpt,
                    'thumbnailUrl'    => $thumb_url,
                    'thumbnailSrcset' => $thumb_srcset ?: '',
                    'thumbnailSizes'  => $thumb_sizes ?: '',
                    'permalink'    => $permalink,
                ];
            }
            wp_reset_postdata();

            return rest_ensure_response( $items );
        },
        'permission_callback' => '__return_true',
    ] );
} );


