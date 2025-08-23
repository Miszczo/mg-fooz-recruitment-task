<?php
/**
 * REST: Related Books endpoint – skeleton.
 */

add_action( 'rest_api_init', function () {
    register_rest_route( 'fooz/v1', '/related-books', [
        'methods'  => 'GET',
        'callback' => function () {
            return rest_ensure_response( [] );
        },
        'permission_callback' => '__return_true',
    ] );
} );


