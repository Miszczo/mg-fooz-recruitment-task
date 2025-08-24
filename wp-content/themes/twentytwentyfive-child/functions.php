<?php
/**
 * Theme bootstrap for Twenty Twenty-Five Child – Library & FAQ
 */

define( 'TT5C_DIR', trailingslashit( get_stylesheet_directory() ) );
define( 'TT5C_URI', trailingslashit( get_stylesheet_directory_uri() ) );
// Unified excerpt length for cards and API (words)
if ( ! defined( 'TT5C_EXCERPT_WORDS' ) ) {
	define( 'TT5C_EXCERPT_WORDS', 36 );
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'post-thumbnails' );
	// 2:3 image for cards – smaller canonical size to reduce transfer
	add_image_size( 'book_card', 360, 540, true );
} );

// Set global excerpt length for SSR contexts (e.g., Post Excerpt block)
add_filter( 'excerpt_length', function ( $length ) {
	return (int) TT5C_EXCERPT_WORDS;
}, 999 );

require_once TT5C_DIR . 'includes/enqueue-assets.php';
require_once TT5C_DIR . 'includes/post-types.php';
require_once TT5C_DIR . 'includes/taxonomies.php';
require_once TT5C_DIR . 'includes/rest-related-books.php';
require_once TT5C_DIR . 'includes/demo-seeder.php';


/**
 * Renders the breadcrumbs navigation HTML.
 *
 * @return void Echos the breadcrumb HTML.
 */
function tt5c_the_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	$items = [];
	$position = 1;

	$items[] = [ 'url' => home_url( '/' ), 'name' => __( 'Home', 'tt5c' ), 'pos' => $position++ ];

	if ( (int) get_query_var( 'tt5c_genres_index' ) === 1 ) {
		$items[] = [ 'url' => '', 'name' => __( 'Genres', 'tt5c' ), 'pos' => $position++ ];
	} elseif ( is_post_type_archive( 'library' ) ) {
		$items[] = [ 'url' => '', 'name' => __( 'Library', 'tt5c' ), 'pos' => $position++ ];
	} elseif ( is_singular( 'library' ) ) {
		$items[] = [ 'url' => get_post_type_archive_link( 'library' ), 'name' => __( 'Library', 'tt5c' ), 'pos' => $position++ ];
		$items[] = [ 'url' => '', 'name' => get_the_title(), 'pos' => $position++ ];
	} elseif ( is_tax( 'book-genre' ) ) {
		$items[] = [ 'url' => get_post_type_archive_link( 'library' ), 'name' => __( 'Library', 'tt5c' ), 'pos' => $position++ ];
		$items[] = [ 'url' => home_url( '/book-genre/' ), 'name' => __( 'Genres', 'tt5c' ), 'pos' => $position++ ];
		$items[] = [ 'url' => '', 'name' => single_term_title( '', false ), 'pos' => $position++ ];
	} elseif ( is_page() ) {
		$items[] = [ 'url' => '', 'name' => get_the_title(), 'pos' => $position++ ];
	} elseif ( is_home() ) {
		$items[] = [ 'url' => '', 'name' => get_the_title( get_option( 'page_for_posts' ) ), 'pos' => $position++ ];
	}

	if ( count( $items ) <= 1 ) {
		return;
	}

	$html  = '<nav aria-label="Breadcrumb" class="breadcrumbs"><ol itemscope itemtype="https://schema.org/BreadcrumbList">';
	foreach ( $items as $i ) {
		$html .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
		if ( ! empty( $i['url'] ) ) {
			$html .= '<a itemprop="item" href="' . esc_url( $i['url'] ) . '"><span itemprop="name">' . esc_html( $i['name'] ) . '</span></a>';
		} else {
			$html .= '<span itemprop="name">' . esc_html( $i['name'] ) . '</span>';
		}
		$html .= '<meta itemprop="position" content="' . (int) $i['pos'] . '" /></li>';
	}
	$html .= '</ol></nav>';

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $html;
}

/**
 * Registers a `[tt5c_breadcrumbs]` shortcode to display breadcrumbs.
 *
 * @return string The breadcrumbs HTML.
 */
function tt5c_breadcrumbs_shortcode_handler() {
	ob_start();
	tt5c_the_breadcrumbs();
	return ob_get_clean();
}
add_shortcode( 'tt5c_breadcrumbs', 'tt5c_breadcrumbs_shortcode_handler' );


// Genres index: /book-genre/ → custom template
add_action( 'init', function () {
	add_rewrite_rule( '^book-genre/?$', 'index.php?tt5c_genres_index=1', 'top' );
} );

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'tt5c_genres_index';
	return $vars;
} );

add_filter( 'template_include', function ( $template ) {
	if ( (int) get_query_var( 'tt5c_genres_index' ) === 1 ) {
		$custom = TT5C_DIR . 'templates/taxonomy-book-genre-index.php';
		if ( file_exists( $custom ) ) {
			return $custom;
		}
	}
	return $template;
} );

// Register minimal FAQ Accordion blocks (container + item)
add_action( 'init', function () {
	// Container block
	register_block_type( 'fooz/faq-accordion', [
		'render_callback' => function ( $attributes, $content, $block ) {
			$html  = '<section class="wp-block-fooz-faq-accordion" aria-label="' . esc_attr__( 'Frequently Asked Questions', 'tt5c' ) . '">';
			if ( ! empty( $attributes['heading'] ) ) {
				$html .= '<h2>' . esc_html( $attributes['heading'] ) . '</h2>';
			}
			// Wrap items in a container to enable CSS counters
			$html .= '<div class="faq-accordion__items">' . $content . '</div>';
			$html .= '</section>';
			return $html;
		},
		'supports' => [ 'align' => true ],
	] );

	// Item block
	register_block_type( 'fooz/faq-item', [
		'render_callback' => function ( $attributes, $content, $block ) {
			$question = isset( $attributes['question'] ) ? wp_strip_all_tags( (string) $attributes['question'] ) : '';
			$answer   = isset( $attributes['answer'] ) ? wp_kses_post( (string) $attributes['answer'] ) : '';

			// Fallback for legacy saved markup (pre-dynamic block): parse innerHTML.
			if ( ( '' === $question || '' === $answer ) && is_object( $block ) && isset( $block->parsed_block ) && is_array( $block->parsed_block ) ) {
				$raw = isset( $block->parsed_block['innerHTML'] ) ? (string) $block->parsed_block['innerHTML'] : '';
				if ( '' === $question && $raw ) {
					if ( preg_match( '/<[^>]*class="[^"]*faq-item__question[^"]*"[^>]*>(.*?)<\/[^>]+>/si', $raw, $m ) ) {
						$question = wp_strip_all_tags( $m[1] );
					}
				}
				if ( '' === $answer && $raw ) {
					if ( preg_match( '/<[^>]*class="[^"]*faq-item__answer[^"]*"[^>]*>([\s\S]*?)<\/[^>]+>/si', $raw, $m ) ) {
						$answer = wp_kses_post( $m[1] );
					}
				}
			}

			$id = 'faq-' . uniqid();

			// Inline SVG chevron (Font Awesome Free) – colored via currentColor
			$button = sprintf(
				'<button class="faq-item__question" aria-expanded="false" aria-controls="%1$s"><span class="faq-item__question-text">%2$s</span><svg class="faq-item__icon" aria-hidden="true" viewBox="0 0 640 640" focusable="false"><path fill="currentColor" d="M297.4 169.4C309.9 156.9 330.2 156.9 342.7 169.4L534.7 361.4C547.2 373.9 547.2 394.2 534.7 406.7C522.2 419.2 501.9 419.2 489.4 406.7L320 237.3L150.6 406.6C138.1 419.1 117.8 419.1 105.3 406.6C92.8 394.1 92.8 373.8 105.3 361.3L297.3 169.3z"/></svg></button>',
				esc_attr( $id ),
				esc_html( $question )
			);
			$panel  = sprintf(
				'<div id="%1$s" class="faq-item__answer" role="region">%2$s</div>',
				esc_attr( $id ),
				$answer
			);
			return '<div class="wp-block-fooz-faq-item">' . $button . $panel . '</div>';
		},
	] );
} );


// Output JSON-LD FAQPage when FAQ block is present on the page content.
add_action( 'wp_head', function () {
	if ( is_admin() ) {
		return;
	}
	if ( is_singular() ) {
		global $post;
		if ( ! $post ) {
			return;
		}
		if ( ! has_block( 'fooz/faq-accordion', $post ) ) {
			return;
		}

		$collect_items = function ( array $blocks ) use ( &$collect_items ) {
			$items = [];
			foreach ( $blocks as $block ) {
				$name = isset( $block['blockName'] ) ? $block['blockName'] : '';
				if ( 'fooz/faq-item' === $name ) {
					$attrs    = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : [];
					$question = isset( $attrs['question'] ) ? wp_strip_all_tags( (string) $attrs['question'] ) : '';
					$answer   = isset( $attrs['answer'] ) ? wp_strip_all_tags( (string) $attrs['answer'] ) : '';
					if ( ! $answer && isset( $block['innerHTML'] ) ) {
						$answer = wp_strip_all_tags( (string) $block['innerHTML'] );
					}
					if ( $question ) {
						$items[] = [
							'@type'          => 'Question',
							'name'           => $question,
							'acceptedAnswer' => [
								'@type' => 'Answer',
								'text'  => $answer,
							],
						];
					}
				}
				if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
					$items = array_merge( $items, $collect_items( $block['innerBlocks'] ) );
				}
			}
			return $items;
		};

		$blocks     = parse_blocks( $post->post_content );
		$faq_items  = $collect_items( $blocks );
		$faq_ld     = [
			'@context'    => 'https://schema.org',
			'@type'       => 'FAQPage',
			'mainEntity'  => $faq_items,
		];

		echo '<script type="application/ld+json">' . wp_json_encode( $faq_ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
} );


add_action('wp', function () {
	if (is_singular('library')) {

		add_filter('the_post_navigation', '__return_empty_string');
	}
});